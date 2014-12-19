<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace PSHD;

/**
 * Result set
 * Class Result
 * @package PSHD
 */

class Result
{

	/** @var PSHD */
	protected $_pshd = null;
	/** @var \PDOStatement */
	protected $_pdoStmnt = null;
	/** @var string */
	protected $_queryString = null;
	/** @var array */
	protected $_parameters = array();
	/** @var int */
	protected $_colCount = 0;
	/** @var int */
	protected $_rowCount = 0;

	protected function close()
	{
		if ($this->_pdoStmnt) $this->_pdoStmnt->closeCursor();
		return $this;
	}

	/**
	 * @param PSHD $pshd
	 * @param string $queryString
	 * @param array $parameters
	 */
	public function __construct($pshd, $queryString, $parameters=array())
	{
		$this->_pshd = &$pshd;
		$this->_queryString = $queryString;
		$this->_parameters = is_array($parameters)?$parameters:array();
		$this->run();
	}

	/**
	 * Run PDOStatement
	 * @return $this
	 */
	public function run()
	{
		$this->_colCount = null;
		$this->_rowCount = null;
		$this->_pdoStmnt = $this->_pshd->prepare($this->_queryString, $this->_parameters);
		if($this->_pdoStmnt->execute($this->_parameters)) {
			$this->_colCount = $this->_pdoStmnt->columnCount();
			$this->_rowCount = $this->_pdoStmnt->rowCount();
		}
		return $this;
	}

	public function getQueryString() {
		if(!$this->_pdoStmnt) return null;
		return $this->_pdoStmnt->queryString;
	}

	public function getParameters() {
		return $this->_parameters;
	}

	/**
	 * Get row count from PDOStatement
	 * @return int|null
	 */
	public function getRowCount()
	{
		if (!$this->_pdoStmnt) return null;
		return $this->_rowCount;
	}

	/**
	 * Get column count from PDOStatement
	 * @return int|null
	 */
	public function getColumnCount()
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
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		return $r[$idx];
	}

	/**
	 * Fetch numbered array
	 * @return array|null
	 */
	public function row()
	{
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_NUM);
		return $r;
	}

	/**
	 * Fetch associative array
	 * @return array|null
	 */
	public function assoc()
	{
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetch(\PDO::FETCH_ASSOC);
		return $r;
	}

	/**
	 * Fetch column
	 * @param int $idx
	 * @return array|null
	 */
	public function column($idx = 0)
	{
		if (!$this->_pdoStmnt) return null;
		$a = $this->_pdoStmnt->fetchAll(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		$r = array();
		foreach ($a as $v) $r[] = $v[$idx];
		return $r;
	}

	/**
	 * @param int $keyIdx
	 * @param int $valueIdx
	 * @return array|null
	 * @throws Exception
	 */
	public function keyValue($keyIdx=0, $valueIdx=1)
	{
		if (!$this->_pdoStmnt) return null;
		$a = $this->_pdoStmnt->fetchAll(\PDO::FETCH_NUM);
		if($this->_pdoStmnt->columnCount()<2) $this->_pshd->exception(new Exception("The query for keyValue results must have at least two columns!"));
		$r = array();
		foreach($a as $v) $r[$v[$keyIdx]] = $v[$valueIdx];
		return $r;
	}

	/**
	 * Fetch table
	 * @param bool $assoc
	 * @return array|null
	 */
	public function table($assoc = true)
	{
		if (!$this->_pdoStmnt) return null;
		$r = $this->_pdoStmnt->fetchAll($assoc ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM);
		return $r;
	}

	public function object($model='stdClass') {
		$a = func_get_args();
		array_shift($a);
		return $this->_pdoStmnt->fetchObject($model,$a);
	}

	public function collection($model='stdClass') {
		$a = func_get_args();
		array_shift($a);
		return $this->_pdoStmnt->fetchAll(\PDO::FETCH_OBJ,$model,$a);
	}
}