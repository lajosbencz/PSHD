<?php

namespace PSHD;

class Literal {

	protected $_pshd = null;
    protected $_expression = null;
	protected $_parameters = array();

	/**
	 * @param string $expression
	 * @param array $parameters (optional)
	 * @param PSHD $pshd (optional)
	 */
	public function __construct($expression, $parameters=array(), $pshd=null) {
		$this->_pshd = $pshd;
		if($expression) $this->setExpression($expression);
		$this->setParameters($parameters);
	}

	/**
     * @param string $expression
     */
    public function setExpression($expression) {
        $this->_expression = $expression;
    }

	/**
     * @return string
     */
    public function getExpression() {
        return (string)$this->_expression;
    }

	/**
	 * @param string $parameters
	 */
	public function setParameters($parameters) {
		$this->_parameters = $parameters;
	}

	/**
	 * @return string
	 */
	public function getParameters() {
		return $this->_parameters;
	}

    public function __toString() {
        return $this->getExpression();
    }

}