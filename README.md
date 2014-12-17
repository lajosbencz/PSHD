PSHD
====
PDO Short Hand Database
-----------------------

An OOP attempt to simplify common SQL commands.

### Instance
```php
require_once PATH_TO_COMPOSER_VENDOR.'/autoload.php';

use Lazos\PSHD;

$db = new PSHD(array(
	'driver' => 'mysql',
	'host' => 'localhost'
));
```

### Exception handling
```php
$db->setErrorHandler(function($message, $parameters, $innerException) {
	// handle error, original exceptions will be suppressed
});
$db->setErrorHandler(null); // clear handler, exceptions not suppressed
```

### Basic
```php
$pdoStatement = $db->prepare("SELECT * FROM `%s` WHERE `name` LIKE ? OR `name` LIKE ?",$tableName);
$pdoStatement->execute(array('some', 'parameters'));
***
$db->execute("UPDATE `%s` SET `when` = NOW()", $tableName);
```

### CRUD
```php
$db->insert($tableName,array(
	'col1' => $val1,
	'col2' => $val2,
));

$db->select()
```