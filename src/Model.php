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
abstract class Model implements \ArrayAccess
{

	/**
	 * @param $array
	 * @param $node
	 * @param mixed|null $value (optional)
	 * @param string $delimiter (optional)
	 * @param int $level (optional)
	 * @param bool $unset (optional)
	 * @return mixed|null
	 */
	public static function ArrayNode(&$array, $node, $value = null, $delimiter = '.', $level = 0, $unset = false)
	{
		if ($level == 0) $node = str_replace("[]", ".", str_replace(array("[", "]"), "", $node));
		$node = preg_replace("/\\.+/", ".", $node);
		$node = preg_replace("/^\\./", "", $node);
		$p = strpos($node, $delimiter);
		$push = false;
		if ($p == strlen($node) - 1) {
			$p = false;
			$node = substr($node, 0, -1);
			$push = true;
		}
		if ($p === false) {
			if ($value === null) {
				if (!$unset) return $array[$node];
				unset($array[$node]);
				return null;
			}
			$old = $array[$node];
			if ($push) $array[$node][] = $value;
			else $array[$node] = $value;
			return $old;
		}
		$k = substr($node, 0, $p);
		return self::ArrayNode($array[$k], substr($node, $p + 1), $value, $delimiter, $level + 1, $unset);
	}

	/**
	 * @param $array
	 * @param $node
	 * @param string $delimiter (optional)
	 * @return mixed|null
	 */
	public static function ArrayNodeGet(&$array, $node, $delimiter = '.')
	{
		return self::ArrayNode($array, $node, null, $delimiter, 0);
	}

	/**
	 * @param $array
	 * @param $node
	 * @param $value
	 * @param string $delimiter (optional)
	 * @return mixed|null
	 */
	public static function ArrayNodeSet(&$array, $node, $value, $delimiter = '.')
	{
		return self::ArrayNode($array, $node, $value, $delimiter, 0, true);
	}

	public function offsetExists($offset)
	{
		return isset($this->_container[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->_container[$offset]) ? $this->_container[$offset] : null;
	}

	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) $this->_container[] = $value;
		else $this->_container[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->_container[$offset]);
	}


	/** @var PSHD */
	protected $_pshd = null;
	/** @var string */
	protected $_table = null;
	/** @var int|string */
	protected $_id;
	/** @var array */
	protected $_container = array();


	/**
	 * Array of hidden property names
	 * @return array
	 */
	protected abstract function _private();

	/**
	 * Array of visible property names
	 * @return array
	 */
	protected abstract function _readonly();

	/**
	 * Array of editable property names
	 * @return array
	 */
	protected abstract function _public();

	/** @return void */
	protected abstract function _init();

	/**
	 * @param PSHD $pshd
	 * @param string $table
	 * @param array $data
	 * @param int $id (optional)
	 */
	public function __construct($pshd, $table, $data, $id = null)
	{
		$this->_pshd = $pshd;
		$this->_table = $table;
		$this->_id = $id;
		if (isset($data[$pshd->idField])) if (!$this->_id) $this->setId($data[$pshd->idField]);
		$this->_container = $data;
		$this->_init();
	}


	public function getPrivateFields()
	{
		return $this->_private();
	}

	public function getReadonlyFields()
	{
		return $this->_readonly();
	}

	public function getPublicFields()
	{
		return $this->_public();
	}

	public function getPullPushFields()
	{
		return array_merge($this->getPrivateFields(), $this->getPublicFields());
	}

	public function isPrivateField($field)
	{
		return in_array($field, $this->_private());
	}

	public function isReadonlyField($field)
	{
		return in_array($field, $this->_readonly());
	}

	public function isPublicField($field)
	{
		return in_array($field, $this->_public());
	}

	public function isPullPushField($field)
	{
		return in_array($field, $this->getPullPushFields());
	}

	public function canSet($property)
	{
		return $this->isPublicField($property);
	}

	public function canGet($field)
	{
		return $this->isPublicField($field) || $this->isReadonlyField($field);
	}

	public function getTable()
	{
		return $this->_table;
	}

	public function setId($id)
	{
		if ((is_numeric($id) && $id > 0) || (preg_match("/^[\\S]+$/i", trim($id)))) {
			$this->_id = $id;
		}
		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function push()
	{
		if (!$this->_id) $this->_pshd->exception(new Exception('Id was not set for this instance, cannot push to database'));
		$d = array();
		foreach ($this->getPullPushFields() as $field) $d[$field] = $this->_container[$field];
		$this->_pshd->update($this->getTable(), $d, $this->_id);
		return $this;
	}

	public function pull()
	{
		if (!$this->_id) $this->_pshd->exception(new Exception('Id was not set for this instance, cannot pull from database'));
		$this->_container = array_merge($this->_container, $this->_pshd->select($this->getPullPushFields())->from($this->getTable())->where($this->getId())->assoc());
		return $this;
	}

	public function __set($field, $value)
	{
		if (strlen($field) < 1 || $field == $this->_pshd->idField || !$this->canSet($field)) return $this->_pshd->exception(new \InvalidArgumentException("Cannot set [$field] on [" . get_class($this) . "]"));
		$mn = 'set' . strtoupper($field[0]) . substr($field, 1);
		if (method_exists($this, $mn)) return $this->$mn($value);
		return $this->_container[$field] = $value;
	}

	public function __get($field)
	{
		if (strlen($field) < 1 || !$this->canGet($field)) return $this->_pshd->exception(new \InvalidArgumentException("Cannot get [$field] of [" . get_class($this) . "]"));
		$mn = 'get' . strtoupper($field[0]) . substr($field, 1);
		if (method_exists($this, $mn)) return $this->$mn();
		if ($field == $this->_pshd->idField) return $this->_id;
		return $this->_container[$field];
	}

	public function toArray()
	{
		return $this->_container;
	}

	public function __toArray()
	{
		return $this->toArray();
	}

}