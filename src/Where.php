<?php


namespace PSHD;


class Where {

	/**
	 * @param $arr
	 * @return bool
	 */
	public static function IsAssoc(&$arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

    /**
     * @var PSHD
     */
    protected $_pshd = null;
    /**
     * @var string|int|array
     */
    protected $_clause = null;
    /**
     * @var array
     */
    protected $_parameters = array();

    /**
     * @param PSHD $pshd
     * @param string|array $clause (optional)
     * @param array $parameters (optional)
     */
    public function __construct($pshd, $clause=null, $parameters=array()) {
        $this->_pshd = $pshd;
        if(isset($parameters) && !is_array($parameters)) $parameters = array($parameters);
        if(is_numeric($clause)) $clause = array($this->_pshd->getIdField() => $clause);
        if(is_array($clause)) {
            $where = "";
            $parameters = array();
			$isAssoc = self::IsAssoc($clause);
			if($isAssoc) {
				foreach($clause as $k=>$v) {
					$where.=" AND $k=?";
					$parameters[]=$v;
				}
			} else {
				foreach($clause as $v) {
					$w = new Where($pshd,$v);
					$where.= " AND ".$w->getClause();
					$parameters[] = $w->getParameters();
				}
			}
            $clause = substr($where,4);
        }
        $this->setClause($clause);
        $this->setParameters($parameters);
    }

    /**
     * @param $string
     * @return $this
     */
    public function setClause($string) {
        $this->_clause = $string;
        return $this;
    }

    /**
     * @return string
     */
    public function getClause() {
        return $this->_clause;
    }

    /**
     * @param array $prms (optional)
     * @return $this
     */
    public function setParameters($prms=array()) {
        $this->_parameters = $prms;
        return $this;
    }

    /**
     * @param $prm
     * @return $this
     */
    public function addDarameter($prm) {
        $this->_parameters[] = $prm;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->_parameters;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getClause();
    }

}