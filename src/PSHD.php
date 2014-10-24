<?php

namespace LajosBencz\PSHD;

class PSHD
{

	public static $VALID_DRIVER = array(
		'mysql', // http://php.net/manual/en/ref.pdo-mysql.connection.php
		'pgsql', // http://php.net/manual/en/ref.pdo-pgsql.connection.php
		'sqlite', // http://php.net/manual/en/ref.pdo-sqlite.connection.php
	);

	public static $VALID_CONFIG = array(
		'dsn',
		// OR
		'driver',
			'socket',
			// OR
			'host',
			'port', // optional

		'user',
		'password',
	);

	public static $VALID_OPTION = array(
		'database',
		'charset',
		'idField',
		'idPlace',
		'tablePrefix',
		'tablePrefixPlace',
		'defaultLimit',
		'defaultPageLimit',
		'joinChar',
		'leftJoinChar',
		'innerJoinChar',
		'rightJoinChar',
		'subSelectChar',
	);

	/**
	 * @var string
	 */
	protected $_driver;

	/**
	 * @return string
	 */
	public function getDriver()
	{
		return $this->_driver;
	}

	/**
	 * @var \PDO
	 */
	protected $_pdo;

	/**
	 * @return \PDO
	 */
	public function getPDO()
	{
		return $this->_pdo;
	}

	/**
	 * @var bool|callable
	 */
	protected $_errorHandler = false;

	/**
	 * @param bool|callable $handler
	 * @return $this
	 * @throws \Exception
	 */
	public function setErrorHandler($handler)
	{
		if ($handler !== false && !is_callable($handler)) throw new \Exception("Invalid handler passed in! (not callable)");
		$this->_errorHandler = $handler;
		return $this;
	}


	/**
	 * @var string
	 */
	protected $_charset = 'utf8';

	/**
	 * string $name
	 * return $this
	 */
	public function setCharset($charset)
	{
		$this->_charset = $charset;
		return $this;
	}

	/**
	 * return string
	 */
	public function getCharset()
	{
		return $this->_charset;
	}

	/**
	 * @var string
	 */
	protected $_database = '';

	/**
	 * string $name
	 * return $this
	 */
	public function setDatabase($database)
	{
		$this->_database = $database;
		return $this;
	}

	/**
	 * return string
	 */
	public function getDatabase()
	{
		return $this->_database;
	}

	/**
	 * @var string
	 */
	protected $_idField = 'Id';

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setIdField($name = 'Id')
	{
		$this->_idField = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdField()
	{
		return $this->_idField;
	}

	/**
	 * @var string
	 */
	protected $_idPlace = '{I}';

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setIdPlace($name = '{I}')
	{
		$this->_idPlace = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdPlace()
	{
		return $this->_idPlace;
	}

	/**
	 * @var string
	 */
	protected $_tablePrefix = "";

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setTablePrefix($prefix = "")
	{
		$this->_tablePrefix = $prefix;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->_tablePrefix;
	}

	/**
	 * @var string
	 */
	protected $_tablePrefixPlace = "{P}";

	/**
	 * @param string $prefixPlace
	 * @return $this
	 */
	public function setTablePrefixPlace($prefixPlace = "{P}")
	{
		$this->_tablePrefixPlace = $prefixPlace;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTablePrefixPlace()
	{
		return $this->_tablePrefixPlace;
	}

	/**
	 * @var int
	 */
	protected $_defaultLimit = 1000000;

	/**
	 * @param int $limit
	 * @return $this
	 */
	public function setDefaultLimit($limit = 1000000)
	{
		$this->_defaultLimit = $limit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDefaultLimit()
	{
		return $this->_defaultLimit;
	}

	/**
	 * @var int
	 */
	protected $_defaultPageLimit = 25;

	/**
	 * @param int $pageLimit
	 * @return $this
	 */
	public function setDefaultPageLimit($pageLimit = 25)
	{
		$this->_defaultPageLimit = $pageLimit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDefaultPageLimit()
	{
		return $this->_defaultPageLimit;
	}


	/**
	 * @var string
	 */
	protected $_joinChar = "|";

	/**
	 * @param string $char
	 */
	public function setJoinChar($char = "|")
	{
		$this->_joinChar = $char;
	}

	/**
	 * @return string
	 */
	public function getJoinChar()
	{
		return $this->_joinChar;
	}

	/**
	 * @var string
	 */
	protected $_leftJoinChar = "<";

	/**
	 * @param string $char
	 */
	public function setLeftJoinChar($char = "<")
	{
		$this->_leftJoinChar = $char;
	}

	/**
	 * @return string
	 */
	public function getLeftJoinChar()
	{
		return $this->_leftJoinChar;
	}

	/**
	 * @var string
	 */
	protected $_innerJoinChar = "+";

	/**
	 * @param string $char
	 */
	public function setInnerJoinChar($char = "+")
	{
		$this->_innerJoinChar = $char;
	}

	/**
	 * @return string
	 */
	public function getInnerJoinChar()
	{
		return $this->_innerJoinChar;
	}

	/**
	 * @var string
	 */
	protected $_rightJoinChar = ">";

	/**
	 * @param string $char
	 */
	public function setRightJoinChar($char = ">")
	{
		$this->_rightJoinChar = $char;
	}

	/**
	 * @return string
	 */
	public function getRightJoinChar()
	{
		return $this->_rightJoinChar;
	}

	/**
	 * @var string
	 */
	protected $_subSelectChar = "^";

	/**
	 * @param $char
	 * @return $this
	 */
	public function setSubSelectChar($char)
	{
		$this->_subSelectChar = $char;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubSelectChar()
	{
		return $this->_subSelectChar;
	}

	/**
	 * @return bool
	 */
	public function begin()
	{
		$this->setAutoCommit(0);
		return $this->_pdo->beginTransaction();
	}

	/**
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->_pdo->rollBack();
	}

	/**
	 * @return bool
	 */
	public function commit()
	{
		$r = $this->_pdo->commit();
		$this->setAutoCommit(1);
		return $r;
	}

	/**
	 * @param string $query
	 * @param int|array $parameters
	 * @param \Exception|null $exception
	 * @throws \Exception
	 */
	protected function _errorHandler($query = "", $parameters = array(), $exception = null)
	{
		if ($exception != null) throw $exception;
		ob_start();
		print_r($parameters);
		$query .= "\r\n" . ob_get_clean();
		throw new \Exception($query);
	}

	/**
	 * @param string|array $dsn
	 * @param string|null $user (optional)
	 * @param string|null $password (optional)
	 * @throws Exception
	 */
	public function __construct($dsn, $user = null, $password = null)
	{
		$this->setErrorHandler(array($this, '_errorHandler'));
		if (is_array($dsn)) {
			foreach (self::$VALID_OPTION as $o) {
				if (isset($dsn[$o])) {
					$m = "set" . strtoupper(substr($o, 0, 1)) . substr($o, 1);
					$this->$m($dsn[$o]);
				}
			}
			$password = $dsn['password'];
			$user = $dsn['user'];
			if (isset($dsn['driver']) && strlen($dsn['driver']) > 0 && (isset($dsn['socket']) && strlen($dsn['socket']) > 0 || isset($dsn['host']) && strlen($dsn['host']) > 0)) {
				if (isset($dsn['socket']) && strlen($dsn['socket']) > 0) {
					$dsn = sprintf("%s:unix_socket=%s", $dsn['driver'], $dsn['socket']);
				} else {
					$port = 3306;
					if (isset($dsn['port']) && strlen($dsn['port']) > 0) $port = intval($dsn['port']);
					$dsn = sprintf("%s:host=%s;port=%s;", $dsn['driver'], $dsn['host'], $port);
				}
			} elseif (isset($dsn['dsn']) && strlen($dsn['dsn']) > 0) {
				$dsn = $dsn['dsn'];
			} else {
				throw new Exception("Insufficient input parameters! (no data on remote server)");
			}
		}
		$this->_driver = substr($dsn, 0, strpos($dsn, ':'));
		if(!in_array($this->_driver,self::$VALID_DRIVER)) throw new Exception("Invalid driver! (".$this->_driver.")");
		$attr = array(
			\PDO::ATTR_PERSISTENT => true,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		);
		try {
			$this->_pdo = new \PDO($dsn, $user, $password, $attr);
		} catch (\PDOException $pe) {
			$this->triggerError(null, null, $pe);
		}
		if (strlen($this->_database) > 0) $this->execute("USE " . $this->_database);
		if (strlen($this->_charset) > 0) $this->execute("SET NAMES " . $this->_charset);
	}


	/**
	 * @param string $query
	 * @param array $parameters
	 * @param \Exception|null $exception
	 * @throws \Exception
	 */
	public function triggerError($query, $parameters = array(), $exception = null)
	{
		$ps = "";
		if (is_array($parameters) && count($parameters)) {
			ob_start();
			print_r($parameters);
			$ps .= "\r\n";
			$ps .= ob_get_clean();
		}
		if ($exception === null) $exception = new Exception($query . $ps);
		$query = (string)$query;
		if (!is_callable($this->_errorHandler)) {
			ob_start();
			var_dump($parameters);
			$prmString = ob_get_clean();
			throw new Exception($query . "<br />\r\n" . $prmString, 0, $exception);
		}
		call_user_func($this->_errorHandler, $query, $parameters, $exception);
	}

	public function prefixTable($table)
	{
		if (is_string($this->getTablePrefix()) && strlen($this->getTablePrefix()) < 1) return str_replace($this->getTablePrefixPlace(), "", $table);
		if (!preg_match("/[\\s]/", $table) && strpos($table, $this->getTablePrefixPlace()) !== 0) return $this->getTablePrefix() . $table;
		return str_replace($this->getTablePrefixPlace(), $this->getTablePrefix(), $table);
	}

	public function replaceIdField($str) {
		if(strlen($this->getIdPlace())>0) return str_replace($this->getIdPlace(),$this->getIdField(),$str);
		return $str;
	}


	/**
	 * @param bool $on (optional)
	 * @return $this
	 */
	public function setAutoCommit($on = true)
	{
		$this->execute("SET AUTOCOMMIT ?", array($on ? 1 : 0));
		$this->_pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, $on ? 1 : 0);
		return $this;
	}

	/**
	 * @param $expression
	 * @param array $parameters
	 * @param PSHD $pshd
	 * @return Literal
	 */
	public function literal($expression, $parameters=array(), $pshd=null)
	{
		return new Literal($expression, $parameters, $pshd);
	}

	/**
	 * @param string $format
	 * @param mixed ...
	 * @return int|null
	 */
	public function execute($format)
	{
		$a = func_get_args();
		if(count($a)<1) return null;
		$query = array_shift($a);
		if(count($a)>0) $query = vsprintf($query,$a);
		try {
			$s = $this->_pdo->exec($this->replaceIdField($this->prefixTable($query)));
		} catch (\Exception $e) {
			$this->triggerError($query, array(), $e);
			return null;
		}

		return $s;
	}

	/**
	 * @param string $query
	 * @return \PDOStatement|null
	 */
	public function prepare($query)
	{
		try {
			$r = $this->_pdo->prepare($this->replaceIdField($this->prefixTable($query)));
		} catch (\Exception $e) {
			$this->triggerError($query, array(), $e);
			return null;
		}
		return $r;
	}

	/**
	 * @param string $query
	 * @param array $params (optional)
	 * @return Result|null
	 */
	public function query($query, $params = array())
	{
		try {
			$s = $this->_pdo->prepare($this->replaceIdField($this->prefixTable($query)));
			$s->execute($params);
		} catch (\Exception $e) {
			$this->triggerError($query, $params, $e);
			return null;
		}
		return new Result($this, $s);
	}

	/**
	 * @param string|int|array|Where $clause
	 * @param array $parameters (optional)
	 * @return Where
	 */
	public function where($clause, $parameters = array())
	{
		if (is_object($clause) && get_class($clause) == __NAMESPACE__ . '\\Where') {
			if (count($parameters) > 0) $clause->setParameters($parameters);
			return $clause;
		}
		return new Where($this, $clause, $parameters);
	}

	/**
	 * @param string $table
	 * @param array $where
	 * @param array $parameters
	 * @return bool
	 */
	public function exists($table, $where, $parameters = array())
	{
		$w = $this->where($where, $parameters);
		return $this->select()->from($table)->where($w)->count() > 0;
	}

	/**
	 * @param string $table
	 * @param array $data
	 * @param bool $onDuplicateUpdate (optional)
	 * @return int
	 */
	public function insert($table, $data, $onDuplicateUpdate = false)
	{
		$multi = false;
		foreach ($data as $dk => $dv) {
			if (is_array($dv)) $multi = true;
			if ((!$multi && is_numeric($dk)) || ($multi && is_numeric(array_keys($dv)[0]))) {
				$this->triggerError("Passed in data array must be associative!", array(), new \Exception("Passed in data array must be associative!"));
				return -1;
			}
			break;
		}
		if (!$multi) $data = array($data);
		$head = array_keys($data[0]);
		$count = count($data[0]);
		if ($count < 1) {
			$this->triggerError("Data array is empty", array(), new \Exception("Data array is empty"));
			return -1;
		}
		$place = "";
		//$place = ",(" . substr(str_repeat(",?", count($data[0])), 1) . ")";
		$p = array();
		$q = " INSERT INTO ";
		$q .= $this->prefixTable($table);
		$q .= ' ( ';
		$q .= implode(',', $head);
		$q .= ' )  VALUES ';
		foreach($data[0] as $dv) {
			if(is_object($dv) && get_class($dv)==__NAMESPACE__.'\\Literal') {
				/** @var $dv Literal */
				$place.= ','.$dv->getExpression();
				foreach($dv->getParameters() as $dp) $p[] = $dp;
			} else {
				$place.= ',?';
				$p[] = $dv;
			}
		}
		$place = ",(" . $place . ")";
		$q .= substr(str_repeat($place, count($data)), 1);
		if ($onDuplicateUpdate) {
			$dup = "";
			foreach ($head as $h) $dup .= ",$h=VALUES($h) ";
			$q .= " ON DUPLICATE KEY UPDATE " . substr($dup, 1);
		}
		foreach ($data as $dv) foreach ($dv as $v) $p[] = $v;
		$this->prepare($q)->execute($p);
		return intval($this->_pdo->lastInsertId());
	}

	public function select($fields = "*")
	{
		$s = new Select($this);
		call_user_func_array(array($s, 'select'), func_get_args());
		return $s;
	}

	/**
	 * @param string $table
	 * @param array $data
	 * @param $where
	 * @return int|null
	 */
	public function update($table, $data, $where)
	{
		$set = "";
		$p = array();
		foreach ($data as $k => $v) {
			if(is_object($v) && get_class($v)==__NAMESPACE__.'\\Literal') {
				/** @var Literal $v */
				$set.= ", $k=".$v->getExpression();
				foreach($v->getParameters() as $vp) $p[] = $vp;
			} else {
				$set .= ", $k=?";
				$p[] = $v;
			}
		}
		$set = substr($set, 1);
		$whr = $this->where($where);
		$q = sprintf("UPDATE %s SET %s WHERE %s", $this->prefixTable($table), $set, $whr->getClause());
		$p = array_merge($p, $whr->getParameters());
		return $this->prepare($q)->execute($p);
	}

	/**
	 * @param string $table
	 * @param int|string|Where $where
	 * @return int|null
	 */
	public function delete($table, $where)
	{
		$whr = $this->where($where);
		$q = sprintf("DELETE FROM %s WHERE %s", $this->prefixTable($table), $whr->getClause());
		return $this->prepare($q)->execute($whr->getParameters());
	}

}