<?php
/**
 * PSHD utility wrapper
 * @example http://pshd.lazos.me/example/
 * @author Lajos Bencz <lazos@lazos.me>
 */

namespace PSHD;

/**
 * Generic model
 * Class Where
 * @package PSHD
 */
class Model extends \stdClass {

	protected $_table;

	protected function _init() {
	}

	public function __construct($table) {
		$this->_table = $table;
		$a = func_get_args();
		array_shift($a);
		call_user_func_array(array($this,'_init'),$a);
	}

	public function getTable() {
		return $this->_table;
	}

}