<?php
/**
 * WebTools - HTMLDiffTest.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Page;

use cogpowered\FineDiff\Diff as FineDiff;
use cogpowered\FineDiff\Granularity\Character;
use cogpowered\FineDiff\Granularity\Paragraph;
use cogpowered\FineDiff\Granularity\Sentence;
use cogpowered\FineDiff\Granularity\Word;
use cogpowered\FineDiff\Render\Html;
use Draken\WebTools\Page\HTMLDiff;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SebastianBergmann\Diff\Differ;

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







	public function testDiffEngine()
	{
		$differ = new Differ;
		$diff= $differ->diffToArray('string one', 'string two');

		var_export( $diff );
	}

	public function testDiffEngine2()
	{
		$differ = new Differ;
		$diff= $differ->diffToArray('<html>string one</html>', '<html>string two</html>');

		var_export( $diff );
	}

	public function testFineDiff()
	{
		$granularity = new Character();
		$granularity = new Word();
		$granularity = new Sentence();
		$granularity = new Paragraph();

		$renderer = new Html();

		$diff = new FineDiff( $granularity, $renderer );
		echo $diff->render('string one', 'string two');
	}
}
