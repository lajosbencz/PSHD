<?php

namespace Lajosbencz\Pshd;

class PshdConnector {

    public static function AvailableDrivers(){
        return array(
            'mysql',
            'mssql',
            'pgsql'
        );
    }

    public function __construct($cfg) {
        if(
            array_key_exists('dsn',$cfg) &&
            array_key_exists('',$cfg) &&
            array_key_exists('',$cfg)
        ) {

        } else if(
            array_key_exists('',$cfg) &&
            array_key_exists('',$cfg) &&
            array_key_exists('',$cfg)
        ) {

        } else {

        }
    }

}