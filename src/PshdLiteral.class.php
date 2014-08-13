<?php

namespace LajosBencz\Pshd;


class PshdLiteral {

    protected $_literal;

    public function __construct($literal) {
        $this->_literal = $literal;
    }

    public function get() {
        return $this->_literal;
    }

    public function __toString() {
        return $this->get();
    }

}