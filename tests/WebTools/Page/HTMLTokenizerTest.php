<?php
/**
 * WebTools - HTMLTokenizerTest.php
 * Created by: Eric Draken
 * Date: 2017/10/28
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Page;

use Draken\WebTools\Page\HTMLTokenizer;
use PHPUnit\Framework\TestCase;

class HTMLTokenizerTest extends TestCase
{

	public function testGetNormalizedText()
	{
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
	<section>
		Colorless <em>green</em> ideas<noscript>Oh no</noscript><script>document.write('inline')</script>sleep furiously.
		<style>header{}</style>
	</section>
	<section>
		Odorless <em>stinky</em> eggs<noscript>Oh no</noscript><script>document.write('inline')</script>fry quietly.
		<style>header{}</style>
	</section>
</body>
</html>
HTML;

		$tokenizer = new HTMLTokenizer();
		$text = $tokenizer->getNormalizedText( $html, 'body' );

		$this->assertEquals( 'Colorless green ideas sleep furiously. Odorless stinky eggs fry quietly.', $text );
	}

	/**
	 * Test that multiple nodes can be selected and the text is joined
	 */
	public function testGetNormalizedTextSelector()
	{
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
	<section>
		Colorless<em>green</em> ideas<noscript>Oh no</noscript><script>document.write('inline')</script>sleep furiously.
		<style>header{}</style>
	</section>
	<section>
		Odorless    <em> stinky</em>eggs<noscript>Oh no</noscript><script>document.write('inline')</script>fry quietly.
		<style>header{}</style>
	</section>
</body>
</html>
HTML;

		$tokenizer = new HTMLTokenizer();
		$text = $tokenizer->getNormalizedText( $html, 'section' );

		$this->assertEquals( 'Colorless green ideas sleep furiously. Odorless stinky eggs fry quietly.', $text );
	}

	/**
	 * Test that the whole html text can be selected
	 */
	public function testGetNormalizedTextNoSelector()
	{
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
	<section>
		Colorless <em>green </em>ideas<noscript>Oh no</noscript><script>document.write('inline')</script>sleep furiously.
		<style>header{}</style>
	</section>
	<section>
		Odorless <em>stinky</em>   eggs<noscript>Oh no</noscript><script>document.write('inline')</script>fry quietly.
		<style>header{}</style>
	</section>
</body>
</html>
HTML;

		$tokenizer = new HTMLTokenizer();
		$text = $tokenizer->getNormalizedText( $html );

		$this->assertEquals( 'Colorless green ideas sleep furiously. Odorless stinky eggs fry quietly.', $text );
	}

	/**
	 * Test when selected elements are nested that only
	 * the parent element's text is returned without duplication
	 */
	public function testGetNormalizedTextNestedSelector()
	{
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
	<div>
	outer
		<div>
		inner
			<div>
			child
			</div>
		</div>
	after	
	</div>
</body>
</html>
HTML;

		$tokenizer = new HTMLTokenizer();
		$text = $tokenizer->getNormalizedText( $html, 'div' );

		// Not 'outer inner child after inner child child'
		$this->assertEquals( 'outer inner child after', $text );
	}

	/**
	 * Test when selected elements are nested that only
	 * the parent element's text is returned without duplication
	 */
	public function testGetNormalizedTextDoubleNestedSelector()
	{
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
	<div>
	outer
		<div>
		inner
			<div>
			child
			</div>
		</div>
		<div>
		inner2
			<div>
			child2
			</div>
		</div>
	after	
	</div>
	<div>
	sibling	
	</div>	
</body>
</html>
HTML;

		$tokenizer = new HTMLTokenizer();
		$text = $tokenizer->getNormalizedText( $html, 'div' );

		$this->assertEquals( 'outer inner child inner2 child2 after sibling', $text );
	}

	/**
	 * Test head section text
	 */
	public function testGetNormalizedTextForHead()
	{
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>the title</title>
</head>
<body>
the body
</body>
</html>
HTML;

		$tokenizer = new HTMLTokenizer();
		$text = $tokenizer->getNormalizedText( $html, 'head' );

		$this->assertEquals( 'the title', $text );
	}
}
