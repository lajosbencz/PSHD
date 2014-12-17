<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/ Brief tutorial
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace Lazos\PSHD;

/**
 * Exception thrown by PSHD methods
 * Class Exception
 * @package Lazos\PSHD
 */
class Exception extends \Exception
{

	/**
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * Get parameters of SQL query
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}

	/**
	 * @param string $message Error message
	 * @param int $code Error code
	 * @param \Exception $exception Inner exception
	 * @param array $parameters Query parameters
	 */
	public function __construct($message = "", $code = 0, $exception = null, $parameters = array())
	{
		if (strlen($message) < 1 && is_object($exception)) {
			$message = $exception->getMessage();
			ob_start();
			var_dump($exception->getTrace());
			$message .= ob_get_clean();
		}
		$this->_parameters = $parameters;
		parent::__construct($message, $code, $exception);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$s = parent::__toString();
		if (count($this->_parameters) > 0) {
			ob_start();
			var_dump($this->_parameters);
			$s .= ob_get_clean();
		}
		return $s;
	}

}