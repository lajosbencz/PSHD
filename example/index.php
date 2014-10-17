<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

function predump($var) {
    print '<pre>';
    foreach(func_get_args() as $a) var_dump($a);
    print '</pre>';
}

function preprint($format) {
    if(!is_array($format)) $format = func_get_args();
    $a = $format;
    if(count($a)>0) {
        $format = array_shift($a);
        if(count($a)>0) $format = vsprintf($format,$a);
        print '<pre>';
        print $format;
        print '</pre>';
    }
}

require_once dirname(__DIR__).'/vendor/autoload.php';

$pshd = new \PSHD\PSHD("mysql:host=127.0.0.1;dbname=pshd","pshd","pshd");
predump($pshd->getIdField());
