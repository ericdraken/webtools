<?php
/**
 * WebTools - HTML5Tidy.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Page;

use Draken\WebTools\Exceptions\InvalidArgumentException;
use Draken\WebTools\Exceptions\RuntimeException;
use Draken\WebTools\Utils\LoggableBase;
use Symfony\Component\Process\Process;

/**
 * Class HTML5Tidy
 * Run an analysis on an HTML string to return warnings and errors
 *
 * @package Draken\ChromeCrawler\Tools
 */
class HTML5Tidy extends LoggableBase
{
	private static $isTidyInstalled = null;

	private static $defaults = [
		'--show-errors 20',
		'--show-warnings yes',
		'--show-info no',
		'--accessibility-check 0',
		'--drop-empty-elements no',
		'--drop-empty-paras no',
		'--drop-proprietary-attributes no',
		'--enclose-text no',
		'--merge-divs no',
		'--merge-spans no',
		'--preserve-entities yes',
		'--fix-style-tags no'
	];

	/** @var int */
	private $numErrors = 0;

	/** @var int */
	private $numWarnings = 0;

	/** @var string[] */
	private $errors = [];

	/** @var string[] */
	private $warnings = [];

	/**
	 * Perform a Tidy analysis on the HTML, returning a status code.
	 * 0 = no problems, 1 = warnings present, 2 = warnings and errors found
	 * This does not return tidied HTML. It is just an analysis
	 *
	 * @param string $html
	 * @param array $args
	 * @param int $timeout
	 *
	 * @return int
	 */
	public function runTidy( string $html, array $args = [], int $timeout = 4 )
	{
		// Nothing to do if not installed
		if ( ! self::isTidyHtml5Installed() ) {
			throw new RuntimeException( "PHP Tidy library not installed" );
		}

		// Nothing to do with no string
		if ( empty( $html ) ) {
			throw new InvalidArgumentException( "Nothing to do with an empty HTML string" );
		}

		// Setup a process to run tidy and return the warnings/errors
		$tidyProc = new Process(
			'$(which tidy) -quiet ' . implode( ' ', array_merge( self::$defaults, $args ) ),
			null, null, null, $timeout );

		// Pipe in the HTML string directly to the tidy process
		/** @noinspection PhpParamsInspection */
		$tidyProc->setInput( $html );

		// Run the tidy command
		$code = $tidyProc->run();

		// 0 - OK
		// 1 - Warnings
		// 2 - Errors

		// Stderr contains the errors and warnings
		$res = $tidyProc->getErrorOutput();
		$items = explode( PHP_EOL, trim( $res ) );

		foreach ( $items as $item )
		{
			if ( preg_match( '/^line\s*([0-9]*)\s*column\s*([0-9]*)\s*-\s*([a-zA-Z0-9]*)\s*\:\s*(.*)$/isU', $item, $matches ) )
			{
				$type = trim( $matches[3] );

				$message = [
					'full' => $matches[0],
					'line' => $matches[1],
					'column' => $matches[2],
					'messageType' => $type,
					'message' => trim( $matches[4] ),
				];

				if ( stripos( $type, 'warning' ) === 0 )
				{
					$this->warnings[] = $message;
					$this->numWarnings++;
				}
				else if ( stripos( $type, 'error' ) === 0 )
				{
					$this->errors[] = $message;
					$this->numErrors++;
				}
			}
		}

		return $code;
	}

	/**
	 * @return int
	 */
	public function getNumErrors(): int
	{
		return $this->numErrors;
	}

	/**
	 * @return int
	 */
	public function getNumWarnings(): int
	{
		return $this->numWarnings;
	}

	/**
	 * @return string[]
	 */
	public function getWarningDetails(): array
	{
		return $this->warnings;
	}

	/**
	 * @return string[]
	 */
	public function getErrorDetails(): array
	{
		return $this->errors;
	}

	/**
	 * Display the list of errors and warnings
	 * @return string
	 */
	public function detailedResults(): string
	{
		$str = "Errors: {$this->numErrors}" . PHP_EOL;
		foreach ( $this->errors as $error ) {
			/** @var array $error */
			$str .= $error['full'] . PHP_EOL;
		}

		$str .= PHP_EOL . "Warnings: {$this->numWarnings}" . PHP_EOL;
		foreach ( $this->warnings as $warning ) {
			/** @var array $warning */
			$str .= $warning['full'] . PHP_EOL;
		}

		return $str;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return "Errors: {$this->numErrors}, warnings: {$this->numWarnings}";
	}

	/**
	 * Check if Tidy-HTML5 version 5.x or higher is installed
	 * @return bool
	 */
	public static function isTidyHtml5Installed(): bool
	{
		// Run a check for Tidy just once
		if ( ! is_null( self::$isTidyInstalled ) ) {
			return self::$isTidyInstalled;
		}

		$tidyProc = new Process('which tidy && $(which tidy) -v' );

		$code = $tidyProc->run();
		$res = $tidyProc->getOutput();

		if ( $code !== 0 ) {
			// Tidy not found
			return false;
		}

		// e.g. HTML Tidy for Linux version 5.5.62
		$ver = explode( ' ', trim( $res ) );
		$ver = end( $ver );

		if ( version_compare( $ver, '5.0.0' ) !== 1 )
		{
			self::logger()->error( "Tidy version too old. Minimum version 5.x required. Got: $ver" );
			return false;
		}

		return true;
	}
}