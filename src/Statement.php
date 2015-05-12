<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/
 * @author Lajos Bencz <lazos@lazos.me>
 */
namespace PSHD;

/**
 * PDOStatement wrapper
 * Class Statement
 * @package PSHD
 */
class Statement {

	/** @var PSHD */
	protected $_pshd;
	/** @var \PDOStatement */
	protected $_pdoStatement;

	/**
	 * @param PSHD $pshd
	 * @param \PDOStatement $pdoStatement
	 */
	public function __construct($pshd, $pdoStatement) {
		$this->_pshd = $pshd;
		$this->_pdoStatement = $pdoStatement;
		$this->_pdoStatement->rowCount();
	}

	public function rowCount() {
		return $this->_pdoStatement->rowCount();
	}

	public function bindColumn($column, &$param, $maxlen=null, $driverdata=null) {
		return $this->_pdoStatement->bindColumn($column, $param, $maxlen, $driverdata);
	}

	public function bindParam($parameter, &$variable, $data_type=null, $length=null, $driver_options=null) {
		return $this->_pdoStatement->bindParam($parameter,$variable,$data_type,$length,$driver_options);
	}

	public function bindValue($parameter, &$value, $data_type=null) {
		return $this->_pdoStatement->bindValue($parameter, $value, $data_type);
	}

	public function closeCursor() {
		return $this->_pdoStatement->closeCursor();
	}

	public function columnCount() {
		return $this->_pdoStatement->columnCount();
	}

	public function debugDumpParams() {
		return $this->_pdoStatement->debugDumpParams();
	}

	public function errorCode() {
		return $this->_pdoStatement->errorCode();
	}

	public function errorInfo() {
		return $this->_pdoStatement->errorInfo();
	}

	public function execute($input_parameters=array()) {
		try {
			$r = $this->_pdoStatement->execute($input_parameters);;
			return $r;
		} catch(\Exception $e) {
			$this->_pshd->exception($this->_pdoStatement->queryString,$input_parameters,$e);
		}
		return false;
	}

	public function nextRowset() {
		return $this->_pdoStatement->nextRowset();
	}

	public function setAttribute($attribute, $value) {
		return $this->_pdoStatement->setAttribute($attribute,$value);
	}

	public function setFetchMode($mode) {
		return $this->_pdoStatement->setFetchMode($mode);
	}

	public function getColumnMeta($column) {
		return $this->_pdoStatement->getColumnMeta($column);
	}

	public function getQueryString() {
		return $this->_pdoStatement->queryString;
	}

	public function fetch($fetch_style=null, $cursor_orientation=\PDO::FETCH_ORI_NEXT, $cursor_offset=0) {
		return $this->_pdoStatement->fetch($fetch_style,$cursor_orientation,$cursor_offset);
	}

	public function fetchColumn($column_number=0) {
		return $this->_pdoStatement->fetchColumn($column_number);
	}

	public function fetchObject($class_name='stdClass',$ctor_args=null) {
		return $this->_pdoStatement->fetchObject($class_name,$ctor_args);
	}

	public function fetchAll($fetch_style=null, $fetch_argument=null, $ctor_args=array()) {
		return $this->_pdoStatement->fetchAll($fetch_style);
	}

}