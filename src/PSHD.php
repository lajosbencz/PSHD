<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/ Brief tutorial
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace Lazos\PSHD;

/**
 * PDO utility wrapper for short hand operations
 * Class PSHD
 * @package Lazos\PSHD
 */
class PSHD
{

	/**
	 * Currently supported PDO drivers
	 * @var array
	 */
	public static $VALID_DRIVER = array(
		'mysql', // http://php.net/manual/en/ref.pdo-mysql.connection.php
		'pgsql', // http://php.net/manual/en/ref.pdo-pgsql.connection.php
		'sqlite', // http://php.net/manual/en/ref.pdo-sqlite.connection.php
	);

	/**
	 * Required config.
	 * You have three options:
	 * - dsn
	 * - driver
	 *  - socket
	 *  - host,[port]
	 * @var array
	 */
	public static $VALID_CONFIG = array(
		'dsn',
		'driver',
		'socket',
		'host',
		'port',
		'user',
		'password',
	);

	/**
	 * List of valid options
	 * @var array
	 */
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
	 * Get recognized driver
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
	 * Get underlying PDO object
	 * @return \PDO
	 */
	public function getPDO()
	{
		return $this->_pdo;
	}

	/**
	 * @var callable
	 */
	protected $_errorHandler = null;

	/**
	 * Set error handler. Assigned function will take Exception as parameter
	 * @param null|callable $handler
	 * @return $this
	 * @throws Exception
	 */
	public function setErrorHandler($handler = null)
	{
		if ($handler !== null && !is_callable($handler)) throw new Exception("Invalid handler passed in!");
		$this->_errorHandler = $handler;
		return $this;
	}

	/**
	 * Get error handler
	 * @return callable
	 */
	public function getErrorHandler()
	{
		return $this->_errorHandler;
	}


	/**
	 * @param \Exception|string $message
	 * @param array $parameters (optonal)
	 * @param \Exception $exception (optional)
	 * @throws Exception
	 * @throws \Exception
	 */
	public function handleError($message,$parameters=array(),$exception=null)
	{
		$isParent = function($class,$what){
			if(is_object($class)) {
				$class = new \ReflectionClass($class);
				while($parent = $class->getParentClass()){
					if($what==$parent->getName()) return true;
					$class = $parent;
				}
			}
			return false;
		};
		if($isParent($message,'Exception')) {
			/** @var \Exception $message */
			$exception = $message;
			$message = $exception->getMessage();
		} elseif($isParent($message,'PDOStatement')) {
			/** @var \PDOStatement $message */
			$message = $message->queryString;
		}
		if(!is_object($exception)) $exception = new Exception($message,0);
		if (is_callable($this->_errorHandler)) call_user_func($this->_errorHandler, $message, $parameters, $exception);
		else throw $exception;
	}


	/**
	 * @var string
	 */
	protected $_charset = 'utf8';

	/**
	 * Set character set
	 * @param string $charset
	 * @return $this
	 */
	public function setCharset($charset)
	{
		$this->_charset = $charset;
		return $this;
	}

	/**
	 * Get character set
	 * @return string
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
	 * Set database name, if second parameter is true the SQL command will also be issued
	 * @param string $database
	 * @param bool $use
	 * @return $this
	 */
	public function setDatabase($database, $use = false)
	{
		$this->_database = $database;
		if ($use) $this->execute("USE %s", $database);
		return $this;
	}

	/**
	 * Get database name
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->_database;
	}

	/**
	 * @var string
	 */
	protected $_idField = 'id';

	/**
	 * Id field used for look-ups
	 * @param string $name
	 * @return $this
	 */
	public function setIdField($name = 'id')
	{
		$this->_idField = $name;
		return $this;
	}

	/**
	 * Get idField
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
	 * Placeholder for idField. Will be replaced in raw SQL commands
	 * @param string $name
	 * @return $this
	 */
	public function setIdPlace($name = '{I}')
	{
		$this->_idPlace = $name;
		return $this;
	}

	/**
	 * Get idPlace
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
	 * Prefix for tables
	 * @param string $prefix
	 * @return $this
	 */
	public function setTablePrefix($prefix = "")
	{
		$this->_tablePrefix = $prefix;
		return $this;
	}

	/**
	 * Get tablePrefix
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
	 * The placeholder for tablePrefix. Will be replaced in raw SQL commands
	 * @param string $prefixPlace
	 * @return $this
	 */
	public function setTablePrefixPlace($prefixPlace = "{P}")
	{
		$this->_tablePrefixPlace = $prefixPlace;
		return $this;
	}

	/**
	 * Get tablePrefixPlace
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
	 * Default limit for commands with Select
	 * @param int $limit
	 * @return $this
	 */
	public function setDefaultLimit($limit = 1000000)
	{
		$this->_defaultLimit = $limit;
		return $this;
	}

	/**
	 * Get defaultLimit
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
	 * Default limit for pages with Select
	 * @param int $pageLimit
	 * @return $this
	 */
	public function setDefaultPageLimit($pageLimit = 25)
	{
		$this->_defaultPageLimit = $pageLimit;
		return $this;
	}

	/**
	 * Get defaultPageLimit
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
	 * If Select fields start with this character, the following word will be joined as a table.
	 * When the second characters is the same, tables roles will be inverted at the ON clause.
	 * @param string $char
	 */
	public function setJoinChar($char = "|")
	{
		$this->_joinChar = $char;
	}

	/**
	 * Get joinChar
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
	 * If Select fields start with this character, the following word will be joined as a table.
	 * When the second characters is the same, tables roles will be inverted at the ON clause.
	 * @param string $char
	 */
	public function setLeftJoinChar($char = "<")
	{
		$this->_leftJoinChar = $char;
	}

	/**
	 * Get leftJoinChar
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
	 * If Select fields start with this character, the following word will be joined as a table.
	 * When the second characters is the same, tables roles will be inverted at the ON clause.
	 * @param string $char
	 */
	public function setInnerJoinChar($char = "+")
	{
		$this->_innerJoinChar = $char;
	}

	/**
	 * Get innerJoinChar
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
	 * If Select fields start with this character, the following word will be joined as a table.
	 * When the second characters is the same, column roles will be inverted at the ON clause.
	 * @param string $char
	 */
	public function setRightJoinChar($char = ">")
	{
		$this->_rightJoinChar = $char;
	}

	/**
	 * Get rightJoinChar
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
	 * If Select fields start with this character, the following word will be selected as a sub-table.
	 * When the second characters is the same, column roles will be inverted at the WHERE clause.
	 * @param $char
	 * @return $this
	 */
	public function setSubSelectChar($char)
	{
		$this->_subSelectChar = $char;
		return $this;
	}

	/**
	 * Get subSelectChar
	 * @return string
	 */
	public function getSubSelectChar()
	{
		return $this->_subSelectChar;
	}

	/**
	 * Starts PDO transaction
	 * @return bool
	 */
	public function begin()
	{
		$this->setAutoCommit(0);
		return $this->_pdo->beginTransaction();
	}

	/**
	 * Roll PDO transaction back
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->_pdo->rollBack();
	}

	/**
	 * Commit PDO transaction
	 * @return bool
	 */
	public function commit()
	{
		$r = $this->_pdo->commit();
		$this->setAutoCommit(1);
		return $r;
	}

	/**
	 * @param string|array $dsn
	 * @param string|null $user (optional)
	 * @param string|null $password (optional)
	 * @throws \Exception
	 */
	public function __construct($dsn, $user = null, $password = null)
	{
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
		if (!in_array($this->_driver, self::$VALID_DRIVER)) throw new Exception("Invalid driver! (" . $this->_driver . ")");
		$attr = array(
			\PDO::ATTR_PERSISTENT => true,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		);
		try {
			$this->_pdo = new \PDO($dsn, $user, $password, $attr);
		} catch (\Exception $pe) {
			$this->handleError($pe);
		}
		if (strlen($this->_database) > 0) $this->execute("USE " . $this->_database);
		if (strlen($this->_charset) > 0) $this->execute("SET NAMES " . $this->_charset);
	}

	/**
	 * Convert table table to prefixed
	 * @param string $table
	 * @return string
	 */
	public function prefixTable($table)
	{
		if (is_string($this->getTablePrefix()) && strlen($this->getTablePrefix()) < 1) return str_replace($this->getTablePrefixPlace(), "", $table);
		if (!preg_match("/[\\s]/", $table) && strpos($table, $this->getTablePrefixPlace()) !== 0) return $this->getTablePrefix() . $table;
		return str_replace($this->getTablePrefixPlace(), $this->getTablePrefix(), $table);
	}

	/**
	 * Replace all occurrences of idPlace to idField in $str
	 * @param $str
	 * @return mixed
	 */
	public function replaceIdField($str)
	{
		if (strlen($this->getIdPlace()) > 0) return str_replace($this->getIdPlace(), $this->getIdField(), $str);
		return $str;
	}


	/**
	 * Set auto-commit
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
	 * Create literal SQL expression
	 * @param $expression
	 * @param array $parameters
	 * @param PSHD $pshd
	 * @return Literal
	 */
	public function literal($expression, $parameters = array(), $pshd = null)
	{
		if(!$pshd) $pshd = $this;
		return new Literal($expression, $parameters, $pshd);
	}

	/**
	 * Execute SQL query without PDO parameters. May be formatted as printf
	 * @param string $format
	 * @param mixed ...
	 * @return int|null
	 * @throws Exception
	 */
	public function execute($format)
	{
		$a = func_get_args();
		if (count($a) < 1) return null;
		$query = array_shift($a);
		if (count($a) > 0) $query = vsprintf($query, $a);
		try {
			$s = $this->_pdo->exec($this->replaceIdField($this->prefixTable($query)));
		} catch (\Exception $e) {
			if (is_callable($this->_errorHandler)) call_user_func($this->_errorHandler, $e);
			else throw new Exception($query, 0, $e);
			return null;
		}
		return $s;
	}

	/**
	 * Prepare PDOStatement. May be formatted as printf
	 * @param string $query
	 * @return \PDOStatement
	 * @throws Exception
	 */
	public function prepare($query)
	{
		$a = func_get_args();
		if (count($a) < 1) return null;
		$query = array_shift($a);
		if (count($a) > 0) $query = vsprintf($query, $a);
		try {
			$r = $this->_pdo->prepare($this->replaceIdField($this->prefixTable($query)));
		} catch (\Exception $e) {
			$this->handleError($query,array(),$e);
			return null;
		}
		return $r;
	}

	/**
	 * Execute SQL query and wrap results with utility class. Second input may only be array of PDO parameters
	 * @param string $query
	 * @param array $params (optional)
	 * @return Result|null
	 * @throws Exception
	 */
	public function query($query, $params = array())
	{
		try {
			$s = $this->_pdo->prepare($this->replaceIdField($this->prefixTable($query)));
			$s->execute($params);
		} catch (\Exception $e) {
			$this->handleError($query,$params,$e);
			return null;
		}
		return new Result($this, $s);
	}

	/**
	 * Create utility Where object
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
	 * Check if record exists in table
	 * @param string $table
	 * @param array $where
	 * @param array $parameters
	 * @return bool
	 */
	public function exists($table, $where, $parameters = array())
	{
		$w = $this->where($where, $parameters);
		$q = $this->select()->from($table)->where($w);
		$n = $q->count();
		return $n > 0;
	}

	/**
	 * Insert new record into table, on duplicates may be updated
	 * @param string $table
	 * @param array $data
	 * @param bool $onDuplicateUpdate (optional)
	 * @return int
	 * @throws Exception
	 */
	public function insert($table, $data, $onDuplicateUpdate = false)
	{
		$table = $this->prefixTable($table);
		$multi = false;
		foreach ($data as $dk => $dv) {
			if (is_array($dv)) $multi = true;
			if ((!$multi && is_numeric($dk)) || ($multi && is_numeric(array_keys($dv)[0]))) {
				$e = new Exception("Passed in data array must be associative!");
				if (is_callable($this->_errorHandler)) call_user_func($this->_errorHandler, $e);
				else throw $e;
				return -1;
			}
			break;
		}
		if (!$multi) $data = array($data);
		$head = array_keys($data[0]);
		foreach($head as &$h) {
			if(strpos($h,$table.'.')===0) continue;
			$h = $table.'.'.$h;
		}
		$count = count($data[0]);
		if ($count < 1) {
			$e = new Exception("Data array is empty!");
			if (is_callable($this->_errorHandler)) call_user_func($this->_errorHandler, $e);
			else throw $e;
			return -1;
		}
		$place = "";
		$p = array();
		$q = " INSERT INTO ";
		$q .= $table;
		$q .= ' ( ';
		$q .= implode(',', $head);
		$q .= ' )  VALUES ';
		foreach ($data[0] as $dv) {
			if (is_object($dv) && get_class($dv) == __NAMESPACE__ . '\\Literal') {
				/** @var $dv Literal */
				$place .= ',' . $dv->getExpression();
			} else {
				$place .= ',?';
			}
		}
		$place = ",(" . substr($place, 1) . ")";
		$q .= substr(str_repeat($place, count($data)), 1);
		if ($onDuplicateUpdate) {
			$dup = "";
			foreach ($head as $h) $dup .= ",$h=VALUES($h) ";
			$q .= " ON DUPLICATE KEY UPDATE " . substr($dup, 1);
		}
		foreach ($data as $dv) foreach ($dv as $v) $p[] = $v;
		try {
			$this->prepare($q)->execute($p);
		} catch (\Exception $e) {
			$this->handleError($q,$p,$e);
			return -1;
		}
		return intval($this->_pdo->lastInsertId());
	}

	public function select($fields = "*")
	{
		$s = new Select($this);
		call_user_func_array(array($s, 'select'), func_get_args());
		return $s;
	}

	/**
	 * Update record in table
	 * @param string $table
	 * @param array $data
	 * @param $where
	 * @param $forceInsert
	 * @return int|null
	 * @throws Exception
	 */
	public function update($table, $data, $where, $forceInsert=false)
	{
		if(is_int($where)) $where = array($this->_idField=>$where);
		if($forceInsert && !is_array($where)) throw new Exception('When using with force insert, $where parameter should be an array!',0,null,array('table'=>$table,'data'=>$data,'where'=>$where));
		$set = "";
		$p = array();
		foreach ($data as $k => $v) {
			if (is_object($v) && get_class($v) == __NAMESPACE__ . '\\Literal') {
				/** @var Literal $v */
				$set .= ", $k=" . $v->getExpression();
				foreach ($v->getParameters() as $vp) $p[] = $vp;
			} else {
				$set .= ", $k=?";
				$p[] = $v;
			}
		}
		$set = substr($set, 1);
		$whr = $this->where($where);
		$q = sprintf("UPDATE %s SET %s WHERE %s", $this->prefixTable($table), $set, $whr->getClause());
		$p = array_merge($p, $whr->getParameters());
		if(!$this->exists($table,$where) && $forceInsert) {
			$this->insert($table,array_merge($where,$data));
			return 0;
		}
		else {
			try {
				$c = $this->prepare($q);
				$c->execute($p);
				return $c->rowCount();
			} catch (\Exception $e) {
				$this->handleError($q,$p,$e);
			}
		}
		return null;
	}

	/**
	 * Delete record from table
	 * @param string $table
	 * @param int|string|Where $where
	 * @return int|null
	 * @throws Exception
	 */
	public function delete($table, $where)
	{
		$whr = $this->where($where);
		$q = sprintf("DELETE FROM %s WHERE %s", $this->prefixTable($table), $whr->getClause());
		$p = $whr->getParameters();
		try {
			$r = $this->prepare($q)->execute($p);
			return $r;
		} catch (\Exception $e) {
			$this->handleError($q,$p,$e);
		}
		return null;
	}

}