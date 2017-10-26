<?php
/**
 * WebTools - SitemapTest.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Network;

use Draken\WebTools\Utils\URL;
use Draken\WebTools\Network\Sitemap;
use DrakenTest\WebTools\LoggerHelper;

class SitemapTest extends NetworkTestFixture
{
	/**
	 * Find the sitemap from the domain
	 */
	public function testFindSitemap()
	{
		$domain = URL::getHost( self::$server );
		$pages = (new Sitemap())->findSitemapPages( $domain );
		$this->assertCount( 2, $pages );
	}

	/**
	 * Verify the desired sitemap was found
	 */
	public function testGetSitemap()
	{
		$pages = (new Sitemap())->getSitemapPages( self::$server.'/sitemap.xml' );
		$this->assertCount( 2, $pages );
	}

	/**
	 * Verify no warnings are issued
	 * @depends testGetSitemap
	 */
	public function testGetSitemapNoWarnings()
	{
		$fp = LoggerHelper::setMemoryBaseLogger();

		(new Sitemap())->getSitemapPages( self::$server.'/sitemap.xml' );

		rewind($fp);
		$res = stream_get_contents($fp);
		$this->assertNotContains( 'WARN', $res );
	}

	/**
	 * Verify the pages returned
	 * @depends testGetSitemap
	 */
	public function testGetSitemapEntry()
	{
		$pages = (new Sitemap())->getSitemapPages( self::$server.'/sitemap.xml' );
		$this->assertEquals( 'http://127.0.0.1:8889/1.html' , $pages[0] );
		$this->assertEquals( 'http://127.0.0.1:8889/2.html' , $pages[1] );
	}

	/**
	 * Verify state when sitemap not found
	 */
	public function testNoSitemap()
	{
		$pages = (new Sitemap())->getSitemapPages( self::$server.'/404notfound' );
		$this->assertCount( 0, $pages );
	}

	/**
	 * Test that a 404 error is logged in the PSR logger
	 * @ depends testNoSitemap
	 */
	public function testNoSitemapDebugMessages()
	{
		$fp = LoggerHelper::setMemoryBaseLogger();

		(new Sitemap())->getSitemapPages( self::$server.'/404notfound' );

		rewind($fp);
		$this->assertContains( ': 404', stream_get_contents($fp) );
	}

	// TODO: Test sitemap.xml.gz

	// TODO: Test domain not found

	// TODO: Test timeout

	// TODO: Test follow complex sitemaps

	/**
	 * Test a malformed sitemap XML file throws no errors and returns no pages,
	 * but adds warnings to the PSR log
	 */
	public function testMalformedSitemap()
	{
		$fp = LoggerHelper::setMemoryBaseLogger();

		$pages = (new Sitemap())->getSitemapPages( self::$server.'/malformed.xml' );
		$this->assertCount( 0, $pages );

		rewind($fp);
		$this->assertContains( 'validation errors', stream_get_contents($fp) );
	}
}
