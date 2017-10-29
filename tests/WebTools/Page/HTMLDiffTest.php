<?php
/**
 * WebTools - HTMLDiffTest.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Page;

use Draken\WebTools\Page\HTMLDiff;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HTMLDiffTest extends TestCase
{
	/**
	 * Expose protected methods in HTMLDiff class
	 * @param $name
	 *
	 * @return \ReflectionMethod
	 */
	protected static function getMethod( $name )
	{
		$class  = new ReflectionClass( HTMLDiff::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}

	/**
	 * Expect no new lines
	 */
	public function testGetNoBreaksRatio()
	{
		$getRatio = self::getMethod('getBreaksRatio');
		$hd = new HTMLDiff();

		$ratio = $getRatio->invoke( $hd, 'aaaa' );
		$this->assertEquals( 0, $ratio );
	}

	/**
	 * Expect 1 new line
	 */
	public function testGetOneBreaksRatio()
	{
		$getRatio = self::getMethod('getBreaksRatio');
		$hd = new HTMLDiff();

		$ratio = $getRatio->invoke( $hd, 'aaa'.PHP_EOL );
		$this->assertEquals( 0.25, $ratio );
	}

	/**
	 * Expect 2 new lines
	 */
	public function testGetTwoBreaksRatio()
	{
		$getRatio = self::getMethod('getBreaksRatio');
		$hd = new HTMLDiff();

		$ratio = $getRatio->invoke( $hd, "\naa\n" );
		$this->assertEquals( 0.5, $ratio );
	}

	/**
	 * Expect 2 Windows new lines
	 */
	public function testGetTwoWindowsBreaksRatio()
	{
		$getRatio = self::getMethod('getBreaksRatio');
		$hd = new HTMLDiff();

		$ratio = $getRatio->invoke( $hd, "\n\r\n\r" );
		$this->assertEquals( 0.5, $ratio );
	}

	/**
	 * Expect only new line
	 */
	public function testGetOnlyBreaksRatio()
	{
		$getRatio = self::getMethod('getBreaksRatio');
		$hd = new HTMLDiff();

		$ratio = $getRatio->invoke( $hd, PHP_EOL );
		$this->assertEquals( 1, $ratio );
	}

	/**
	 * Expect a ratio of 0 on an empty string
	 */
	public function testGetNothingBreaksRatio()
	{
		$getRatio = self::getMethod('getBreaksRatio');
		$hd = new HTMLDiff();

		$ratio = $getRatio->invoke( $hd, '' );
		$this->assertEquals( 0, $ratio );
	}

	/////////////////////

	/**
	 * Test that body tag diff can be found
	 */
	public function testGetBodyDiffArray()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
abc
</body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
def
</body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffArray( $from, $to, 'body' );
		$this->assertCount( 3, $res );
		$this->assertEquals( HTMLDiff::REMOVED, $res[1][1] );
		$this->assertEquals( HTMLDiff::ADDED, $res[2][1] );
	}

	/**
	 * Test that only the first tag selected if multiple are
	 * present is diffed. In this test, only the first P is matched
	 */
	public function testGetMultipleSelectedDiffArray()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<p>Batman</p>
<p>Robin</p>
</body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<p>Joker</p>
<p>Quinn</p>
</body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffArray( $from, $to, 'p' );
		$this->assertCount( 2, $res );
		$this->assertEquals( HTMLDiff::REMOVED, $res[0][1] );
		$this->assertEquals( HTMLDiff::ADDED, $res[1][1] );
		$this->assertContains( 'Joker', $res[1][0] );
	}

	/**
	 * Test that body tag diff can be found
	 */
	public function testGetBodyDiffTextual()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
abc
</body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
def
</body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffTextual( $from, $to, 'body' );
		$this->assertContains( '-abc', $res );
		$this->assertContains( '+def', $res );
	}

	/**
	 * Test that html tag diff can be found
	 */
	public function testGetHtmlDiffArray()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Batman</title>
</head>
<body></body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Joker</title>
</head>
<body></body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffArray( $from, $to, 'html' );
		$this->assertCount( 6, $res );
		$this->assertEquals( HTMLDiff::REMOVED, $res[2][1] );
		$this->assertEquals( HTMLDiff::ADDED, $res[3][1] );
		$this->assertContains( '<title>Joker</title>', $res[3][0] );
	}

	/**
	 * Test that the contents are removed if the tag cannot be found in
	 * the second snippet
	 */
	public function testBadMismatchHtmlDiffArray()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <script>{}</script>
</head>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Joker</title>
</head>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffArray( $from, $to, 'script' );
		$this->assertCount( 1, $res );
		$this->assertEquals( HTMLDiff::REMOVED, $res[0][1] );
		$this->assertContains( '{}', $res[0][0] );
	}

	/**
	 * Test that if the selector cannot be found in either snippet, then return
	 * an empty array
	 */
	public function testBothMissingHtmlDiffArray()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en"></html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en"></html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffArray( $from, $to, 'h1' );
		$this->assertCount( 0, $res );
	}

	/**
	 * Test that only changes are returned
	 */
	public function testGetHtmlDiffChangesArray()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Batman</title>
</head>
<body></body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Joker</title>
</head>
<body></body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedDiffChangesArray( $from, $to, 'html' );
		$this->assertCount( 2, $res );
		$this->assertEquals( HTMLDiff::REMOVED, $res[0][1] );
		$this->assertEquals( HTMLDiff::ADDED, $res[1][1] );
		$this->assertContains( '<title>Joker</title>', $res[1][0] );
	}

	/**
	 * Test the percent of lines changed
	 */
	public function testGetPercentLinesChangedOneLineChanged()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
3
4
5
6
7
8
9
</body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
3
4
5
6---
7
8
9
</body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedLinesChangedPercent( $from, $to, 'html' );

		$this->assertEquals( 1/10, $res );
	}

	/**
	 * Test the percent of lines changed
	 */
	public function testGetPercentLinesChangedOneLineChangedReversed()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
3
4
5
6---
7
8
9
</body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
3
4
5
6
7
8
9
</body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedLinesChangedPercent( $from, $to, 'html' );

		$this->assertEquals( 1/10, $res );
	}

	/**
	 * Test the percent of lines changed
	 */
	public function testGetPercentLinesChanged50PercentChanged()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
3
4
5
6
7
8
9
</body>
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
3
4---
5---
6---
7---
8---
9
</body>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedLinesChangedPercent( $from, $to, 'html' );

		$this->assertEquals( 5/10, $res );
	}

	/**
	 * Test the percent of lines changed - no changes
	 */
	public function testGetPercentLinesChangedNoChanges()
	{
		$from = <<<HTML
<!DOCTYPE html>
<html lang="en">
</html>
HTML;

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedLinesChangedPercent( $from, $to, 'html' );

		$this->assertEquals( 0, $res );
	}

	/**
	 * Test the percent of lines changed - all changed
	 */
	public function testGetPercentLinesChangedAllChanged()
	{
		$from = '';

		$to = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>Head</head>
</html>
HTML;

		$hd = new HTMLDiff();
		$res = $hd->getSelectedLinesChangedPercent( $from, $to, 'html' );

		$this->assertEquals( 1.0, $res );
	}

	/**
	 * Test the percent of lines changed - no strings
	 */
	public function testGetPercentLinesChangedNothing()
	{
		$from = '';
		$to = '';

		$hd = new HTMLDiff();
		$res = $hd->getSelectedLinesChangedPercent( $from, $to, 'html' );

		$this->assertEquals( 0.0, $res );
	}
}
