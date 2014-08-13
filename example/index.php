<?php

ini_set('display_errors',1);
error_reporting(E_ALL&~(E_NOTICE|E_WARNING));

foreach(glob(__DIR__.'/../src/*.class.php') as $c) require_once $c;

use LajosBencz\Pshd\Pshd;

$db = Pshd::Connect(array(
    'driver'    => 'mysql',
    'database'  => 'pshd',
    'user'      => 'pshd',
    'pass'      => 'pshd',
));

?>

<pre>

<?php


$q2 = $db->query("SELECT bar.id bar_id, bar.val bar_val, foo.id foo_id, foo.val foo_val FROM bar LEFT JOIN foo ON 1=1");
var_dump($q2->table());

$q1 = $db->select("*")->from('foo');

var_dump(array(
    'cell' => $q1->runAgain()->cell(),
    'row' => $q1->runAgain()->row(),
    'assoc' => $q1->runAgain()->assoc(),
    'column' => $q1->runAgain()->column(),
    'table' => $q1->runAgain()->table(),
    'grid' => $q1->runAgain()->grid()
));
