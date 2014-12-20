<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace PSHD;

/**
 * Base class
 * Class PSHD
 * @package PSHD
 */
class PSHD {

	protected static $VALID_DRIVER = array('mysql','mysqli','pgsql','sqlite');
	protected static $VALID_OPTION = array('nameWrapper','idField','idFieldPlace','tablePrefix','tablePrefixPlace','limitEnable','limit','pageLimit','charJoin','charLeftJoin','charRightJoin','charInnerJoin','charSubSelect');

	public static function Autoload() { foreach(array('Exception','Literal','Result','Select','Where','Model') as $f) require_once sprintf("%s/%s.php",__DIR__,$f); }

	/** @var string */
	protected $_driver;
	/** @var string */
	protected $_dsn;
	/** @var string */
	protected $_user;
	/** @var string */
	protected $_password;
	/** @var string */
	protected $_database;
	/** @var string */
	protected $_charset;
	/** @var bool */
	protected $_persist = true;
	/** @var bool */
	protected $_autoCommit = true;
	/** @var \PDO */
	protected $_pdo;
	/** @var bool */
	protected $_connected = false;
	/** @var callable|null */
	protected $_exceptionHandler;
	/** @var bool */
	protected $_exceptionHandlerEnabled = true;
	/** @var callable|null */
	protected $_queryHandler;
	/** @var bool */
	protected $_queryHandlerEnabled = true;

	protected function _queryCallback($query, $parameters=array()) {
		if($this->_queryHandlerEnabled && is_callable($this->_queryHandler)) call_user_func($this->_queryHandler,$query,$parameters);
	}


	/** @var string */
	public $nameWrapper = '``';
	/** @var string */
	public $idField = 'id';
	/** @var string */
	public $idFieldPlace = '{I}';
	/** @var string */
	public $tablePrefix = '';
	/** @var string */
	public $tablePrefixPlace = '{P}';
	/** @var bool */
	public $limitEnable = true;
	/** @var int */
	public $limit = 1000000;
	/** @var int */
	public $pageLimit = 15;
	/** @var string */
	public $charJoin = '|';
	/** @var string */
	public $charLeftJoin = '<';
	/** @var string */
	public $charRightJoin = '>';
	/** @var string */
	public $charInnerJoin = '+';
	/** @var string */
	public $charSubSelect = '^';


	/**
	 * Creates instance of PSHD database wrapper
	 * @param $config
	 * @param bool $autoConnect (optional)
	 */
	public function __construct($config, $autoConnect=true) {
		foreach($config as $k=>$v) if(in_array($k,self::$VALID_OPTION)) $this->$k = $v;
		if(isset($config['user'])) $this->_user = $config['user'];
		if(isset($config['password'])) $this->_password = $config['password'];
		if(isset($config['driver']) && (isset($config['socket']) || isset($config['host']))) {
			$this->_driver = $config['driver'];
			$this->_dsn = $this->_driver.':';
			if(isset($config['socket'])) {
				$this->_dsn.= 'unix_socket='.$config['socket'].';';
			} else {
				$this->_dsn.= 'host='.$config['host'].';';
				if(isset($config['port'])) $this->_dsn.='port='.$config['port'].';';
			}
			if(isset($config['database'])) {
				$this->_dsn.='dbname='.$config['database'].';';
				$this->_database = $config['database'];
			}
			if(isset($config['charset'])) {
				$this->_dsn.='charset='.$config['charset'].';';
				$this->_charset = $config['charset'];
			}
		}
		elseif(isset($config['dsn'])) {
			$this->_dsn = $config['dsn'];
			$e = explode(':',$config['dsn']);
			$this->_driver = $e[0];
		} else {
			$this->exception(new Exception("You must either specify a [dsn] or [driver]+[host] in your config array"));
		}
		if(!in_array($this->_driver,self::$VALID_DRIVER)) {
			$this->exception(new Exception("Invalid driver: [".$this->_driver."]"));
		}
		if(isset($config['persist'])) $this->_persist = $config['persist']?true:false;
		if(isset($config['autoCommit'])) $this->_autoCommit = $config['autoCommit']?true:false;

		if($autoConnect) $this->connect();
	}

	public function getPDO() {
		return $this->_pdo;
	}

	public function isConnected() {
		return $this->_connected;
	}

	public function connect() {
		$attr = array(
			\PDO::ATTR_PERSISTENT => $this->_persist,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		);
		$this->_connected = false;
		try {
			$this->_pdo = new \PDO($this->_dsn, $this->_user, $this->_password, $attr);
		} catch (\Exception $pe) {
			$this->exception($pe);
			return $this;
		}
		$this->_connected = true;
		if ($this->_database) $this->execute("USE %s", $this->_database);
		if ($this->_charset) $this->execute("SET NAMES %s", $this->_charset);
		$this->setAutoCommit($this->_autoCommit);
		return $this;
	}

	/**
	 * @param callable|bool $callable
	 * @return $this
	 */
	public function setExceptionCallback($callable=true) {
		if($callable===true || $callable===false) $this->_exceptionHandlerEnabled = $callable;
		else {
			if($callable!==null) $this->_exceptionHandlerEnabled = true;
			$this->_exceptionHandler = $callable;
		}
		return $this;
	}

	/**
	 * @param callable|bool $callable (optional)
	 * @return $this
	 */
	public function setQueryCallback($callable=true) {
		if($callable===true || $callable===false) $this->_queryHandlerEnabled = $callable;
		else {
			if($callable!==null) $this->_queryHandlerEnabled = true;
			$this->_queryHandler = $callable;
		}
		return $this;
	}

	/**
	 * @param string|\Exception $message
	 * @param array $parameters
	 * @param \Exception|null $exception
	 * @throws \Exception
	 * @return $this
	 */
	public function exception($message, $parameters=array(), $exception=null) {
		if(is_object($message) && preg_match("/Exception$/",get_class($message))) {
			$exception = $message;
			$message = $exception->getMessage();
		}
		if(!$this->_exceptionHandlerEnabled || !is_callable($this->_exceptionHandler)) {
			if(!$exception) $exception = (new Exception($message))->getPrevious();
			throw $exception;
		} else {
			call_user_func($this->_exceptionHandler,$message,$parameters,$exception);
		}
		return null;
	}

	public function nameWrap($name) {
		if(strlen($this->nameWrapper)<1) $this->nameWrapper = '`';
		if(strlen($this->nameWrapper)<2) $this->nameWrapper[1] = $this->nameWrapper[0];
		$name = trim($name);
		$i = strlen($name)-1;
		if($i<0) return "";
		if($name[0]!=$this->nameWrapper[0]) {
			$name = $this->nameWrapper[0].$name;
			$i++;
		}
		if($name[$i]!=$this->nameWrapper[1]) $name.= $this->nameWrapper[1];
		return $name;
	}

	public function tableName($table) {
		return $this->tablePrefix.$table;
	}

	public function placeHolders($string) {
		return str_replace(array($this->idFieldPlace,$this->tablePrefixPlace),array($this->idField,$this->tablePrefix),$string);
	}

	public function literal($expression, $parameters=array()) {
		return new Literal($expression,$parameters);
	}

	public function where($expression, $parameters=array()) {
		return new Where($this, $expression,$parameters);
	}

	public function setAutoCommit($autoCommit=true) {
		$autoCommit = $autoCommit?1:0;
		$this->_pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, $autoCommit);
		return $this;
	}

	public function begin() {
		$this->_pdo->beginTransaction();
		return $this;
	}

	public function rollBack() {
		$this->_pdo->rollBack();
		return $this;
	}

	public function commit() {
		$this->_pdo->commit();
		return $this;
	}

	/**
	 * @param string $format
	 * @return int|null
	 */
	public function execute($format) {
		$a = func_get_args();
		$format = array_shift($a);
		if(count($a)>0) $format = vsprintf($format,$a);
		$format = $this->placeHolders($format);
		$this->_queryCallback($format);
		try {
			$r = $this->_pdo->exec($format);
			return $r;
		} catch(\Exception $e) {
			$this->exception($e);
		}
		return null;
	}

	/**
	 * @param string $format
	 * @return \PDOStatement
	 */
	public function prepare($format) {
		$a = func_get_args();
		$format = array_shift($a);
		if(count($a)>0) $format = vsprintf($format,$a);
		$format = $this->placeHolders($format);
		$this->_queryCallback($format,'prepare');
		try {
			$r = $this->_pdo->prepare($format);
			return $r;
		} catch(\Exception $e) {
			$this->exception($e);
		}
		return null;
	}

	/**
	 * @param string $format
	 * @param array $parameters
	 * @return \PDOStatement
	 */
	public function statement($format, $parameters=array()) {
		$a = func_get_args();
		$format = array_shift($a);
		if(count($a)>0) {
			if(is_array($a[count($a)-1])) $parameters = array_pop($a);
			if(count($a)>0) $format = vsprintf($format,$a);
		}
		$format = $this->placeHolders($format);
		$this->_queryCallback($format,$parameters);
		try {
			$r = $this->_pdo->prepare($format);
			$r->execute($parameters);
			return $r;
		} catch(\Exception $e) {
			$this->exception($e);
		}
		return null;
	}

	/**
	 * @param string $format
	 * @param array $parameters
	 * @return Result
	 */
	public function result($format, $parameters=array()) {
		$a = func_get_args();
		$format = array_shift($a);
		if(count($a)>0) {
			if(is_array($a[count($a)-1])) $parameters = array_pop($a);
			if(count($a)>0) $format = vsprintf($format,$a);
		}
		$format = $this->placeHolders($format);
		$this->_queryCallback($format,$parameters);
		return new Result($this,$format,$parameters);
	}

	public function insert($table, $data, $updateIfDuplicate=false) {
		$table = $this->tableName($table);
		$multi = false;
		foreach ($data as $dk => $dv) {
			if (is_array($dv)) $multi = true;
			if ((!$multi && is_numeric($dk)) || ($multi && is_numeric(array_keys($dv)[0]))) {
				$this->exception(new Exception("Passed in data array must be associative!",$data));
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
			$this->exception(new Exception("Data array is empty!"));
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
		if ($updateIfDuplicate) {
			$dup = "";
			foreach ($head as $h) $dup .= ",$h=VALUES($h) ";
			$q .= " ON DUPLICATE KEY UPDATE " . substr($dup, 1);
		}
		foreach ($data as $dv) foreach ($dv as $v) $p[] = $v;
		$this->statement($q,$p);
		return intval($this->_pdo->lastInsertId());
	}

	public function select($columns=array()) {
		$s = new Select($this);
		call_user_func_array(array($s, 'select'), func_get_args());
		return $s;
	}

	public function update($table, $data, $where, $insertIfNonExisting=false) {
		$eligibleForce = is_array($where);
		$where = new Where($this, $where);
		if(is_int($where)) $where = array($this->idField=>$where);
		if($insertIfNonExisting && !$eligibleForce) throw new Exception('When using with force insert, $where parameter should be an array!',0,null,array('table'=>$table,'data'=>$data,'where'=>$where));
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
		$q = "UPDATE ".$this->tableName($table)." SET ".$set." WHERE ".$whr->getClause()."";
		$p = array_merge($p, $whr->getParameters());
		$s = $this->statement($q,$p);
		$n = $s->rowCount();
		if($n<1 && $insertIfNonExisting && !$this->exists($table,$where)) {
			$this->insert($table,array_merge($where,$data));
			return true;
		}
		return $n;
	}

	public function delete($table, $where) {
		$whr = $this->where($where);
		$s = $this->statement("DELETE FROM %s WHERE %s", $this->tableName($table), $whr->getClause(), $whr->getParameters());
		return $s->rowCount();
	}

	public function exists($table,$where=array()) {
		return $this->select()->from($table)->where($where)->count()>0;
	}

	public function model($table,$where) {
		$model = explode('.',$table);
		$model = $model[count($model)-1].'_Model';
		//$model = str_replace(array('.'),array('_'),$table).'_Model';
		return $this->select('*')->from($table)->where($where)->model($model,$table);
	}

}