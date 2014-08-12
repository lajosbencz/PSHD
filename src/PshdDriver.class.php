<?php

namespace Lajosbencz\Pshd;

abstract class PshdDriver {

    protected $_fieldEncapsulator = "  ";
    protected $_valueEncapsulator = "  ";

    abstract function getName();

    public function getFieldEncapsulator($closing=false) {
        return $this->_fieldEncapsulator[$closing?1:0];
    }

    public function getValueEncapsulator($closing=false) {
        return $this->_valueEncapsulator[$closing?1:0];
    }

}