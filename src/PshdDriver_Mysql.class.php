<?php

namespace LajosBencz\Pshd;

class PshdDriver_Mysql extends PshdDriver {

    protected $_fieldEncapsulator = "``";
    protected $_valueEncapsulator = "''";

    public function setAttributes() {
        parent::setAttributes();
    }

    public function getName() {
        return 'mysql';
    }

    public function getDsn($dbname, $host='127.0.0.1', $charset=null) {
        return 'mysql:'.parent::getDsn($dbname,$host,$charset);
    }

}