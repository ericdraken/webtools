<?php
/**
 * WebTools - HTML.php
 * Created by: Eric Draken
 * Date: 2017/10/28
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Utils;

use Symfony\Component\DomCrawler\Crawler;

class HTML
{
	/**
	 * Get the selected part of the html if present. If multiple
	 * elements are selected, then only the first element
	 * in the list will be returned
	 *
	 * @param string $html
	 *
	 * @param string|null $selector
	 *
	 * @return string|false
	 */
	public static function getSelectedHtml( string $html, string $selector = null )
	{
		// Get the from body HTML
		$elem = self::getSelectedNodes( $html, $selector );
		if ( $elem->count() ) {
			return $elem->first()->html();
		}

		return false;
	}

	/**
	 * Get the selected nodes of the html string if present
	 *
	 * @param string $html
	 *
	 * @param string|null $selector
	 *
	 * @return Crawler
	 */
	public static function getSelectedNodes( string $html, string $selector = null ): Crawler
	{
		// Get the from body HTML
		$crawler = new Crawler();
		$crawler->addHtmlContent( $html );
		return ! is_null( $selector ) ? $crawler->filter( $selector ) : $crawler;
	}
}