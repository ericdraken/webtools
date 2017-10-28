<?php
/**
 * WebTools - HTMLDiff.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Page;

use Draken\WebTools\Utils\HTML;
use Draken\WebTools\Utils\LoggableBase;
use SebastianBergmann\Diff\Differ;

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
		$from = HTML::getSelectedHtml( $fromHtml, $selector ) ?: '';

		// Get the to HTML
		$to = HTML::getSelectedHtml( $toHtml, $selector ) ?: '';

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
		$from = HTML::getSelectedHtml( $fromHtml, $selector ) ?: '';

		// Get the to HTML
		$to = HTML::getSelectedHtml( $toHtml, $selector ) ?: '';

		$differ = new Differ;
		return $differ->diffToArray( $from, $to );
	}

	/**
	 * Return only the changes in a selected html section
	 *
	 * @param string $fromHtml
	 * @param string $toHtml
	 * @param string $selector
	 *
	 * @return array
	 */
	public function getSelectedDiffChangesArray( string $fromHtml, string $toHtml, string $selector )
	{
		$diffs = $this->getSelectedDiffArray( $fromHtml, $toHtml, $selector );
		return $this->getOnlyChangedLines( $diffs );
	}

	/**
	 * Return the percent of lines that changed
	 * @param string $fromHtml
	 * @param string $toHtml
	 * @param string $selector
	 *
	 * @return float
	 */
	public function getSelectedLinesChangedPercent( string $fromHtml, string $toHtml, string $selector ): float
	{
		$before = count( $this->getSelectedDiffArray( '', $fromHtml, $selector ) );
		$after = count( $this->getSelectedDiffArray( $fromHtml, $toHtml, $selector ) );

		// Both are empty strings - no changes
		if ( $before === 0 && $after === 0 ) {
			return 0.0;
		}

		// Going from nothing to something or vice versa is a 100% change
		if ( $before === 0 || $after === 0 ) {
			return 1.0;
		}

		return abs( $after - $before ) / $before;
	}

	/**
	 * Get only changed lines from a diff results array
	 * @param array $diffs
	 *
	 * @return array
	 */
	private function getOnlyChangedLines( array $diffs )
	{
		return array_values(    // Reset the indices
			array_filter( $diffs, function( $elem ) {
				return $elem[1] !== self::UNCHANGED;
			} )
		);
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