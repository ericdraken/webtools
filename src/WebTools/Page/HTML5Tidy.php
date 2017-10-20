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
 * Tidy File manipulation (https://github.com/htacg/tidy-html5/)
-----------------
-output <file>, -o <file>  write output to the specified <file>
-config <file>             set configuration options from the specified <file>
-file <file>, -f <file>    write errors and warnings to the specified <file>
-modify, -m                modify the original input files

Processing directives
---------------------
-indent, -i                indent element content
-wrap <column>, -w         wrap text at the specified <column>. 0 is assumed
<column>                   if <column> is missing. When this option is
                           omitted, the default of the configuration option
                           'wrap' applies.
-upper, -u                 force tags to upper case
-clean, -c                 replace FONT, NOBR and CENTER tags with CSS
-bare, -b                  strip out smart quotes and em dashes, etc.
-gdoc, -g                  produce clean version of html exported by Google Docs
-numeric, -n               output numeric rather than named entities
-errors, -e                show only errors and warnings
-quiet, -q                 suppress nonessential output
-omit                      omit optional start tags and end tags
-xml                       specify the input is well formed XML
-asxml, -asxhtml           convert HTML to well formed XHTML
-ashtml                    force XHTML to well formed HTML
-access <level>            do additional accessibility checks (<level> = 0,
                           1, 2, 3). 0 is assumed if <level> is missing.

Character encodings
-------------------
-raw                       output values above 127 without conversion to entities
-ascii                     use ISO-8859-1 for input, US-ASCII for output
-latin0                    use ISO-8859-15 for input, US-ASCII for output
-latin1                    use ISO-8859-1 for both input and output
-iso2022                   use ISO-2022 for both input and output
-utf8                      use UTF-8 for both input and output
-mac                       use MacRoman for input, US-ASCII for output
-win1252                   use Windows-1252 for input, US-ASCII for output
-ibm858                    use IBM-858 (CP850+Euro) for input, US-ASCII for output
-utf16le                   use UTF-16LE for both input and output
-utf16be                   use UTF-16BE for both input and output
-utf16                     use UTF-16 for both input and output
-big5                      use Big5 for both input and output
-shiftjis                  use Shift_JIS for both input and output

Miscellaneous
-------------
-version, -v               show the version of Tidy
-help, -h, -?              list the command line options
-help-config               list all configuration options
-help-env                  show information about the environment and runtime configuration
-show-config               list the current configuration settings
-export-config             list the current configuration settings, suitable
                           for a config file
-export-default-config     list the default configuration settings, suitable
                           for a config file
-help-option <option>      show a description of the <option>
-language <lang>           set Tidy's output language to <lang>. Specify
                           '-language help' for more help. Use before
                           output-causing arguments to ensure the language
                           takes effect, e.g.,`tidy -lang es -lang help`.

XML
---
-xml-help                  list the command line options in XML format
-xml-config                list all configuration options in XML format
-xml-strings               output all of Tidy's strings in XML format
-xml-error-strings         output error constants and strings in XML format
-xml-options-strings       output option descriptions in XML format
*/

/**
 * Class HTML5Tidy
 * Run an analysis on an HTML string to return warnings and errors
 *
 * @package Draken\ChromeCrawler\Tools
 */
class HTML5Tidy extends LoggableBase
{
	const MIN_TIDY_VERSION = '5.4.0';

	private static $isTidyInstalled = null;

	private static $defaults = [
		// '--file errors.log', //  Save errors to this log file as well as stdout
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
	 * @param string|null $output
	 * @param array $args
	 * @param int $timeout
	 *
	 * @return int
	 */
	public function runTidy( string $html, string $output = null, array $args = [], int $timeout = 4 )
	{
		// Nothing to do if not installed
		if ( ! self::isTidyHtml5Installed() ) {
			throw new RuntimeException( "PHP Tidy library not installed" );
		}

		// Nothing to do with no string
		if ( empty( $html ) ) {
			throw new InvalidArgumentException( "Nothing to do with an empty HTML string" );
		}

		// No output by default
		if ( is_null( $output ) ) {
			$output = '/dev/null';
		} else {
			$output = escapeshellarg( $output );
		}

		// Security check
		if ( stripos( $output, '.php' ) !== false ) {
			throw new InvalidArgumentException( "Output file cannot have a PHP extensions" );
		}

		// Build the command
		$argstr = implode( ' ', array_merge( self::$defaults, $args ) );
		$cmd = "\$(which tidy) -quiet -o $output $argstr";

		// Args security check
		if ( preg_match( '/-o\s|-output\s/i', $argstr ) ) {
			throw new InvalidArgumentException( "Please do not specify an output file manually" );
		}

		// Setup a process to run tidy and return the warnings/errors
		$tidyProc = new Process( $cmd,null, null, null, $timeout );

		// Pipe in the HTML string directly to the tidy process
		/** @noinspection PhpParamsInspection */
		$tidyProc->setInput( $html );

		// Run the tidy command (blocking)
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

		if ( version_compare( $ver, self::MIN_TIDY_VERSION ) !== 1 )
		{
			self::logger()->error( "Tidy version too old. Minimum version ".self::MIN_TIDY_VERSION." required. Got: $ver" );
			return false;
		}

		return true;
	}
}