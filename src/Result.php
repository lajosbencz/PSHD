<?php

namespace PSHD;


class Result {

    protected $_psdh = null;
    protected $_pdoStmnt = null;
    protected $_colCount = 0;
    protected $_rowCount = 0;

    protected function _preProcess() {
        // override in children
    }

    /**
     * @param PSHD $psdh
     * @param \PDOStatement $pdoStmnt
     */
    public function __construct($psdh,$pdoStmnt) {
        $this->_psdh = &$psdh;
        $this->_pdoStmnt = &$pdoStmnt;
        $this->_colCount = $this->_pdoStmnt->columnCount();
        $this->_rowCount = $this->_pdoStmnt->rowCount();
    }

    /**
     * @param bool $cached
     * @return int
     */
    public function rowCount($cached=true) {
        if($cached) return $this->_rowCount;
        return $this->_pdoStmnt->rowCount();
    }

    /**
     * @param int $idx
     * @return mixed|null
     */
    public function cell($idx=0) {
        $this->_preProcess();
        if($this->_rowCount<1) return null;
        $r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
        $idx = max(0,min($this->_colCount-1,$idx));
        return $r[$idx];
    }

    /**
     * @return mixed|null
     */
    public function row() {
        $this->_preProcess();
        if($this->_rowCount<1) return null;
        return $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
    }

    /**
     * @return mixed|null
     */
    public function assoc() {
        $this->_preProcess();
        if($this->_rowCount<1) return null;
        return $this->_pdoStmnt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $idx
     * @return array
     */
    public function column($idx=0) {
        $this->_preProcess();
        if($this->_rowCount<1) return array();
        $a = $this->_pdoStmnt->fetchAll(\PDO::FETCH_NUM);
        $idx = max(0,min($this->_colCount-1,$idx));
        $r = array();
        foreach($a as $v) $r[] = $v[$idx];
        return $r;
    }

    /**
     * @param bool $assoc
     * @return array
     */
    public function table($assoc=true) {
        $this->_preProcess();
        if($this->_rowCount<1) return array();
        return $this->_pdoStmnt->fetchAll($assoc?\PDO::FETCH_ASSOC:\PDO::FETCH_NUM);
    }

}