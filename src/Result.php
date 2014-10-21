<?php

namespace PSHD;


class Result {

    protected $_pshd = null;
	/**
	 * @var \PDOStatement
	 */
	protected $_pdoStmnt = null;
	protected $_colCount = 0;
	protected $_rowCount = 0;
	protected $_removePagingEnable = false;

	protected function _preProcess() {
		// override in children
	}
	protected function _postProcess() {
		if($this->_pdoStmnt) $this->_pdoStmnt->closeCursor();
	}

	protected function _removePagingColumn(&$row,$assoc=true) {
		if(!is_array($row) || $this->_pshd->isMS() && !$this->_removePagingEnable) return;
		if($assoc) {
			if(array_key_exists($this->_pshd->getIdPaging(), $row)) {
				$row[$this->_pshd->getIdPaging()] = null;
				unset($row[$this->_pshd->getIdPaging()]);
			}
		} else {
			array_shift($row);
		}
	}

    /**
     * @param PSHD $pshd
     * @param \PDOStatement|bool $pdoStmnt
	 * @param bool $removePagingEnable
     */
    public function __construct($pshd,$pdoStmnt=false,$removePagingEnable=false) {
        $this->_pshd = &$pshd;
		if($pdoStmnt) {
			$this->init($pdoStmnt);
		}
    }

	public function init(&$pdoStmnt,$removePagingEnable=false) {
		$this->_pdoStmnt = &$pdoStmnt;
		$this->_removePagingEnable = $removePagingEnable;
		$this->_colCount = $this->_pdoStmnt->columnCount();
		//$this->_rowCount = $this->_pdoStmnt->rowCount();
		$this->_rowCount = 0;
		return $this;
	}

    /**
     * @return int
     */
    public function rowCount() {
        return $this->_rowCount;
    }

    /**
     * @param int $idx
     * @return mixed|null
     */
    public function cell($idx=0) {
		$this->_preProcess();
        $r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
        $idx = max(0,min($this->_colCount-1,$idx));
		if($this->_removePagingEnable) $idx++;
		$this->_postProcess();
        return $r[$idx];
    }

    /**
     * @return mixed|null
     */
    public function row() {
		$this->_preProcess();
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		$this->_removePagingColumn($r,false);
		$this->_postProcess();
        return $r;
    }

    /**
     * @return mixed|null
     */
    public function assoc() {
		$this->_preProcess();
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_ASSOC);
		$this->_removePagingColumn($r);
		$this->_postProcess();
        return $r;
    }

    /**
     * @param int $idx
     * @return array
     */
    public function column($idx=0) {
		$this->_preProcess();
        $a = $this->_pdoStmnt->fetchAll(\PDO::FETCH_NUM);
        $idx = max(0,min($this->_colCount-1,$idx));
		if($this->_removePagingEnable) $idx++;
        $r = array();
		$this->_rowCount = 0;
        foreach($a as $v) {
			$r[] = $v[$idx];
			$this->_rowCount++;
		}
		$this->_postProcess();
        return $r;
    }

    /**
     * @param bool $assoc
     * @return array
     */
    public function table($assoc=true) {
		$this->_preProcess();
		$r = $this->_pdoStmnt->fetchAll($assoc?\PDO::FETCH_ASSOC:\PDO::FETCH_NUM);
		foreach($r as &$rv) $this->_removePagingColumn($rv);
		$this->_rowCount = count($r);
		$this->_postProcess();
        return $r;
    }

}