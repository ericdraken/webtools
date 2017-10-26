<?php
/**
 * WebTools - Sitemap.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Network;

use GuzzleHttp\Client;
use Draken\WebTools\Utils\LoggableBase;
use Draken\WebTools\Exceptions\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class Sitemap extends LoggableBase
{
	const REQUEST_TIMEOUT = 4; // seconds

	const MAX_REDIRECTS = 4;

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
	 * Try to find the sitemap for the given domain and return its pages
	 *
	 * @param string $domain
	 *
	 * @param int|null $timeout
	 *
	 * @param bool $allowInsecure
	 *
	 * @return array
	 */
	public function findSitemapPages( string $domain, int $timeout = null, bool $allowInsecure = false )
	{
		if ( stripos( $domain, '/' ) !== false ) {
			throw new InvalidArgumentException( "Domain not supplied. Got: $domain" );
		}

		$sitemaps = [
			'http://' . $domain . '/sitemap.xml',
			'http://' . $domain . '/sitemap.xml.gz'
		];

		foreach ( $sitemaps as $sitemap )
		{
			$pages = Sitemap::getSitemapPages( $sitemap, $timeout, $allowInsecure );

			if ( count( $pages ) ) {
				return $pages;
			}
		}

		return [];
	}

	/**
	 * Parse a sitemap and return the pages from 'loc' nodes
	 *
	 * @param string $sitemapURL
	 *
	 * @param int|null $timeout
	 *
	 * @param bool $allowInsecure
	 *
	 * @return array
	 */
	public function getSitemapPages( string $sitemapURL, int $timeout = null, bool $allowInsecure = false )
	{
		$pages = [];

		$guzzle = new Client();

		$response = $guzzle->get( $sitemapURL, [
			'connect_timeout' => $timeout ?? self::REQUEST_TIMEOUT,
			'timeout' => $timeout ?? self::REQUEST_TIMEOUT,
			'headers' => [
				'User-Agent' => $this->userAgent,
				'Accept-Encoding' => 'gzip',
				'Accept' => 'application/xml,text/plain;q=0.9,*/*;q=0.8'
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

		self::logger()->debug( "Response from [$sitemapURL]: {$response->getStatusCode()}" );

		if ( $response->getStatusCode() >= 200 && $response->getStatusCode() <= 299 )
		{
			$crawler = new Crawler();

			$content = $response->getBody()->getContents();

			// Capture internal parsing errors
			$internalErrors = libxml_use_internal_errors(true);

			$crawler->addXmlContent( $content );

			// Get and clear any errors
			$errorsArr = libxml_get_errors();
			libxml_clear_errors();
			libxml_use_internal_errors( $internalErrors );

			// The XPath is done this way to ignore the namespace
			// REF: https://stackoverflow.com/questions/5239685/xml-namespace-breaking-my-xpath
			$crawler->filterXPath( "//*[name()='loc']" )->each( function ( Crawler $node ) use ( &$pages ) {
				$pages[] = $node->getNode( 0 )->nodeValue;
			} );

			// "DTD missing" is always going to be a problem,
			// so only include warnings if there are no pages found
			if ( empty( $pages ) && ! empty( $errorsArr ) ) {
				self::logger()->warning( "Sitemap XML validation errors: " . print_r( $errorsArr, true ) );
			}

			// Remove duplicates
			$pages = array_unique( $pages, SORT_REGULAR );
		}

		self::logger()->info( "Found ".count( $pages )." sitemap pages from [$sitemapURL]" );

		// Return an array of URLs
		return $pages;
	}
}