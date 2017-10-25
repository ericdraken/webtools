<?php
/**
 * WebTools - HTMLDiff.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Page;

use Draken\WebTools\Utils\LoggableBase;

class HTMLDiff extends LoggableBase
{

	public function runDiff( string $str1, string $str2 )
	{


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