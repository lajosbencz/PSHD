<?php

namespace Lajosbencz\Pshd;

class Pshd {

    private static $_Default = null;

    public static function Connect($cfg) {
        if(!self::$_Default) {
            self::$_Default = new PshdConnector($cfg);
        }
    }

    public static function SetDefault() {

    }

}