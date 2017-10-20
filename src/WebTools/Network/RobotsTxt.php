<?php
/**
 * WebTools - RobotsTxt.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Network;

use Draken\WebTools\Exceptions\InvalidArgumentException;
use Draken\WebTools\Utils\LoggableBase;
use GuzzleHttp\Client;
use RobotsTxtParser;

class RobotsTxt extends LoggableBase
{
	const REQUEST_TIMEOUT = 10; // seconds

	const MAX_REDIRECTS = 2;

	/** @var RobotsTxtParser */
	private $parser;

	/** @var string */
	private $userAgent = '';

	/**
	 * @param string $userAgent
	 */
	public function setUserAgent( string $userAgent )
	{
		$this->userAgent = $userAgent;
	}

	/**
	 * @param string $domain
	 * @param int|null $timeout
	 * @param bool $allowInsecure
	 *
	 * @return bool
	 */
	public function findRobotsTxt( string $domain, int $timeout = null, bool $allowInsecure = false )
	{
		if ( stripos( $domain, '/' ) !== false ) {
			throw new InvalidArgumentException( "Domain not supplied. Got: $domain" );
		}

		$url = 'http://' . $domain . '/robots.txt';

		return $this->getRobotsTxt( $url, $timeout, $allowInsecure );
	}

	/**
	 * @param string $robotsURL
	 * @param int|null $timeout
	 * @param bool $allowInsecure
	 *
	 * @return bool
	 */
	public function getRobotsTxt( string $robotsURL, int $timeout = null, bool $allowInsecure = false )
	{
		$guzzle = new Client();

		$response = $guzzle->get( $robotsURL, [
			'connect_timeout' => $timeout ?? self::REQUEST_TIMEOUT,
			'timeout' => $timeout ?? self::REQUEST_TIMEOUT,
			'headers' => [
				'User-Agent' => $this->userAgent,
				'Accept-Encoding' => 'gzip',
				'Accept' => 'text/plain;q=0.9,*/*;q=0.5'
			],
			'decode_content' => true,
			'allow_redirects' => [
				'max' => self::MAX_REDIRECTS,
				'strict' => true,
				'referer' => true,
				'protocols' => ['http', 'https'],
				'track_redirects' => false
			],
			'http_errors' => false,         // Do not throw exceptions on 4xx and 5xx errors
			'verify' => ! $allowInsecure    // Verify SSL
		] );

		self::logger()->info( "Response from [$robotsURL]: {$response->getStatusCode()}" );

		if ( $response->getStatusCode() >= 200 && $response->getStatusCode() <= 299 )
		{
			$this->parser = new RobotsTxtParser( $response->getBody()->getContents() );

			// Add the parser logs
			array_map( function( $log ) {
				self::logger()->debug( $log );
			}, $this->parser->getLog() );

			self::logger()->info( "Found ".count( $this->parser->getRules() )." applicable rules from [$robotsURL]" );
		}
		else
		{
			$this->parser = new RobotsTxtParser( '' );
			self::logger()->info( "Couldn't open [$robotsURL]. Status: {$response->getStatusCode()}" );
			return false;
		}

		return true;
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	public function isUrlAllowed( string $url )
	{
		// Better safe than sorry
		return ! $this->parser->isDisallowed( $url );
	}
}