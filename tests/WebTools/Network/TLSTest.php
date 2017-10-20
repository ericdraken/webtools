<?php
/**
 * WebTools - TLSTest.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Network;

use Draken\WebTools\Network\TLS;

class TLSTest extends NetworkTestFixture
{
	// TODO: Finish this
	public function testTLS()
	{
		$tls = new TLS();

		$tls->checkTLS( 'ericdraken.com', 5 );
	}
}
