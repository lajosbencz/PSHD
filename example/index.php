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

$q1 = $db->select('*')->from('head')->whereId(1);

