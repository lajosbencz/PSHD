<?php

namespace PSHD;


class PSHD {

    public static $VALID_DSN = array(
        'mysql',    // http://php.net/manual/en/ref.pdo-mysql.connection.php
        'pgsql',    // http://php.net/manual/en/ref.pdo-pgsql.connection.php
        'sqlite',   // http://php.net/manual/en/ref.pdo-sqlite.connection.php
        /* wrap paging for these drivers */
        'sqlsrv',   // http://php.net/manual/en/ref.pdo-sqlsrv.connection.php
        'sybase',   // http://php.net/manual/en/ref.pdo-dblib.connection.php
        'dblib',    // http://php.net/manual/en/ref.pdo-dblib.connection.php
    );

    /**
     * @var bool
     */
    protected $_isMS = false;
    /**
     * @return bool
     * @param bool $isMs
     */
    public function isMS($isMs=null) {
        if(is_bool($isMs)) $this->_isMS = $isMs;
        return $this->_isMS;
    }

    /**
     * @var \PDO
     */
    protected $_pdo;
    /**
     * @return \PDO
     */
    public function getPDO() {
        return $this->_pdo;
    }

    /**
     * @var bool|callable
     */
    protected $_errorHandler=false;
    /**
     * @param bool|callable $handler
     * @return $this
     * @throws \Exception
     */
    public function setErrorHandler($handler) {
        if($handler!==false && !is_callable($handler)) throw new \Exception("Invalid handler passed in! (not callable)");
        $this->_errorHandler = $handler;
        return $this;
    }


    /**
     * @var string
     */
    protected $_idField = 'Id';

    /**
     * @param string $name
     * @return $this
     */
    public function setIdField($name='Id') {
        $this->_idField = $name;
        return $this;
    }
    /**
     * @return string
     */
    public function getIdField() {
        return $this->_idField;
    }

	/**
	 * @var string
	 */
	protected $_idPaging = '_PSHD_PAGING_ID__HIDDEN';

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setIdPaging($name='_PSHD_PAGING_ID__HIDDEN') {
		$this->_idPaging = $name;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getIdPaging() {
		return $this->_idPaging;
	}

    /**
     * @var string
     */
    protected $_tablePrefix = "";
    /**
     * @param string $prefix
     * @return $this
     */
    public function setTablePrefix($prefix="") {
        $this->_tablePrefix = $prefix;
        return $this;
    }
    /**
     * @return string
     */
    public function getTablePrefix() {
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
    public function setTablePrefixPlace($prefixPlace="{P}") {
        $this->_tablePrefixPlace = $prefixPlace;
        return $this;
    }
    /**
     * @return string
     */
    public function getTablePrefixPlace() {
        return $this->_tablePrefixPlace;
    }

    /**
     * @var int
     */
    protected $_defaultLimit = 1000000;
    /**
     * @param int $limit
     */
    public function setDefaultLimit($limit=1000000) {
        $this->_defaultLimit = $limit;
    }
    /**
     * @return int
     */
    public function getDefaultLimit() {
        return $this->_defaultLimit;
    }

    /**
     * @var string
     */
    protected $_leftJoinChar = "<";
    /**
     * @param string $char
     */
    public function setLeftJoinChar($char="") {
        $this->_leftJoinChar = $char;
    }
    /**
     * @return string
     */
    public function getLeftJoinChar() {
        return $this->_leftJoinChar;
    }

    /**
     * @var string
     */
    protected $_innerJoinChar = "+";
    /**
     * @param string $char
     */
    public function setInnerJoinChar($char="") {
        $this->_innerJoinChar = $char;
    }
    /**
     * @return string
     */
    public function getInnerJoinChar() {
        return $this->_innerJoinChar;
    }

    /**
     * @var string
     */
    protected $_rightJoinChar = ">";
    /**
     * @param string $char
     */
    public function setRightJoinChar($char="") {
        $this->_rightJoinChar = $char;
    }
    /**
     * @return string
     */
    public function getRightJoinChar() {
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
    public function setSubSelectChar($char) {
        $this->_subSelectChar = $char;
        return $this;
    }
    /**
     * @return string
     */
    public function getSubSelectChar() {
        return $this->_subSelectChar;
    }

    /**
     * @param string $query
     * @param int|array $parameters
     * @param \Exception|null $exception
     * @throws \Exception
     */
    protected function _errorHandler($query="",$parameters=array(),$exception=null){
        if($exception!=null) throw $exception;
        ob_start();
        print_r($parameters);
        $query.="\r\n".ob_get_clean();
        throw new \Exception($query);
    }

    /**
     * @param string|array $dsn
     * @param string|null $user (optional)
     * @param string|null $password (optional)
     * @param string|null $charset (optional)
     */
    public function __construct($dsn,$user=null,$password=null,$charset='utf8') {
        $this->setErrorHandler(array($this,'_errorHandler'));
		$dbName = false;
        if(is_array($dsn)) {
			$dbName =(isset($dsn['database'])?$dsn['database']:false);
            $this->_idField = (isset($dsn['idField'])?:$this->_idField);
			$this->_idPaging = (isset($dsn['idPaging'])?:$this->_idPaging);
            $this->_tablePrefix = (isset($dsn['tablePrefix'])?:$this->_tablePrefix);
            $this->_tablePrefixPlace = (isset($dsn['tablePrefixPlace'])?:$this->_tablePrefixPlace);
            $this->_leftJoinChar = (isset($dsn['leftJoinChar'])?:$this->_leftJoinChar);
            $this->_innerJoinChar = (isset($dsn['innerJoinChar'])?:$this->_innerJoinChar);
            $this->_rightJoinChar = (isset($dsn['rightJoinChar'])?:$this->_rightJoinChar);
            $this->_subSelectChar = (isset($dsn['subSelectChar'])?:$this->_subSelectChar);
            $this->_defaultLimit = (isset($dsn['defaultLimit'])?intval($dsn['defaultLimit']):$this->_defaultLimit);
            if(isset($dsn['charset'])) $charset = $dsn['charset'];
            $password = $dsn['password'];
            $user = $dsn['user'];
            $dsn = $dsn['dsn'];
        }
        if(preg_match("/^(sqlsrv|sybase|dblib|odbc)/",$dsn)) $this->_isMS = true;
		if(strpos($dsn,'dblib')===0) {
			// attributes not supported
			$attr = array();
		} else {
			$attr = array(
				\PDO::ATTR_PERSISTENT => true,
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			);
		}
		if(!$this->isMS()) $attr[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES ".$charset;
        try {
            $this->_pdo = new \PDO($dsn,$user,$password,$attr);
        } catch(\PDOException $pe) {
            $this->triggerError(null,null,$pe);
        }
		if(strlen($dbName)>0) $this->execute("USE ".$dbName);
    }



    /**
     * @param string $query
     * @param array $parameters
     * @param \Exception|null $exception
     * @throws \Exception
     */
    public function triggerError($query,$parameters=array(),$exception=null) {
        $query = (string)$query;
        if(!is_callable($this->_errorHandler)) {
            ob_start();
            var_dump($parameters);
            $prmString = ob_get_clean();
            throw new \PSHD\Exception($query."<br />\r\n".$prmString,0,$exception);
        }
        call_user_func($this->_errorHandler,$query,$parameters,$exception);
    }

    public function prefixTable($table) {
        if(is_string($this->getTablePrefix()) && strlen($this->getTablePrefix())<1) return str_replace($this->getTablePrefixPlace(),"",$table);
        if(!preg_match("/[\\s]/",$table) && strpos($table,$this->getTablePrefixPlace())!==0) return $this->getTablePrefix().$table;
        return str_replace($this->getTablePrefixPlace(),$this->getTablePrefix(),$table);
    }


    /**
     * @param bool $on (optional)
     * @return $this
     */
    public function setAutoCommit($on=true) {
        $this->_pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT,$on?1:0);
        return $this;
    }

    /**
     * @param string $expression
     * @return Literal
     */
    public function literal($expression) {
        return new Literal($this,$expression);
    }

    /**
     * @param $query
     * @param array $params (optional)
     * @return int|null
     */
    public function execute($query,$params=array()) {
        try {
            $s = $this->_pdo->prepare($this->prefixTable($query));
            $s->execute($params);
        } catch (\Exception $e) {
            $this->triggerError($query,$params,$e);
            return null;
        }
        $n = $s->rowCount();
        $s = null;
        unset($s);
        return $n;
    }

    /**
     * @param string $query
     * @return \PDOStatement|null
     */
    public function prepare($query) {
        try {
        $r =$this->_pdo->prepare($this->prefixTable($query));
        } catch(\Exception $e) {
            $this->triggerError($query,array(),$e);
            return null;
        }
        return $r;
    }

    /**
     * @param string $query
     * @param array $params (optional)
     * @return Result|null
     */
    public function query($query,$params=array()) {
        try {
            $s = $this->_pdo->prepare($this->prefixTable($query));
            $s->execute($params);
        } catch(\Exception $e) {
            $this->triggerError($query,$params,$e);
            return null;
        }
        return new Result($this,$s);
    }

    /**
     * @param string|Where $clause
     * @param array $parameters (optional)
     * @return Where
     */
    public function where($clause,$parameters=array()) {
        if(is_object($clause) && get_class($clause)==__NAMESPACE__.'\\Where') {
            if(count($parameters)>0) $clause->setParameters($parameters);
            return $clause;
        }
        return new Where($this,$clause,$parameters);
    }

    /**
     * @param string $table
     * @param array $data
     * @param bool $onDuplicateUpdate (optional)
     * @return int
     */
    public function insert($table,$data,$onDuplicateUpdate=false) {
        $multi = false;
        foreach($data as $dk=>$dv) {
            if(is_array($dv)) $multi = true;
            if((!$multi && is_numeric($dk)) || ($multi && is_numeric(array_keys($dv)[0]))) $this->triggerError("",array(),new \Exception("Passed in data array must be associative!"));
            break;
        }
        if($multi) $head = array_keys($data[0]);
        else $head = array_keys($data);
        $count = $multi?count($data[0]):count($data);
        if($count<1) $this->triggerError("",array(),new \Exception("Data array is empty"));
        $vals = substr(str_repeat(',?',$count),1);
        if($multi) $vals = substr(str_repeat("),(".$vals,count($data)),3);
        $vals = "(".$vals.")";
        $duplicateUpdate = "";
        if($onDuplicateUpdate) {
            $dup = "";
            foreach($head as $h) $dup.=",$h=VALUES($h) ";
            $dup = substr($dup,1);
            $duplicateUpdate = "ON DUPLICATE KEY UPDATE ".$dup;
        }
        $q = sprintf("INSERT INTO %s (%s) VALUES %s %s",$this->prefixTable($table),implode(',',$head),$vals,$duplicateUpdate);
        $p = array();
        foreach($data as $dv) {
            if($multi) foreach($dv as $sdv) $p[]=$sdv;
            else $p[] = $dv;
        }
        $this->execute($q,$p);
        return intval($this->_pdo->lastInsertId());
    }

    public function select($fields="*") {
        $s = new Select($this);
        call_user_func_array(array($s,'select'),func_get_args());
        return $s;
    }

    /**
     * @param string $table
     * @param array $data
     * @param $where
     * @return int|null
     */
    public function update($table,$data,$where) {
        $set = "";
        $p = array();
        foreach($data as $k=>$v) {
            $set.= ", $k=?";
            $p[] = $v;
        }
        $set = substr($set,1);
        $whr = $this->where($where);
        $q = sprintf("UPDATE %s SET %s WHERE %s",$this->prefixTable($table),$set,$whr->getClause());
        $p = array_merge($p,$whr->getParameters());
        return $this->execute($q,$p);
    }

    /**
     * @param string $table
     * @param int|string|Where $where
     * @return int|null
     */
    public function delete($table,$where) {
        $whr = $this->where($where);
        $q = sprintf("DELETE FROM %s WHERE %s",$this->prefixTable($table),$whr->getClause());
        return $this->execute($q,$whr->getParameters());
    }

}