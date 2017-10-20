<?php
/**
 * WebTools - NetworkFixture.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace DrakenTest\WebTools\Network;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class NetworkTestFixture extends TestCase
{
	protected static $testServerPort = 8889;

	protected static $server;

	/**
	 * Kill all running Chrome instance.
	 */
	public static function setUpBeforeClass()
	{
		// Kill the test server or anything on that port
		// REF: https://stackoverflow.com/a/9169237/1938889
		exec( sprintf( 'fuser -k -n tcp %u 2>&1', self::$testServerPort ) );

		// Helper
		self::$server = 'http://127.0.0.1:' . self::$testServerPort;

		// Start a node server
		$server = new Process( sprintf(
			'$(which node) %s %u &',
			__DIR__ . '/../../server/server.js',
			self::$testServerPort
		) );
		$server->start();

		// Give the server a chance to setup
		sleep(1);

		echo $server->getErrorOutput();
	}

	public static function tearDownAfterClass()
	{
		// Kill the test server
		exec( sprintf( 'fuser -k -n tcp %u 2>&1', self::$testServerPort ) );
	}
}