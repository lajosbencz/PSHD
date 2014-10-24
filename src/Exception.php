<?php

namespace LajosBencz\PSHD;

class Exception extends \Exception
{

	/**
	 * @param string $message
	 * @param int $code
	 * @param \Exception $exception
	 */
	public function __construct($message = "", $code = 0, $exception = null)
	{
		if (strlen($message) < 1 && is_object($exception)) {
			$message = $exception->getMessage();
			ob_start();
			predump($exception->getTrace());
			$message .= ob_get_clean();
		}
		parent::__construct($message, $code, $exception);
	}

}