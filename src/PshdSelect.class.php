<?php

namespace LajosBencz\Pshd;


class PshdSelect extends PshdQuery {

    protected $_from = null;
    protected $_columns = null;
    protected $_join = null;
    protected $_where = null;
    protected $_groupBy = null;
    protected $_having = null;
    protected $_orderBy = null;
    protected $_limit = null;
    protected $_offset = null;

    public function select($fields=null) {
        if(!$fields) $this->_columns = null;
        else {
            foreach(func_get_args() as $a) {
                if(strpos($a,',')>-1) foreach(explode(',',$a) as $c) $this->_columns[] = trim($c);
                else $this->_columns[] = trim($a);
            }
        }
        return $this;
    }

    public function from($table) {
        $this->_from = $table;
        return $this;
    }

    public function join($tableOn=null) {
        if(!$tableOn) $this->_join = null;
        else $this->_join[] = "JOIN ".$tableOn;
        return $this;
    }

    public function leftJoin($tableOn=null) {
        if(!$tableOn) $this->_join = null;
        else $this->_join[] = "LEFT JOIN ".$tableOn;
        return $this;
    }

    public function where($where=null) {
        if(!$where) $this->_where = null;
        else $this->_where[] = $where;
        return $this;
    }

    public function whereId($id) {
        $this->where(array('Id'=>$id));
        return $this;
    }

    public function groupBy($column=null) {
        if(!$column) $this->_groupBy = null;
        else $this->_groupBy[] = trim($column);
        return $this;
    }

    public function having($where=null) {
        if(!$where) $this->_having = null;
        else $this->_having[] = $where;
        return $this;
    }

    public function orderBy($column=null, $dir='') {
        if(!$column) $this->_orderBy = null;
        else $this->_orderBy[] = trim($column.' '.$dir);
        return $this;
    }

    public function limit($limit=null, $offset=false) {
        $this->_limit = $limit;
        if($offset!==false) $this->_offset = $offset;
        return $this;
    }

    public function offset($offset=null) {
        $this->_offset = $offset;
        return $this;
    }

    public function count($flags=0) {
        $c = clone $this;
        if(($flags>>Pshd::COUNT_LIMIT)&1) $c->limit()->offset();
        if(($flags>>Pshd::COUNT_HAVING)&1) $c->having();
        if(($flags>>Pshd::COUNT_WHERE)&1) $c->where();
        $fc = $this->_columns[0];
        if(count($this->_columns)<1 || strlen($this->_columns[0])<1) $fc = "*";
        $c->select()->select("COUNT(".$fc.")");
        return $c->close()->cell();
    }

    public function run($params=null) {
        $this->setQuery("SELECT %s FROM %s",implode(',',$this->_columns),$this->_from);
        return parent::run($params);
    }

}