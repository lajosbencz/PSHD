<?php

namespace LajosBencz\Pshd;

class PshdQuery {

    private $_connector = null;
    private $_query = null;
    private $_values = null;
    private $_run = false;

    protected $_pdoStmnt = null;

    public function __construct(PshdConnector $connector) {
        $this->_connector = $connector;
    }

    public function getConnector() {
        return $this->_connector;
    }

    public function setQuery($strQuery=null) {
        if(!$strQuery) {
            $this->_query = "";
            return $this;
        }
        unset($q);
        $a = func_get_args();
        array_unshift($a,$q);
        if(count($a)>0) $q = vsprintf($q,$a);
        $this->_query = $q;
        return $this;
    }

    public function setValues($values=null) {
        if(!is_array($values)) $this->_values = null;
        else foreach($values as $k=>$v) if(is_string($k)) $this->setValue($k,$v);
        return $this;
    }
    public function setValue($label,$value) {
        $this->_values[$label[0]==':'?'':':'.$label] = $value;
    }

    public function run($params=null) {
        Pshd::Dump(get_class($this));
        if($this->_run) return $this;
        if(!$this->_pdoStmnt) $this->_pdoStmnt = $this->getConnector()->getPDO()->query($this->_query);
        if(is_array($params)) $this->setValues($params);
        foreach($this->_values as $k=>$v) $this->_pdoStmnt->bindValue($k,$v);
        $this->_pdoStmnt->execute();
        $this->_run = true;
        return $this;
    }

    public function runAgain($params=null) {
        $this->_run = false;
        return $this->run($params);
    }

    public function cell() {
        $this->run();
        return null;
    }

    public function row() {
        $this->run();
        return null;
    }

    public function assoc() {
        $this->run();
        return null;
    }

    public function vrow() {
        $this->run();
        return null;
    }

    public function vassoc() {
        $this->run();
        return null;
    }

    public function grid() {
        $this->run();
        $this->_pdoStmnt->setFetchMode(\PDO::FETCH_NUM);
        return $this->_pdoStmnt->fetchAll();
    }

    public function table() {
        $this->run();
        $this->_pdoStmnt->setFetchMode(\PDO::FETCH_ASSOC);
        return $this->_pdoStmnt->fetchAll();
    }

    public function next() {
        return null;
    }

    public function close() {
        try {
            $this->_pdoStmnt->closeCursor();
        } catch(\Exception $e) {
            throw $e;
        }
        $this->_query = "";
        $this->_pdoStmnt = null;
        $this->_run = false;
        return $this;
    }

}
