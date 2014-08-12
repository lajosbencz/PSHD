<?php

namespace Lajosbencz\Pshd;

class PshdDriver_Mssql extends PshdDriver {

    protected $_fieldEncapsulate = "[]";

    public function getName() {
        return 'mssql';
    }

}
