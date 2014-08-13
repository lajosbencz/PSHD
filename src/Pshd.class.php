<?php

namespace LajosBencz\Pshd;

class Pshd {

    const ERROR = 0;
    const IGNORE = 1;
    const FORCE = 2;

    const COUNT_WHERE = 0;
    const COUNT_HAVING = 1;
    const COUNT_LIMIT = 2;

    private static $_Default = null;

    public static function Connect(array $cfg) {
        $c = new PshdConnector($cfg);
        if(!self::$_Default) {
            self::$_Default = $c;
        }
        return $c;
    }

    public static function SetDefault(PshdConnector $connector) {
        self::$_Default = $connector;
    }

    public static function GetDefault() {
        return self::$_Default;
    }

    public static function Log($line) {
        $a = func_get_args();
        if(count($a)>0) {
            $line = array_shift($a);
            if(count($a)>0) $line = vsprintf($line,$a);
            print "<pre>$line</pre>";
        }
    }

    public static function Dump($object) {
        print "<pre>";
        foreach(func_get_args() as $o) var_dump($o);
        print "</pre>";
    }

}
