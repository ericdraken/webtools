<?php
/**
 * WebTools - URL.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Utils;

use Draken\WebTools\Exceptions\InvalidArgumentException;

class URL
{
	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function isFullUrl( string $url ): bool
	{
		$parts = parse_url( $url );
		return isset( $parts['scheme'] ) && isset( $parts['host'] );
	}

	/**
	 * Get the host from a URL
	 * @param string $url
	 *
	 * @return string|false
	 */
	public static function getHost( string $url )
	{
		$host = parse_url( $url, PHP_URL_HOST );

		if ( $port = parse_url( $url, PHP_URL_PORT ) )
		{
			return $host . ':' . $port;
		}

		return $host ?? false;
	}

	/**
	 * Get the extension of script in the path
	 * @param string $url
	 *
	 * @return string
	 */
	public static function getPathExtension( string $url )
	{
		$path = parse_url( $url, PHP_URL_PATH );
		if ( ! $path ) {
			return '';
		}

		$ext = pathinfo( $path, PATHINFO_EXTENSION );

		return !! $ext ? strtolower( $ext ) : '';
	}

	/**
	 * Examine the extension of the script in the URL and
	 * determine if it is a common text document extension
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function isTextualUrl( string $url )
	{
		$ext = self::getPathExtension( $url );
		if ( $ext === '' ) {
			return true;
		}

		switch( $ext )
		{
			case 'htm':
			case 'html':
			case 'xml':
			case 'txt':
				return true;
		}

		return false;
	}

	/**
	 * Check if the domain of this URL is the same as the supplied domain
	 * @param string $url
	 * @param string $domain
	 *
	 * @return int
	 */
	public static function isSameDomainUrl( string $url, string $domain )
	{
		$domain = str_replace( [ 'http://', 'https://' ], '', $domain );
		return preg_match( "@http(s)?\://$domain@i", $url );
	}

	/**
	 * Return a URL with no fragment and all lowercase letters
	 * @param string $url
	 *
	 * @return string
	 */
	public static function normalizeUrl( string $url )
	{
		$parts = parse_url( $url );

		if ( ! $parts || empty( $parts ) || ! isset( $parts['scheme'] ) ) {
			throw new InvalidArgumentException( "Invalid URL supplied. Got: $url" );
		}

		$newUrl = $parts['scheme'] . '://' .
		          ( isset( $parts['user'] ) ? "{$parts['user']}:{$parts['pass']}@" : '' ) .
	              $parts['host'] .
		          ( isset( $parts['port'] ) ? $parts['port'] : '' ) .
		          ( isset( $parts['path'] ) ? $parts['path'] : '' ) .
		          ( isset( $parts['query'] ) ? $parts['query'] : '' );

		return strtolower( $newUrl );
	}
}