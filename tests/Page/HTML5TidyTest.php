<?php
/**
 * WebTools - HTML5TidyTest.php
 * Created by: Eric Draken
 * Date: 2017/10/20
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Page;

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

		$code = $tidy->runTidy( $html, [], 4 );

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

		$code = $tidy->runTidy( $html, [], 4 );

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

		$code = $tidy->runTidy( $html, [], 4 );

		$this->assertEquals( 2, $code );
	}
}
