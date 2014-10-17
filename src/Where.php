<?php


namespace PSHD;


class Where {

    /**
     * @var PSHD
     */
    protected $_shdb = null;
    /**
     * @var string
     */
    protected $_clause = null;
    /**
     * @var array
     */
    protected $_parameters = array();

    /**
     * @param PSHD $shdb
     * @param string|array $clause (optional)
     * @param array $parameters (optional)
     */
    public function __construct($shdb, $clause=null, $parameters=array()) {
        $this->_shdb = $shdb;
        if(!is_array($parameters)) $parameters = array($parameters);
        if(is_numeric($clause)) $clause = array($this->_shdb->getIdField()=>$clause);
        if(is_array($clause)) {
            $where = "";
            $parameters = array();
            foreach($clause as $k=>$v) {
                $where.=" AND $k=?";
                $parameters[]=$v;
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