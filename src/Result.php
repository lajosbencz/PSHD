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
	/** @var Statement */
	protected $_statement = null;
	/** @var string */
	protected $_queryString = null;
	/** @var array */
	protected $_parameters = array();
	/** @var int */
	protected $_colCount = 0;
	/** @var int */
	protected $_rowCount = 0;
	/** @var string */
	protected $_table;

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
		$this->_table = null;
		$this->run();
	}

	/**
	 * Run PDOStatement
	 * @return $this
	 */
	public function run()
	{
		$matches = array();
		preg_match_all("/\\sFROM\\s/i",$this->_queryString,$matches,\PREG_OFFSET_CAPTURE);
		foreach($matches[0] as $m) {
			$paro = $parc =  0;
			for($i=0; $i<$m[1]; $i++) {
				if($this->_queryString[$i]=='(') $paro++;
				if($this->_queryString[$i]==')') $parc++;
			}
			if($paro!=$parc) continue;
			$p = $m[1] + 6;
			$table = "";
			for($i=$p;$i<strlen($this->_queryString);$i++) {
				if($this->_queryString[$i]==' ') break;
				$table.= $this->_queryString[$i];
			}
			$table = trim($table);
			if(preg_match("/^[a-z\\_][a-z\\.\\_]*$/i",$table)) $this->_table = $table;
			else $this->_table = null;
			break;
		}
		$this->_colCount = null;
		$this->_rowCount = null;
		$this->_statement = $this->_pshd->statement($this->_queryString, $this->_parameters);
		if($this->_statement->execute($this->_parameters)) {
			$this->_colCount = $this->_statement->columnCount();
			$this->_rowCount = $this->_statement->rowCount();
		}
		return $this;
	}

	public function getQueryString() {
		if(!$this->_statement) return null;
		return $this->_statement->getQueryString();
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
		if (!$this->_statement) return null;
		return $this->_rowCount;
	}

	/**
	 * Get column count from PDOStatement
	 * @return int|null
	 */
	public function getColumnCount()
	{
		if (!$this->_statement) return null;
		return $this->_rowCount;
	}

	/**
	 * Fetch cell
	 * @param int $idx
	 * @return mixed|null
	 */
	public function cell($idx = 0)
	{
		if (!$this->_statement) return null;
		$r = $this->_statement->fetch(\PDO::FETCH_NUM);
		$idx = max(0, min($this->_colCount - 1, $idx));
		return $r[$idx];
	}

	/**
	 * Fetch numbered array
	 * @return array|null
	 */
	public function row()
	{
		if (!$this->_statement) return null;
		$r = $this->_statement->fetch(\PDO::FETCH_NUM);
		return $r;
	}

	/**
	 * Fetch associative array
	 * @return array|null
	 */
	public function assoc()
	{
		if (!$this->_statement) return null;
		$r = $this->_statement->fetch(\PDO::FETCH_ASSOC);
		return $r;
	}

	/**
	 * Fetch column
	 * @param int $idx
	 * @return array|null
	 */
	public function column($idx = 0)
	{
		if (!$this->_statement) return null;
		$idx = max(0, $idx);
		if($this->_colCount>0) $idx = min($idx, $this->_colCount - 1);
		return $this->_statement->fetchColumn($idx);
	}

	/**
	 * @param int $keyIdx
	 * @param int $valueIdx
	 * @return array|null
	 * @throws Exception
	 */
	public function keyValue($keyIdx=0, $valueIdx=1)
	{
		if (!$this->_statement) return null;
		$a = $this->_statement->fetchAll(\PDO::FETCH_NUM);
		if($this->_statement->columnCount()<2) $this->_pshd->exception(new Exception("The query for keyValue results must have at least two columns!"));
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
		if (!$this->_statement) return null;
		$r = $this->_statement->fetchAll($assoc ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM);
		return $r;
	}

	public function object() {
		return $this->_statement->fetchObject('stdClass');
	}

	public function objectTable() {;
		$r = array();
		while(($o = $this->object())) {
			if(!is_object($o) || get_class($o)!='stdClass') break;
			$r[] = $o;
		}
		return $r;
	}
}