<?php

namespace LajosBencz\Pshd;

class PshdDriver_Mssql extends PshdDriver {

    protected $_fieldEncapsulate = "[]";

    public function getName() {
        return 'Mssql';
    }

    public function getDsn($dbname, $host='127.0.0.1', $charset=null) {
        return 'dblib:'.parent::getDsn($dbname,$host,$charset);
    }

}
