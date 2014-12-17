<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/ Brief tutorial
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace Lazos\PSHD;

/**
 * Where clauses
 * Class Where
 * @package Lazos\PSHD
 */
class Where
{

	/**
	 * Is array associative
	 * @param $arr
	 * @return bool
	 */
	public static function IsAssoc(&$arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * @var PSHD
	 */
	protected $_pshd = null;
	/**
	 * @var string|int|array
	 */
	protected $_clause = null;
	/**
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * @param PSHD $pshd
	 * @param string|array $clause (optional)
	 * @param array $parameters (optional)
	 */
	public function __construct($pshd, $clause = null, $parameters = array())
	{
		$this->_pshd = $pshd;
		if (isset($parameters) && !is_array($parameters)) $parameters = array($parameters);
		if (is_numeric($clause)) $clause = array($this->_pshd->getIdField() => $clause);
		if (is_array($clause)) {
			$where = "";
			$parameters = array();
			$isAssoc = self::IsAssoc($clause);
			if ($isAssoc) {
				foreach ($clause as $k => $v) {
					$where .= " AND $k".(is_string($v) && !is_numeric($v)?' LIKE ':' = ')."?";
					$parameters[] = $v;
				}
			} else {
				foreach ($clause as $v) {
					$w = new Where($pshd, $v);
					$where .= " AND " . $w->getClause();
					$parameters[] = $w->getParameters();
				}
			}
			$clause = substr($where, 4);
		}
		$this->setClause($clause);
		$this->setParameters($parameters);
	}

	/**
	 * Set WHERE clause. May begin with OR
	 * @param $string
	 * @return $this
	 */
	public function setClause($string)
	{
		$this->_clause = $string;
		return $this;
	}

	/**
	 * Get WHERE clause
	 * @return string
	 */
	public function getClause()
	{
		return $this->_clause;
	}

	/**
	 * Set PDO parameters
	 * @param array $prms (optional)
	 * @return $this
	 */
	public function setParameters($prms = array())
	{
		$this->_parameters = $prms;
		return $this;
	}

	/**
	 * Add PDO parameter
	 * @param $prm
	 * @return $this
	 */
	public function addParameter($prm)
	{
		$this->_parameters[] = $prm;
		return $this;
	}

	/**
	 * Get PDO parameters
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getClause();
	}

}