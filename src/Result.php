<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/ Brief tutorial
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace LajosBencz\PSHD;

/**
 * Utility wrapper for SQL results
 * Class Result
 * @package LajosBencz\PSHD
 */
class Result
{

	/**
	 * @var PSHD
	 */
	protected $_pshd = null;
	/**
	 * @var \PDOStatement
	 */
	protected $_pdoStmnt = null;
	/**
	 * @var int
	 */
	protected $_colCount = 0;
	/**
	 * @var int
	 */
	protected $_rowCount = 0;

	/**
	 * Execute before
	 */
	protected function _preProcess()
	{
		// override in children
	}

	/**
	 * Execute after
	 */
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
	 * Re-initialize from PDOStatement
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
	 * Get row count from PDOStatement
	 * @return int|null
	 */
	public function rowCount()
	{
		if (!$this->_pdoStmnt) return null;
		return $this->_rowCount;
	}

	/**
	 * Fetch cell
	 * @param int $idx
	 * @return mixed|null
	 */
	public function cell($idx = 0)
	{
		$this->_preProcess();
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		$this->_postProcess();
		return $r[$idx];
	}

	/**
	 * Fetch numbered array
	 * @return array|null
	 */
	public function row()
	{
		$this->_preProcess();
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		$this->_postProcess();
		return $r;
	}

	/**
	 * Fetch associative array
	 * @return array|null
	 */
	public function assoc()
	{
		$this->_preProcess();
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_ASSOC);
		$this->_postProcess();
		return $r;
	}

	/**
	 * Fetch column
	 * @param int $idx
	 * @return array
	 */
	public function column($idx = 0)
	{
		$this->_preProcess();
		if (!$this->_pdoStmnt) return null;
		$a = $this->_pdoStmnt->fetchAll(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		$r = array();
		foreach ($a as $v) $r[] = $v[$idx];
		$this->_postProcess();
		return $r;
	}

	/**
	 * Fetch table
	 * @param bool $assoc
	 * @return array
	 */
	public function table($assoc = true)
	{
		$this->_preProcess();
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetchAll($assoc ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM);
		$this->_postProcess();
		return $r;
	}

}