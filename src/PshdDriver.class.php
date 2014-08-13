<?php

namespace LajosBencz\Pshd;

abstract class PshdDriver {

    public static function GetEncapsulator($string,$closing=false) {
        $c = $closing?1:0;
        if(strlen($string)>$c) return $string[$c];
        return "";
    }

    protected $_connector;
    protected $_fieldEncapsulator = "";
    protected $_valueEncapsulator = "''";

    public function __construct(PshdConnector $connector) {
        $this->_connector = $connector;
    }

    public function setAttributes() {
        $this->_connector->getPDO()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public abstract function getName();

    public function getDsn($dbname, $host='127.0.0.1', $charset=null) {
        $r = 'host='.$host.';dbname='.$dbname;
        if(is_string($charset)) $r.=';charset='.$charset;
        return $r;
    }

    public function getFieldEncapsulator($closing=false) {
        return self::GetEncapsulator($this->_fieldEncapsulator,$closing);
    }

    public function encapsulateField($field) {
        return $this->getFieldEncapsulator().$field.$this->getFieldEncapsulator(true);
    }

    public function getValueEncapsulator($closing=false) {
        return self::GetEncapsulator($this->_valueEncapsulator,$closing);
    }

    public function encapsulateValue($value) {
        if(get_class($value)=="Lajosbencz\\Pshd\\PshdLiteral") {
            return $value;
        }
        return $this->getValueEncapsulator().$value.$this->getValueEncapsulator(true);
    }

}