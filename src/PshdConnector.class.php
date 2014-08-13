<?php

namespace LajosBencz\Pshd;

class PshdConnector {

    public static function AvailableDrivers($check=null) {
        $r = array();
        foreach(glob(__DIR__."/PshdDriver_*") as $f) {
            unset($m);
            preg_match("/PshdDriver\\_(.+)\\.class\\.php$/",$f,$m);
            if(is_string($check)) {
                if($m[1]==$check) return true;
            }
            else $r[] = $m[1];
        }
        if(is_string($check)) return false;
        return $r;
    }

    protected $_pdo = null;
    protected $_driver = null;

    public function __construct($cfg) {
        $dn = strtoupper(substr($cfg['driver'],0,1)).substr($cfg['driver'],1);
        if(!self::AvailableDrivers($dn)) throw new \Exception("Invalid driver: ".$dn);
        $dn = "PshdDriver_".$dn;
        require_once __DIR__.'/'.$dn.'.class.php';
        $dn = '\\LajosBencz\\Pshd\\'.$dn;
        $this->_driver = new $dn($this);
        if(isset($cfg['password'])) $cfg['pass'] = $cfg['password'];
        if(isset($cfg['database'])) $cfg['dbname'] = $cfg['database'];
        if(
            array_key_exists('driver',$cfg) &&
            array_key_exists('dsn',$cfg)
        ) {
            $this->_pdo = new \PDO($cfg['dsn'],$cfg['user'],$cfg['pass']);
        } else if(
            array_key_exists('driver',$cfg) &&
            array_key_exists('dbname',$cfg)
        ) {
            $this->_pdo = new \PDO($this->getDriver()->getDsn($cfg['dbname'],$cfg['host'],$cfg['charset']), $cfg['user'], $cfg['pass']);
        } else {
            throw new \Exception("Invalid config!");
        }
        $this->getDriver()->setAttributes();
    }

    public function getPDO() {
        return $this->_pdo;
    }

    public function getDriver() {
        return $this->_driver;
    }

    public function query($strQuery) {
        return (new PshdQuery($this))->setQuery($strQuery);
    }

    public function exec($strQuery) {
        return $this->getPDO()->exec($strQuery);
    }

    public function insert($table, array $data, $mode=Pshd::ERROR) {
        return -1;
    }

    public function select($column) {
        return (new PshdSelect($this))->select($column);
    }

    public function update($table, array $data, $where, $mode=Pshd::ERROR) {
        return -1;
    }

    public function delete($table, $where) {
        return -1;
    }

}