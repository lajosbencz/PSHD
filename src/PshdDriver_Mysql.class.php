<?php

namespace Lajosbencz\Pshd;

class PshdDriver_Mysql extends PshdDriver {

    protected $_fieldEncapsulator = "``";
    protected $_valueEncapsulator = "''";

    public function getName() {
        return 'mysql';
    }

}