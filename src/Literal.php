<?php

namespace PSHD;

class Literal {

    protected $_expression = null;

    /**
     * @param PSHD $shdb
     * @param bool $expression (optional)
     */
    public function __construct($shdb,$expression=false) {
        if($expression) $this->setExpression($expression);
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

    public function __toString() {
        return $this->getExpression();
    }

}