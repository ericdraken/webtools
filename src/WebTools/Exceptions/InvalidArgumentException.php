<?php
/**
 * WebTools - InvalidArgumentException.php
 * Created by: Eric Draken
 * Date: 2017/10/19
 * Copyright (c) 2017
 */

namespace Draken\WebTools\Exceptions;

use Draken\WebTools\Utils\LoggableBase;
use Throwable;

class InvalidArgumentException extends \InvalidArgumentException
{
	public function __construct(
		$message = "",
		$code = 0,
		Throwable $previous = null
	) {
		// Log the error message
		LoggableBase::logger()->error( $message );

		parent::__construct( $message, $code, $previous );
	}
}