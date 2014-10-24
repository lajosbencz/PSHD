<?php

namespace LajosBencz\PSHD;


class Result
{

	protected $_pshd = null;
	/**
	 * @var \PDOStatement
	 */
	protected $_pdoStmnt = null;
	protected $_colCount = 0;
	protected $_rowCount = 0;

	protected function _preProcess()
	{
		// override in children
	}

	protected function _postProcess()
	{
		if ($this->_pdoStmnt) $this->_pdoStmnt->closeCursor();
	}

	/**
	 * @param PSHD $pshd
	 * @param \PDOStatement|bool $pdoStmnt
	 */
	public function __construct($pshd, $pdoStmnt = false)
	{
		$this->_pshd = & $pshd;
		if ($pdoStmnt) {
			$this->init($pdoStmnt);
		}
	}

	/**
	 * @param \PDOStatement $pdoStmnt
	 * @return $this
	 */
	public function init(&$pdoStmnt)
	{
		$this->_pdoStmnt = & $pdoStmnt;
		$this->_colCount = $this->_pdoStmnt->columnCount();
		$this->_rowCount = $this->_pdoStmnt->rowCount();
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function rowCount()
	{
		if(!$this->_pdoStmnt) return null;
		return $this->_rowCount;
	}

	/**
	 * @param int $idx
	 * @return mixed|null
	 */
	public function cell($idx = 0)
	{
		$this->_preProcess();
		if(!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		$this->_postProcess();
		return $r[$idx];
	}

	/**
	 * @return mixed|null
	 */
	public function row()
	{
		$this->_preProcess();
		if(!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		$this->_postProcess();
		return $r;
	}

	/**
	 * @return mixed|null
	 */
	public function assoc()
	{
		$this->_preProcess();
		if(!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_ASSOC);
		$this->_postProcess();
		return $r;
	}

	/**
	 * @param int $idx
	 * @return array
	 */
	public function column($idx = 0)
	{
		$this->_preProcess();
		if(!$this->_pdoStmnt) return null;
		$a = $this->_pdoStmnt->fetchAll(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		$r = array();
		foreach ($a as $v) $r[] = $v[$idx];
		$this->_postProcess();
		return $r;
	}

	/**
	 * @param bool $assoc
	 * @return array
	 */
	public function table($assoc = true)
	{
		$this->_preProcess();
		if(!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetchAll($assoc ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM);
		$this->_postProcess();
		return $r;
	}

}