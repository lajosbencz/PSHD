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
        $q = array_shift($a);
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
        if($this->_run) return $this;
        if(!$this->_pdoStmnt) {
            try {
                $this->_pdoStmnt = $this->getConnector()->getPDO()->query($this->_query);
            }
            catch(\PDOException $e) {
                print "<pre>".$e->getMessage()."\r\n";
                print $this->_query;
                exit;
            }
        }
        if(is_array($params)) $this->setValues($params);
        foreach($this->_values as $k=>$v) $this->_pdoStmnt->bindValue($k,$v);
        try {
            $this->_pdoStmnt->execute();
            $this->_run = true;
        }
        catch(\PDOException $e) {
            print "<pre>".$e->getMessage();
            print $this->_query;
            exit;
        }
        return $this;
    }

    public function runAgain($params=null) {
        $this->_run = false;
        return $this->run($params);
    }

    public function cell() {
        $this->run();
        $this->_pdoStmnt->setFetchMode(\PDO::FETCH_NUM);
        return $this->_pdoStmnt->fetch()[0];
    }

    public function row() {
        $this->run();
        $this->_pdoStmnt->setFetchMode(\PDO::FETCH_NUM);
        return $this->_pdoStmnt->fetch();
    }

    public function assoc() {
        $this->run();
        $this->_pdoStmnt->setFetchMode(\PDO::FETCH_ASSOC);
        return $this->_pdoStmnt->fetch();
    }

    public function column() {
        $this->run();
        $r = array();
        $this->_pdoStmnt->setFetchMode(\PDO::FETCH_NUM);
        foreach($this->_pdoStmnt->fetchAll() as $s) $r[] = $s[0];
        return $r;
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
