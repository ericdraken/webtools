<?php
/**
 * WebTools - RobotsTxtTest.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Network;

use Draken\WebTools\Network\RobotsTxt;
use DrakenTest\ChromeCrawler\Network\NetworkTestFixture;

class RobotsTxtTest extends NetworkTestFixture
{
	public function testMissingRobotsTxt()
	{
		$robots = new RobotsTxt();
		$robots->getRobotsTxt( self::$server . '/404notfound' );

		$this->assertTrue( $robots->isUrlAllowed( self::$server ) );
	}
}
