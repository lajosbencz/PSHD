<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/ Brief tutorial
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace LajosBencz\PSHD;

/**
 * Provides support for literal SQL values, may also be expanded with parameters
 * Class Literal
 * @package LajosBencz\PSHD
 */
class Literal
{

	/**
	 * @var PSHD
	 */
	protected $_pshd = null;

	/**
	 * @var string
	 */
	protected $_expression = null;

	/**
	 * Set literal SQL expression
	 * @param string $expression
     * @return $this
	 */
	public function setExpression($expression)
	{
		$this->_expression = $expression;
        return $this;
	}

	/**
	 * Get literal SQL expression
	 * @return string
	 */
	public function getExpression()
	{
		return (string)$this->_expression;
	}

	/**
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * Set parameters
	 * @param array $parameters
     * @return $this
	 */
	public function setParameters($parameters)
	{
		$this->_parameters = $parameters;
        return $this;
	}

	/**
	 * Get parameters
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}

	/**
	 * Add parameter
	 * @param mixed $parameter
	 */
	public function addParameter($parameter)
	{
		$this->_parameters[] = $parameter;
	}

	/**
	 * @param string $expression Literal SQL expression
	 * @param array $parameters (optional) Add parameters
	 * @param PSHD $pshd (optional)
	 */
	public function __construct($expression, $parameters = array(), $pshd = null)
	{
		if (is_object($pshd)) if (get_class($pshd) == __NAMESPACE__ . '\\PSHD') $this->_pshd = $pshd;
		if ($expression) $this->setExpression($expression);
		$this->setParameters($parameters);
	}

	/**
	 * Appends stored parameters to input variable, accepts Select or Array
	 * @param Select|array $var
	 * @return $this
	 */
	public function appendParanmetersTo(&$var)
	{
		$a = is_array($var);
		$s = (is_object($var) && get_class($var) == __NAMESPACE__ . '\\Select');
		foreach ($this->_parameters as $p) {
			if ($a) {
				/**
				 * @var array $var
				 */
				array_push($var, $p);
			} elseif ($s) {
				/**
				 * @var Select $var
				 */
				$var->addParameter($p);
			}
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getExpression();
	}

}