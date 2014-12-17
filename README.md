PSHD
====

An OOP attempt to simplify common SQL commands.

Currently supported drivers:
* mysql
* pgsql
* sqlite

#### Instance
```php
require_once PATH_TO_COMPOSER_VENDOR.'/autoload.php';

use Lazos\PSHD;

$db = new PSHD(array(
	'driver' => 'mysql',
	'host' => 'localhost'
//	'database' => 'dbname'
//	'user' => 'uname'
//	'password' => 'pwd'
));
```

#### Exception handling
```php
$db->setErrorHandler(function($message, $parameters, $innerException) {
	// handle error, original exceptions will be suppressed
});
$db->setErrorHandler(null); // clear handler, exceptions not suppressed
```

#### Basic
```php
$pdoStatement = $db->prepare("SELECT * FROM `%s` WHERE `name` = ? OR `name` = ?",$table);
$pdoStatement->execute(array('some', 'parameters'));

$db->execute("UPDATE `%s` SET `when` = NOW()", $table);
```

#### Insert
```php
$id = $db->insert($table, array(
	'col1' => $val1,
	'col2' => $val2,
), $updateIfDuplicateKey);
```

#### Update
```php
$rowCount = $db->update($table, array(
	'col1' => $val1
	'col2' => $val2
),array(
 	'id' => $id
), $insertIfNonExisting);
```

#### Delete
```php
$rowCount = $db->delete($table, array(
 	'id' => $id
));
```

#### Exists
```php
$bool = $db->exists($table, array(
	'id' => $id
));
```

