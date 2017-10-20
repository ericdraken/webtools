<?php
/**
 * WebTools - TLSBadSSLTest.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Network;

use DrakenTest\ChromeCrawler\Network\NetworkTestFixture;

class TLSBadSSLTest extends NetworkTestFixture
{
	/**
	 * @return array
	 */
	public function badSSLGenerator()
	{
		return [
			'sha256' => [ 'sha256.badssl.com', true ],
			'expired' => [ 'expired.badssl.com', false ],
			'wrong host' => [ 'wrong.host.badssl.com', false ],
			'self-signed' => [ 'self-signed.badssl.com', false ],
			'untrusted root' => [ 'untrusted-root.badssl.com', false ],
			'incomplete chain' => [ 'incomplete-chain.badssl.com', false ],
			'dh480' => [ 'dh480.badssl.com', false ],
			'dh512' => [ 'dh512.badssl.com', false ],
			'dh1024' => [ 'dh1024.badssl.com', false ],
			'dh2048' => [ 'dh2048.badssl.com', false ],

			// Failing tests
			//'revoked' => [ 'revoked.badssl.com', false ],
			//'pinning' => [ 'pinning-test.badssl.com', false ],
		];
	}
}