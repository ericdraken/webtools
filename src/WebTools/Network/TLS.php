<?php
/**
 * WebTools - TLS.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Network;

use Draken\WebTools\Exceptions\InvalidArgumentException;
use Draken\WebTools\Utils\LoggableBase;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use Spatie\SslCertificate\SslCertificate;

class TLS extends LoggableBase
{
	const REQUEST_TIMEOUT = 10; // seconds

	/** @var SslCertificate */
	private $certificate;

	/** @var string[] */
	private $tlsErrors = [];

	/** @var \Exception */
	private $networkError;

	/** @var bool */
	private $isValid = false;

	/**
	 * @return SslCertificate
	 */
	public function getCertificate()
	{
		return $this->certificate;
	}

	/**
	 * @return \string[]
	 */
	public function getTlsErrors(): array
	{
		return $this->tlsErrors;
	}

	/**
	 * @return \Exception
	 */
	public function getNetworkError()
	{
		return $this->networkError;
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		return $this->isValid;
	}

	/**
	 * @return int
	 */
	public function expiresInDays(): int
	{
		return $this->certificate ? $this->certificate->expirationDate()->diffInDays() : -1;
	}

	/**
	 * @return string[]
	 */
	public function getAdditionalDomains(): array
	{
		return $this->certificate ? $this->certificate->getAdditionalDomains() : [];
	}

	public function checkTLS( string $domain, int $timeout = null )
	{
		// Reset
		$this->isValid = false;
		$this->tlsErrors = [];

		if ( stripos( $domain, '/' ) !== false ) {
			throw new InvalidArgumentException( "Domain not supplied. Got: $domain" );
		}

		try
		{
			// Record the socket stream warnings which are not thrown
			set_error_handler( function ( $errno, $errstr /*, $errfile, $errline*/ ) {
				$this->tlsErrors[] = $errstr;
			} );

			// Try to get the cert with full peer verification
			$this->certificate = ( new Downloader() )
				->setTimeout( $timeout ?? self::REQUEST_TIMEOUT )
				->withVerifyPeer( true )
				->withVerifyPeerName( true )
				->withFullChain( true )
				->forHost( $domain );

			restore_error_handler();

			// Validity check
			$this->isValid = ! count( $this->tlsErrors ) && $this->certificate->isValid( $domain );
		}
		catch ( CouldNotDownloadCertificate $err )
		{
			self::logger()->notice( $err->getMessage() );
			$this->tlsErrors[] = $err->getMessage();

			try
			{
				// Try to get the cert with no peer verification
				$this->certificate = ( new Downloader() )
					->setTimeout( $timeout ?? self::REQUEST_TIMEOUT )
					->withVerifyPeer( false )
					->withVerifyPeerName( false )
					->withFullChain( false )
					->forHost( $domain );
			}
			catch( \Exception $err )
			{
				self::logger()->notice( $err->getMessage() );
				$this->networkError = $err;
			}
		}
		catch ( \Exception $err )
		{
			self::logger()->notice( $err->getMessage() );
			$this->networkError = $err;
		}
	}
}