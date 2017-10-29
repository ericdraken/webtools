<?php
/**
 * WebTools - HTMLTokenizer.php
 * Created by: Eric Draken
 * Date: 2017/10/28
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Page;


use Draken\WebTools\Utils\HTML;
use Symfony\Component\DomCrawler\Crawler;

class HTMLTokenizer
{
	/**
	 * @param string $html
	 * @param string|null $selector
	 *
	 * @return string
	 */
	public function getNormalizedText( string $html, string $selector = null ): string
	{
		// Add a space before each tag
		$html = str_replace( '<', ' <', $html );

		// Get a crawler at this selection
		$crawler = HTML::getSelectedNodes( $html, $selector );

		// No nodes
		if ( ! $crawler->count() ) {
			return '';
		}

		// Remove all the node text we don't want
		$crawler
			->filter( 'script,noscript,style' )
			->each( function ( Crawler $node, $i ) {
				$node->getNode( 0 )->textContent = '';
			} );

		// Get the highest selector path
		$rootpath = $crawler->count() ? $crawler->getNode(0)->getNodePath() : '';

		// If multiple nodes were selected, loop over them and combine their text
		$out = '';
		foreach ( $crawler->getIterator() as $node )
		{
			/** @var \DOMElement $node */

			// Skip node text that is already included by an ancestor node
			$res = $node->getNodePath();
			if ( strpos( $res, $rootpath ) === 0 && $res !== $rootpath ) {
				continue;
			}

			$out .= $node->textContent;
		}

		// Normalize the whitespace
		return trim( preg_replace( '/\s+/', ' ', $out ) );
	}



}