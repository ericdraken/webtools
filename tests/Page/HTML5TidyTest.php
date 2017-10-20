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
		$code = $tidy->runTidy( $html, null, [], 4 );
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
		$code = $tidy->runTidy( $html, null, [], 4 );
		$this->assertEquals( 1, $code );
	}

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
		$code = $tidy->runTidy( $html, null, [], 4 );
		$this->assertEquals( 2, $code );
	}

	// .php extension is not allowed
	public function testTidyInvalidOutFilename()
	{
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', 'file.php', [], 4 );
	}

	// Test an output file can be written
	public function testTidyValidOutFilename()
	{
		$out = '/tmp/testTidyValidOutFilename.txt';
		$tidy = new HTML5Tidy();
		$tidy->runTidy( '<html></html>', $out, [], 4 );

		$this->assertFileExists( $out );
		unlink( $out );
	}

	// Test user-supplied output file param throws an exception
	public function testTidyInvalidArgument()
	{
		$out = '/tmp/testTidyInvalidArgument.txt';
		$tidy = new HTML5Tidy();
		$this->expectException( InvalidArgumentException::class );
		$tidy->runTidy( '', null, [
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
		$tidy->runTidy( '', null, [
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
		$tidy->runTidy( '', null, [
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
		$tidy->runTidy( '', null, [
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
		$tidy->runTidy( '', null, [
			'--output   '.$out
		], 4 );
		$this->assertFileNotExists( $out );
	}
}
