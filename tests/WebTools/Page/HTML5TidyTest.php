<?php
/**
 * WebTools - HTML5TidyTest.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Page;

use Draken\WebTools\Exceptions\InvalidArgumentException;
use Draken\WebTools\Page\HTML5Tidy;
use PHPUnit\Framework\TestCase;

class HTML5TidyTest extends TestCase
{
	public function testValidHTML5()
	{
		$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
</body>
</html>
HTML;

		$tidy = new HTML5Tidy();
		$code = $tidy->runTidy( $html, $output, [], 4 );
		$this->assertEquals( 0, $code );
	}

	public function testHTML5Warnings()
	{
		$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body valign="top">
</body>
</html>
HTML;

		$tidy = new HTML5Tidy();
		$code = $tidy->runTidy( $html, $output, [], 4 );
		$this->assertEquals( 1, $code );
	}

	/**
	 * An error code of 2 and an empty html
	 * output should be returned
	 */
	public function testHTML5Errors()
	{
		$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
	<faketag></faketag>
</body>
</html>
HTML;

		$tidy = new HTML5Tidy();
		$code = $tidy->runTidy( $html, $output, [], 4 );
		$this->assertEquals( 2, $code );
		$this->assertEmpty( $output );
	}

	// Test user-supplied output file param throws an exception
	public function testTidyInvalidArgument()
	{
		$out = '/tmp/testTidyInvalidArgument.txt';
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', $output, [
			'-o '.$out
		], 4 );
		$this->assertFileNotExists( $out );
	}

	// Test user-supplied output file param throws an exception
	public function testTidyInvalidArgument2()
	{
		$out = '/tmp/testTidyInvalidArgument2.txt';
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', $output, [
			'-o     '.$out
		], 4 );
		$this->assertFileNotExists( $out );
	}

	// Test user-supplied output file param throws an exception
	public function testTidyInvalidArgument3()
	{
		$out = '/tmp/testTidyInvalidArgument3.txt';
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', $output, [
			'-output '.$out
		], 4 );
		$this->assertFileNotExists( $out );
	}

	// Test user-supplied output file param throws an exception
	public function testTidyInvalidArgument4()
	{
		$out = '/tmp/testTidyInvalidArgument4.txt';
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', $output, [
			'-output       '.$out
		], 4 );
		$this->assertFileNotExists( $out );
	}

	// Test user-supplied output file param throws an exception
	public function testTidyInvalidArgument5()
	{
		$out = '/tmp/testTidyInvalidArgument5.txt';
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', $output, [
			'--output   '.$out
		], 4 );
		$this->assertFileNotExists( $out );
	}
}
