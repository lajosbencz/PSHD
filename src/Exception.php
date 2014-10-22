<?php

// foo

namespace PSHD;

class Exception extends \Exception {

    public function __construct($message="",$code=0,$exception=null) {
        parent::__construct($message,$code,$exception);
    }

}