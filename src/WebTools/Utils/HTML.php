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
	 * Get the selected part of the html if present
	 *
	 * @param string $html
	 *
	 * @param string $selector
	 *
	 * @return string|bool
	 */
	public static function getSelectedHtml( string $html, string $selector )
	{
		// Get the from body HTML
		$crawler = new Crawler();
		$crawler->addHtmlContent( $html );
		$elem = $crawler->filter( $selector );
		if ( $elem->count() ) {
			return $elem->html();
		}

		return false;
	}
}