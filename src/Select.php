<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace PSHD;

/**
 * Chainable select
 * Class Select
 * @package PSHD
 */
class Select
{
	/**
	 * @var string
	 */
	protected static $RGX_ALIAS = "/^([^\\s]+)(\\s+AS)?\\s+([^\\s]+)$/i";

	/** @var PSHD */
	protected $_pshd = null;
	protected $_fields = array();
	protected $_from = "";
	protected $_where = array();
	protected $_filter = array();
	protected $_groupBy = array();
	protected $_orderBy = array();
	protected $_limit = null;
	protected $_offset = null;
	protected $_filterWhere = array();
	protected $_filterEnabled = array();

	protected $_join = array();
	protected $_joinCustom = "";
	protected $_sub = array();
	protected $_subQueries = array();
	protected $_subSelects = array();

	protected $_queryString = null;
	protected $_parameters = array();
	protected $_built = false;


	/**
	 * Birth of SQL queries
	 * @throws Exception
	 */
	protected function _build()
	{
		$this->_parameters = array();
		$qSelect = "";
		$qFrom = $this->_from;
		$qJoin = "";
		$qWhere = "";
		$qGroupBy = "";
		$qOrderBy = "";
		$qLimitOffset = "";
		if (count($this->_sub) > 0) $this->select($this->_from . "." . $this->_pshd->idField);
		if ((!is_array($this->_fields) || count($this->_fields) < 1) && count($this->_join) < 1 && count($this->_sub) < 1) $this->_fields = array('*');
		foreach ($this->_join as $jTable => $jv) {
			$jMode = "";
			$jInvert = false;
			$jTableAlias = $jTable;
			$jFrom = $qFrom;
			$jFromAlias = $qFrom;
			if (preg_match(self::$RGX_ALIAS, trim($jTable), $m)) {
				$jTable = $m[1];
				$jTableAlias = $m[3];
			}
			if (preg_match(self::$RGX_ALIAS, trim($jFrom), $m)) {
				$jFrom = $m[1];
				$jFromAlias = $m[3];
			}
			foreach ($jv as $jm) {
				$jMode = trim($jm);
				if (strlen($jMode) > 0) if (($jInvert = ($jMode[0] == '_'))) $jMode = substr($jMode, 1);
				break;
			}
			$qJoin .= sprintf(" %s JOIN %s AS %s ON %s.%s_%s=%s.%s ",
				$jMode,
				$jTable,
				$jTableAlias,
				$jInvert ? $jTableAlias : $jFromAlias, $jInvert ? $jFrom : $jTable, $this->_pshd->idField,
				$jInvert ? $jFromAlias : $jTableAlias, $this->_pshd->idField

			);
			if (count($jv) == 1 && isset($jv['*'])) {
				$this->_fields[] = $jTableAlias . ".*";
			} else {
				foreach ($jv as $jField => $v) {
					if(strlen($jField)>0) {
						$jFieldAlias = $jField;
						if (preg_match(self::$RGX_ALIAS, trim($jField), $m)) {
							$jField = $m[1];
							$jFieldAlias = $m[3];
						}
						$f = $jTableAlias . '.' . $jField;
						if ($jField != '*') {
							if ($jField == $jFieldAlias) {
								$f .= ' ' . $jTableAlias . '_' . $jField;
							} else {
								$f .= ' ' . $jFieldAlias;
							}
						}
						$this->_fields[] = $f;
					}
				}
			}
		}
		foreach($this->_fields as $fv) {
			$qSelect.= ','.$fv;
			if(is_object($fv) && get_class($fv)==__NAMESPACE__."\\Literal") {
				/** @var Literal $fv */
				foreach($fv->getParameters() as $fvp) $this->addParameter($fvp);
			}
		}
		$qSelect = substr($qSelect,1);
		$qSelect = preg_replace("/(^|\\,)\\./", "\$1" . $qFrom . ".", $qSelect);
		$qJoin.= $this->_joinCustom;
		foreach ($this->_filterWhere as $n=>$w) {
			if (isset($w['where'])) $w = $w['where'];
			elseif (isset($w['filter'])) {
				if(!$this->_filterEnabled[$n]) continue;
				$w = $w['filter'];
			}
			else $this->_pshd->exception(new Exception("Invalid WHERE data!"));
			/* @var $w Where */
			$c = trim($w->getClause());
			if (strpos($c, $this->_pshd->idField . " ") === 0 || strpos($c, $this->_pshd->idField . "=") === 0) $c = $this->_from . '.' . $c;
			if (!preg_match("/^(AND|OR)/i", $c)) $c = "AND ( " . $c . " )";
			$qWhere .= " " . $c . " ";
			foreach ($w->getParameters() as $wp) $this->addParameter($wp);
		}
		$qWhere = trim($qWhere);
		if (strlen($qWhere) > 0) {
			$qWhere = preg_replace("/^\\s*(AND|OR)/i", '', $qWhere);
			if (!preg_match("/^\\(?\\s*WHERE/", $qWhere)) $qWhere = "WHERE " . $qWhere;
			$this->_queryString .= $qWhere;
		}
		if (count($this->_groupBy) > 0) {
			$qGroupBy = "GROUP BY";
			foreach ($this->_groupBy as $field => $v) {
				if ($field[0] == '.') $field = $qFrom . $field;
				$qGroupBy .= " $field,";
			}
			$qGroupBy = substr($qGroupBy, 0, -1);
		}
		if (count($this->_orderBy) > 0) {
			$qOrderBy = "ORDER BY";
			foreach ($this->_orderBy as $field => $order) {
				if ($field[0] == '.') $field = $qFrom . $field;
				$qOrderBy .= " $field $order,";
			}
			$qOrderBy = substr($qOrderBy, 0, -1);
		}
		if (is_numeric($this->_limit) || is_numeric($this->_offset)) {
			$qLimitOffset = sprintf("LIMIT %d OFFSET %d", max(1, $this->_limit), max(0, $this->_offset));
		}
		$this->_queryString = $this->_pshd->placeHolders(trim(sprintf("SELECT %s FROM %s %s %s %s %s %s", $qSelect, $qFrom, $qJoin, $qWhere, $qGroupBy, $qOrderBy, $qLimitOffset)));
	}

	/**
	 * Handler of macros
	 * @param $field
	 * @param bool $alias
	 * @throws Exception
	 */
	protected function _addJoinAndSub($field, $alias = false)
	{
		if (is_object($field)) {
			if (get_class($field) == __NAMESPACE__ . "\\Select") {
				/** @var Select $field */
				if (!$alias) {
					$this->_pshd->exception(new Exception('Alias must be set for sub query!'));
					return;
				}
				$this->_subSelects[$alias] = $field;
				return;
			}
			else if (get_class($field) == __NAMESPACE__ . "\\Literal") {
				$this->_fields[] = $field;
				return;
			}
		}
		$field = (string)$field;
		$fc = $field[0];
		$invert = (strlen($field) > 1 && $fc === $field[1]) ? 1 : 0;
		switch ($fc) {
			case $this->_pshd->charJoin:
			case $this->_pshd->charLeftJoin:
			case $this->_pshd->charInnerJoin:
			case $this->_pshd->charRightJoin:
				$field = substr($field, 1 + $invert);
				$field = explode('.', $field);
				$m = "";
				if ($fc == $this->_pshd->charJoin) $m = '';
				elseif ($fc == $this->_pshd->charLeftJoin) $m = 'LEFT';
				elseif ($fc == $this->_pshd->charInnerJoin) $m = 'INNER';
				elseif ($fc == $this->_pshd->charRightJoin) $m = 'RIGHT';
				if (count($field) > 1) $this->join($field[0], explode(',', $field[1]), $m, $invert);
				else $this->join($field[0], array(''), $m, $invert);
				break;

			case $this->_pshd->charSubSelect:
				$field = substr($field, 1 + $invert);
				$field = explode('.', $field);
				if (count($field) > 1) foreach (explode(',', $field[1]) as $f) $this->sub($field[0], $f, $invert);
				else $this->sub($field[0], array('*'), $invert);
				break;

			default:
				if (!in_array($field, $this->_fields)) $this->_fields[] = $field;
				break;
		}
	}

	/**
	 * @param PSHD $pshd
	 */
	public function __construct($pshd)
	{
		$this->_pshd = $pshd;
	}

	/**
	 * Get assigned PSHD object
	 * @return PSHD
	 */
	public function getPSHD()
	{
		return $this->_pshd;
	}

	/**
	 * Set raw SQL query
	 * @param string $query
	 * @return $this
	 */
	public function setQueryString($query)
	{
		$this->_queryString = $query;
		return $this;
	}

	/**
	 * Get raw SQL query
	 * @return string
	 */
	public function getQueryString()
	{
		if(!$this->isBuilt()) $this->build();
		return $this->_queryString;
	}

	/**
	 * Set PDO parameters
	 * @param array $prms
	 * @return $this
	 */
	public function setParameters($prms = array())
	{
		$this->_parameters = $prms;
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
	 * Add PDO parameter
	 * @param mixed $prm
	 * @return $this
	 */
	public function addParameter($prm)
	{
		if (is_array($prm)) foreach ($prm as $p) $this->addParameter($p);
		else $this->_parameters[] = $prm;
		return $this;
	}

	/**
	 * Get FROM table
	 * @return string
	 */
	public function getFrom()
	{
		return $this->_from;
	}

	/**
	 * Select columns from table
	 * @param string|array|Select $fields,...
	 * @return $this
	 */
	public function select($fields = '*')
	{
		if ($fields === null) {
			$this->_fields = array();
			$this->_sub = array();
		} else {
			$args = func_get_args();
			foreach ($args as $k => $a) {
				if (is_array($a)) foreach ($a as $av) $this->select($av);
				elseif (is_object($a)) $this->_addJoinAndSub($a, $k);
				elseif (is_string($a) && strlen(trim($a)) > 0) $this->_addJoinAndSub($a);
			}
		}
		return $this;
	}

	/**
	 * Set FROM table
	 * @param string $table
	 * @param bool $prefix
	 * @return $this
	 */
	public function from($table, $prefix = true)
	{
		$this->_from = $prefix ? $this->_pshd->tableName($table) : $table;
		return $this;
	}

	/**
	 * JOIN table
	 * @param string $table Target table
	 * @param array $fields Fields to select
	 * @param string $mode LEFT, INNER, RIGHT
	 * @param bool $invert Invert column roles at ON clause
	 * @return $this
	 */
	public function join($table, $fields = array('*'), $mode = "", $invert = false)
	{
		if ($table === null) {
			$this->_join = array();
		} elseif(is_string($table) && (preg_match("/\\sON\\s/i",$table) || (func_num_args()==1 && preg_match("/^[\\S]+$/",trim($table))))) {
			if(!preg_match("/^((LEFT|INNER|RIGHT)[\\s]*)?JOIN/i",trim($table))) $table = "JOIN ".$table."";
			$this->_joinCustom.= " ".$table." ";
		} else {
			if (!is_array($fields)) $fields = array($fields);
			if (!is_array($this->_join)) $this->_join = array();
			if (empty($this->_join[$table]) || !is_array($this->_join[$table])) $this->_join[$table] = array();
			foreach ($fields as $f) $this->_join[$table][$f] = ($invert ? '_' : '') . $mode;
		}
		return $this;
	}

	/**
	 * Select sub-table
	 * @param string $table Target table
	 * @param array $fields Fields to select
	 * @param bool $invert Invert column roles at ON clause
	 * @return $this
	 */
	public function sub($table, $fields = array('*'), $invert = false)
	{
		if ($table === null) {
			$this->_sub = array();
		} else {
			if (is_object($table) && get_class($table) == __NAMESPACE__ . '\\Select') {
				$alias = (string)$fields;
				$this->_subQueries[$alias] = array('invert' => $invert, 'select' => $table);
			} else {
				if (!is_array($fields)) $fields = array($fields);
				if (!is_array($this->_sub)) $this->_sub = array();
				if (empty($this->_sub[$table]) || !is_array($this->_sub[$table])) $this->_sub[$table] = array();
				foreach ($fields as $f) $this->_sub[$table][$f] = $invert;
			}
		}
		return $this;
	}

	/**
	 * Append WHERE clause
	 * @param Where|string $where
	 * @param array $parameters
	 * @return $this
	 */
	public function where($where, $parameters = array())
	{
		if ($where === null) {
			foreach ($this->_filterWhere as $k => $v) if (isset($v['where'])) {
				$this->_filterWhere[$k] = null;
				unset($this->_filterWhere[$k]);
			}
		} else {
			$this->_filterWhere[] = array('where' => $this->_pshd->where($where, $parameters));
		}
		return $this;
	}

	/**
	 * Append named WHERE clause
	 * @param $name
	 * @param null $where
	 * @param array $parameters
	 * @return $this
	 */
	public function filter($name, $where = null, $parameters = array())
	{
		if ($name === null) {
			foreach ($this->_filterWhere as $k => $v) if (isset($v['filter'])) {
				$this->_filterWhere[$k] = null;
				unset($this->_filterWhere[$k]);
			}
		} else {
			if ($where === null) {
				$this->_filterWhere[$name] = null;
				unset($this->_filterWhere[$name]);
			} elseif($where===false) {
				$this->_filterEnabled[$name] = false;
			} elseif($where===true) {
				$this->_filterEnabled[$name] = true;
			} else {
				$this->_filterWhere[$name] = array('filter' => $this->_pshd->where($where, $parameters));
			}
		}
		return $this;
	}

	/**
	 * Group by column
	 * @param string $field
	 * @return $this
	 */
	public function groupBy($field)
	{
		if ($field === null) {
			$this->_groupBy = array();
		} else {
			$this->_groupBy[$field] = 1;
		}
		return $this;
	}

	/**
	 * Order by column
	 * @param $field
	 * @param string $order
	 * @return $this
	 */
	public function orderBy($field, $order = "ASC")
	{
		if ($field === null) {
			$this->_orderBy = array();
		} else {
			$field = trim($field);
			$e = explode(" ", $field);
			if (count($e) > 1) {
				$field = $e[0];
				$e[1] = strtoupper($e[1]);
				switch ($e[1]) {
					default:
					case 1:
					case 'ASC':
					case '+':
					case '<':
						$order = "ASC";
						break;
					case 0:
					case 'DESC':
					case '-':
					case '>':
						$order = "DESC";
						break;
				}
			}
			$this->_orderBy[$field] = $order;
		}
		return $this;
	}

	/**
	 * Limit rows
	 * @param $limit
	 * @param bool $offset
	 * @return $this
	 */
	public function limit($limit, $offset = false)
	{
		$this->_limit = $limit;
		if ($offset !== false) $this->_offset = $offset;
		return $this;
	}

	/**
	 * Offset rows
	 * @param $offset
	 * @param bool $limit
	 * @return $this
	 */
	public function offset($offset, $limit = false)
	{
		$this->_offset = $offset;
		if ($limit !== false) $this->_limit = $limit;
		return $this;
	}

	/**
	 * Select page
	 * @param int $page
	 * @param null $size
	 * @return $this
	 */
	public function page($page = 1, $size = null)
	{
		$page = max(1,$page);
		$size = is_int($size) ? $size : $this->_pshd->pageLimit;
		$this->limit($size, $size * ($page - 1));
		return $this;
	}

	/**
	 * Create literal SQL expression
	 * @param $expression
	 * @param array $parameters
	 * @return Literal
	 */
	public function literal($expression, $parameters = array())
	{
		return $this->getPSHD()->literal($expression, $parameters);
	}

	/**
	 * Reset state
	 * @return $this
	 */
	public function reset()
	{
		$this->_built = false;
		$this->_queryString = "";
		$this->_parameters = array();
		return $this;
	}

	public function isBuilt() {
		return $this->_built;
	}

	/**
	 * Build SQL query
	 * @return $this
	 */
	public function build()
	{
		$this->reset();
		$this->_build();
		$this->_built = true;
		return $this;
	}

	/**
	 * Run PDOStatement
	 * @param bool $force
	 * @return Result
	 * @throws Exception
	 */
	public function result($force = false)
	{
		if($force || !$this->isBuilt()) $this->build();
		return new Result($this->getPSHD(), $this->_queryString, $this->_parameters);
	}

	/**
	 * Fetch cell
	 * @param int $idx
	 * @return mixed|null
	 */
	public function cell($idx = 0)
	{
		return $this->result()->cell($idx);
	}

	/**
	 * Fetch numbered array
	 * @return array|null
	 */
	public function row()
	{
		return $this->result()->row();
	}

	/**
	 * Fetch associative array
	 * @return array|null
	 */
	public function assoc()
	{
		return $this->result()->assoc();
	}

	/**
	 * Fetch column
	 * @param int $idx (optional)
	 * @return array
	 */
	public function column($idx = 0)
	{
		return $this->result()->column($idx);
	}

	/**
	 * Return results as an associative column, key will be the first column of the result and the value the second
	 * @param int $keyIdx
	 * @param int $valueIdx
	 * @return array|null
	 * @throws Exception
	 */
	public function keyValue($keyIdx=0, $valueIdx=1)
	{
		return $this->result()->keyValue($keyIdx,$valueIdx);
	}

	/**
	 * Fetch table
	 * @param bool $assoc
	 * @return array
	 */
	public function table($assoc = true)
	{
		$d = $this->result()->table($assoc);
		foreach ($d as $dk => $dv) {
			foreach ($this->_sub as $sTable => $sFields) {
				$sTableAlias = $sTable;
				if (preg_match(self::$RGX_ALIAS, $sTable, $m)) {
					$sTable = $m[1];
					$sTableAlias = $m[3];
				}
				if (!is_array($sFields)) $sFields = array($sFields);
				$q = sprintf("SELECT %s FROM %s WHERE %s_%s=?", implode(',', array_keys($sFields)), $sTable, $this->_from, $this->_pshd->idField);
				$d[$dk][$sTableAlias] = $this->_pshd->query($q, array($dv[$this->_pshd->idField]))->table();
			}
			foreach ($this->_subSelects as $name => $select) {
				$nameAlias = $name;
				if (preg_match(self::$RGX_ALIAS, $name, $m)) {
					//$name = $m[1];
					$nameAlias = $m[3];
				}
				/** @var $select Select */
				$sel = clone $select;
				$sel->build();
				$d[$dk][$nameAlias] = $sel->table();
			}
			foreach ($this->_subQueries as $name => $select) {
				$nameAlias = $name;
				if (preg_match(self::$RGX_ALIAS, $name, $m)) {
					//$name = $m[1];
					$nameAlias = $m[3];
				}
				$invert = $select['invert'];
				$select = $select['select'];
				$sel = clone $select;
				/** @var $sel Select */
				if ($invert) {
					$sel->where(sprintf(" AND %s=?", $this->_pshd->idField), array($dv[$sel->getFrom() . '_' . $this->_pshd->idField]));
				} else {
					$sel->where(sprintf(" AND %s_%s=?", $this->_from, $this->_pshd->idField), array($dv[$this->_pshd->idField]));
				}
				$sel->build();
				$d[$dk][$nameAlias] = $sel->table();
			}
		}
		return $d;
	}

	/**
	 * Count results with COUNT(*).
	 * New query will be executed!
	 * @param bool $removeLimitOffset
	 * @return int
	 */
	public function count($removeLimitOffset = false)
	{
		$c = clone $this;
		$c->select(null)->select('COUNT(*)');
		if ($removeLimitOffset) $c->limit(null, null);
		return $c->result(true)->cell();
	}

	protected function _toHtml($t, $style = true)
	{
		$n = count($t);
		if ($n < 1) return '';
		$st = ' style="text-align: right; vertical-align: top; border:1px solid black; padding:2px; border-spacing: 0"';
		$sr = ' style="background: #eee; padding: 0;"';
		$sh = ' style="border-right:2px solid #aaa; padding: 0 3px;"';
		$sr2 = ' style="vertical-align: top;"';
		$sd = ' style="border-right:1px solid #eee; border-top:1px solid #ddd;"';
		$html = '<table' . ($style ? $st : '') . '>';
		$html .= '<tr' . ($style ? $sr : '') . '>';
		$html .= '<th colspan="999">' . $this->_from . ': ' . $n . ' rows</th>';
		$html .= '</tr>';
		$html .= '<tr' . ($style ? $sr : '') . '>';
		foreach ($t[0] as $h => $v)
			$html .= '<th' . ($style ? $sh : '') . '>' . $h . '</th>';
		$html .= '</tr>';
		foreach ($t as $l) {
			$html .= '<tr' . ($style ? $sr2 : '') . '>';
			foreach ($l as $v) {
				$html .= '<td' . ($style ? $sd : '') . '>';
				$html .= is_array($v) ? $this->_toHtml($v, $style) : $v;
				$html .= '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>';
		return $html;
	}

	/**
	 * Build an HTML table of the result
	 * @param bool $style (optional)
	 * @return string
	 */
	public function toHtml($style = true)
	{
		return $this->_toHtml($this->table(), $style);
	}

}