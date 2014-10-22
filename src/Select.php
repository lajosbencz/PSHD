<?php


namespace PSHD;

class Select extends Result {

    protected $_fields = array();
    protected $_from = array();
    protected $_where = array();
    protected $_filter = array();
    protected $_groupBy = array();
    protected $_orderBy = array();
    protected $_limit = null;
    protected $_offset = null;

	protected $_join = array();
	protected $_sub = array();
	protected $_subQueries = array();

    protected $_query = null;
    protected $_parameters = array();
	protected $_run = false;


	protected function _preProcess() {
        parent::_preProcess();
        $this->run();
    }

    protected function _buildQuery() {
        $qSelect = "";
        $qFrom = $this->_from;
        $qJoin = "";
        $qWhere = "";
        $qGroupBy = "";
        $qOrderBy = "";
        $qLimitOffset = "";
        if(count($this->_sub)>0) $this->select($this->_from.".".$this->_pshd->getIdField());
        if(!is_array($this->_fields) || count($this->_fields)<1) $this->_fields = array('*');
        $qSelect.= implode(',',$this->_fields);
        foreach($this->_join as $jTable=>$jv) {
            $jMode = "LEFT";
            foreach($jv as $jm) {
                $jMode = $jm;
                break;
            }
            $qJoin.=sprintf(" %s JOIN %s ON %s.%s_%s=%s.%s ",
                $jMode,
                $jTable,
                $this->_from, $jTable, $this->_pshd->getIdField(),
                $jTable, $this->_pshd->getIdField()

            );
            foreach($jv as $jField => $v) {
                $f = $jTable.'.'.$jField;
                if($jField!='*') $f.=' '.$jTable.'_'.$jField;
                $this->_fields[] = $f;
            }
        }
        foreach(array_merge($this->_filter,$this->_where) as $w) {
            /* @var $w Where */
            $c = trim($w->getClause());
            if(strpos($c,$this->_pshd->getIdField()." ")===0 || strpos($c,$this->_pshd->getIdField()."=")===0) $c = $this->_from.'.'.$c;
            if(!preg_match("/^(AND|OR)/i",$c)) $c = "AND ( ".$c." )";
            $qWhere.= " ".$c." ";
            $this->addParameter($w->getParameters());
        }
        $qWhere = trim($qWhere);
        if(strlen($qWhere)>0) {
            $qWhere = preg_replace("/^\\s*(AND|OR)/i",'',$qWhere);
            if(!preg_match("/^\\(?\\s*WHERE/",$qWhere)) $qWhere = "WHERE ".$qWhere;
            $this->_query.= $qWhere;
        }
        if(count($this->_groupBy)>0) {
            $qGroupBy = "GROUP BY";
            foreach($this->_groupBy as $field=>$v) $qGroupBy.= " $field,";
            $qGroupBy = substr($qGroupBy,0,-1);
        }
        if(count($this->_orderBy)>0) {
            $qOrderBy = "ORDER BY";
            foreach($this->_orderBy as $field=>$order) $qOrderBy.= " $field $order,";
            $qOrderBy = substr($qOrderBy,0,-1);
        }
        if(is_numeric($this->_limit) || is_numeric($this->_offset)) {
            $qLimitOffset = sprintf("LIMIT %d OFFSET %d",max(1,$this->_limit),max(0,$this->_offset));
        }
		foreach(array($qSelect,$qFrom,$qJoin,$qWhere,$qGroupBy,$qOrderBy,$qLimitOffset) as &$q) $q = trim($q);
		$this->_removePagingEnable = false;
		$query = trim(sprintf("SELECT %s FROM %s %s %s %s %s %s", $qSelect, $qFrom, $qJoin, $qWhere, $qGroupBy, $qOrderBy, $qLimitOffset));
		$this->_query = $query;
    }

    protected function _addJoinAndSub($field,$alias=false) {
        if(is_object($field)) {
            if(get_class($field) == __NAMESPACE__."\\Select") {
                $this->_subQueries[$alias] = $field;
                return;
            }
        }
        $fc = $field[0];
        switch($fc) {
            case $this->_pshd->getLeftJoinChar():
            case $this->_pshd->getInnerJoinChar():
            case $this->_pshd->getRightJoinChar():
                $field = substr($field,1);
                $field = explode('.',$field);
                $m = "LEFT";
                if($fc==$this->_pshd->getInnerJoinChar()) $m='INNER';
                elseif($fc==$this->_pshd->getRightJoinChar()) $m='RIGHT';
                if(count($field)>1) foreach(explode(',',$field[1]) as $f) $this->join($field[0],$f,$m);
                else $this->join($field[0],array('*'),$m);
                break;

            case $this->_pshd->getSubSelectChar():
                $field = substr($field,1);
                $field = explode('.',$field);
                if(count($field)>1) foreach(explode(',',$field[1]) as $f) $this->sub($field[0],$f);
                else $this->sub($field[0],array('*'));
                break;

            default:
                if(!in_array($field,$this->_fields)) $this->_fields[] = $field;
                break;
        }
    }

    /**
     * @param PSHD $pshd
     */
    public function __construct($pshd) {
		parent::__construct($pshd);
    }

	public function getPSHD() {
		return $this->_pshd;
	}

    /**
     * @param string|array $fields,...
     * @return $this
     */
    public function select($fields='*') {
        if($fields===null) {
            $this->_fields = array();
            //$this->_join = array();
            $this->_sub = array();
        } else {
            $args = func_get_args();
            foreach($args as $k=>$a) {
                if(is_array($a)) foreach($a as $av) $this->select($av);
                elseif(is_object($a)) $this->_addJoinAndSub($a,$k);
                elseif(is_string($a) && strlen(trim($a))>0) $this->_addJoinAndSub($a);
            }
        }
        return $this;
    }
    public function from($table) {
        $this->_from = $this->_pshd->prefixTable($table);
        return $this;
    }
    public function join($table,$fields=array('*'),$mode="") {
		if($table===null) $this->_join = array();
        if(!is_array($fields)) $fields = array($fields);
        if(!is_array($this->_join)) $this->_join = array();
        if(empty($this->_join[$table]) || !is_array($this->_join[$table])) $this->_join[$table] = array();
        foreach($fields as $f) $this->_join[$table][$f] = $mode;
    }
    public function sub($table,$fields=array('*')) {
		if($table===null) $this->_sub = array();
        if(!is_array($fields)) $fields = array($fields);
        if(!is_array($this->_sub)) $this->_sub = array();
        if(empty($this->_sub[$table]) || !is_array($this->_sub[$table])) $this->_sub[$table] = array();
        foreach($fields as $f) $this->_sub[$table][$f] = 1;
    }
    public function where($where,$parameters=array()) {
        if($where===null) {
            $this->_where = array();
        } else {
            $this->_where[] = $this->_pshd->where($where,$parameters);
        }
        return $this;
    }
    public function filter($name,$where,$parameters=array()) {
        if($name===null) {
            $this->_filter = array();
        } else {
            $this->_filter[$name] = $this->_pshd->where($where,$parameters);
        }
        return $this;
    }
    public function groupBy($field) {
		if($field===null) {
			$this->_groupBy = array();
		} else {
			$this->_groupBy[$field] = 1;
		}
        return $this;
    }
    public function orderBy($field,$order="ASC") {
		if($field===null) {
			$this->_orderBy = array();
		} else {
			$field = trim($field);
			$e = explode(" ",$field);
			if(count($e)>1) {
				$field = $e[0];
				$e[1] = strtoupper($e[1]);
				switch($e[1]) {
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
    public function limit($limit,$offset=false) {
        $this->_limit = $limit;
        if($offset!==false) $this->_offset = $offset;
        return $this;
    }
    public function offset($offset, $limit=false) {
        $this->_offset = $offset;
        if($limit!==false) $this->_limit = $limit;
        return $this;
    }

    public function setQuery($query) {
        $this->_query = $query;
        return $this;
    }
    public function getQuery() {
        return $this->_query;
    }

    public function setParameters($prms) {
        $this->_parameters = $prms;
        return $this;
    }
    public function getParameters() {
        return $this->_parameters;
    }
    public function addParameter($prm) {
        if(is_array($prm)) foreach($prm as $p) $this->addParameter($p);
        else $this->_parameters[] = $prm;
        return $this;
    }

    public function reset() {
        $this->_run = false;
		//if($this->_pdoStmnt) $this->_pdoStmnt->closeCursor();
		return $this;
    }

	public function build() {
		$this->_buildQuery();
		return $this;
	}

    public function run($force=false) {
        if(!$this->_run || $force) {
			if(strlen(trim($this->_query))<1) $this->build();
			try {
				$stmnt = null;
				$stmnt = $this->_pshd->prepare($this->_query);
				$stmnt->execute($this->_parameters);
			} catch(\Exception $e) {
				$this->_pshd->triggerError($this->_query,$this->_parameters,$e);
				return $this;
			}
			parent::init($stmnt, $this->_removePagingEnable);
        }
        $this->_run = true;
        return $this;
    }

    public function cell($idx=0) {
        $this->limit(1,0);
		$this->_preProcess();
        return parent::cell($idx);
    }

    public function row() {
        $this->limit(1,0);
		$this->_preProcess();
        return parent::row();
    }

    public function assoc() {
        $this->limit(1,0);
		$this->_preProcess();
        $d = parent::assoc();
        return $d;
    }

    public function table($assoc=true) {
		$this->_preProcess();
        $d = parent::table($assoc);
        foreach($d as $dk=>$dv) {
            foreach($this->_sub as $sTable=>$sFields) {
                if(!is_array($sFields)) $sFields = array($sFields);
                $q = sprintf("SELECT %s FROM %s WHERE %s_%s=?",implode(',',array_keys($sFields)),$sTable,$this->_from,$this->_pshd->getIdField());
                $d[$dk][$sTable] = $this->_pshd->query($q,array($dv[$this->_pshd->getIdField()]))->table();
            }
            foreach($this->_subQueries as $name=>$select) {
                /** @var $select Select */
                $sel = clone $select;
                $sel->where(sprintf(" AND %s_%s=?",$this->_from,$this->_pshd->getIdField()),array($dv[$this->_pshd->getIdField()]));
                $select->run(true);
                $d[$dk][$name] = $select->table();
            }
        }
        return $d;
    }

	public function count($removeLimitOffset=false) {
		$c = clone $this;
		if($removeLimitOffset) $c->limit(null,null);
		return count($c->column());
	}

	public function literal($expression,$parameters=array()) {
		return new Literal($expression,$parameters,$this->getPSHD());
	}

    protected function _buildSub(&$data) {

    }

}