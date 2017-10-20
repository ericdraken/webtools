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

class SitemapTest extends NetworkTestFixture
{
	public function testFindSitemap()
	{
		$domain = URL::getHost( self::$server );
		$pages = (new Sitemap())->findSitemapPages( $domain );
		$this->assertCount( 2, $pages );
	}

	public function testGetSitemap()
	{
		$pages = (new Sitemap())->getSitemapPages( self::$server.'/sitemap.xml' );
		$this->assertCount( 2, $pages );
	}
}
