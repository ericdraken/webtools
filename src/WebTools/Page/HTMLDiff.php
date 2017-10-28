<?php
/**
 * WebTools - HTMLDiff.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Page;

use Draken\WebTools\Utils\LoggableBase;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\DomCrawler\Crawler;

class HTMLDiff extends LoggableBase
{
	const UNCHANGED = 0;

	const ADDED = 1;

	const REMOVED = 2;

	/**
	 * Get a textual representation of changed lines
	 *
	 * @param string $fromHtml
	 * @param string $toHtml
	 * @param string $selector
	 *
	 * @return string
	 */
	public function getSelectedDiffTextual( string $fromHtml, string $toHtml, string $selector ): string
	{
		// Get the from HTML
		$from = $this->getSelectedHtml( $fromHtml, $selector ) ?: '';

		// Get the to HTML
		$to = $this->getSelectedHtml( $toHtml, $selector ) ?: '';

		$differ = new Differ;
		return $differ->diff( $from, $to );
	}

	/**
	 * Get an array of changed lines
	 *
	 * @param string $fromHtml
	 * @param string $toHtml
	 * @param string $selector
	 *
	 * @return array
	 */
	public function getSelectedDiffArray( string $fromHtml, string $toHtml, string $selector )
	{
		// Get the from HTML
		$from = $this->getSelectedHtml( $fromHtml, $selector ) ?: '';

		// Get the to HTML
		$to = $this->getSelectedHtml( $toHtml, $selector ) ?: '';


		$differ = new Differ;
		return $differ->diffToArray( $from, $to );
	}

	/**
	 * Get the selected part of the html if present
	 *
	 * @param string $html
	 *
	 * @param string $selector
	 *
	 * @return string|bool
	 */
	protected function getSelectedHtml( string $html, string $selector )
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

	/**
	 * Return the density of new lines
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	protected function getBreaksRatio( string $str ): float
	{
		// Return 0 on an empty string
		if ( empty( $str ) ) {
			return 0;
		}

		$numBreaks = substr_count( $str, PHP_EOL );
		return $numBreaks / strlen( $str );
	}
}