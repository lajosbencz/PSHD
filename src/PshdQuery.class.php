<?php

namespace LajosBencz\Pshd;

class PshdQuery {

    private $_connector = null;
    private $_query = null;
    private $_queryProcessed = null;
    private $_values = null;
    private $_run = false;

    protected $_pdoStmnt = null;

    protected function _handleError($exception) {
        print "<pre>".$exception->getMessage()."</pre>";
        print "<pre>".$this->_queryProcessed."</pre>";
        print "<pre>";
        $this->_pdoStmnt->debugDumpParams();
        print "</pre>";
        exit;
    }

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

    public function setParams($values=null) {
        if(!is_array($values)) $this->_values = null;
        else foreach($values as $k=>$v) if(is_string($k)) $this->setParam($k,$v);
        return $this;
    }
    public function setParam($label,$value) {
        $this->_values[$label[0]==':'?'':':'.$label] = $value;
    }

    public function run($params=null) {
        if($this->_run) return $this;
        if(!$this->_pdoStmnt) {
            try {
                $this->_queryProcessed = $this->getConnector()->prefix($this->_query);
                $this->_pdoStmnt = $this->getConnector()->getPDO()->query($this->_queryProcessed);
            }
            catch(\PDOException $e) {
                $this->_handleError($e);
            }
        }
        if(is_array($params)) $this->setParams($params);
        foreach($this->_values as $k=>$v) $this->_pdoStmnt->bindValue($k,$v);
        try {
            $this->_pdoStmnt->execute();
            $this->_run = true;
        }
        catch(\PDOException $e) {
            $this->_handleError($e);
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
