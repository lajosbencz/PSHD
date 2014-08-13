<?php

namespace LajosBencz\Pshd;


class PshdWhere {

    public function __construct($where, $value=null, $matchType='=') {
        Pshd::Dump($where,get_class($where));
    }

    public function a($where) {

        return $this;
    }
    public function o($where) {

        return $this;
    }

    public function __toString() {
        return "";
    }

}