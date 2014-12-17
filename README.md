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

// init with dsn
$db = new PSHD(array(
	'dsn' => 'mysql:host=localhost;port=3306;dbname=testdb;charset=utf8',
//	'user' => 'user',
//	'password' => 'pwd'
);

// init from parameters
$db = new PSHD(array(
	'driver' => 'mysql',
	'host' => 'localhost',
//	'port' => 3306,
//	'database' => 'testdb',
//	'charset' => 'utf8'
//	'user' => 'user',
//	'password' => 'pwd'
);
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

#### Select

Creating a select
```php
$select = $db->select('col1,col2');
$select = $db->select('col1','col2');
$select = $db->select(array('col1','col2'));

$select->from($table);

$select->where("id = 42");
$select->where("id = ?",42);
$select->where("id = ?",array(42));
$select->where(array('id'=>42));
$select->where(42); // integer parameters will be converted to array($idField => $parameter)

$select->orderBy('when','DESC');
$select->orderBy('when',0);
$select->orderBy('when','-');
$select->orderBy('when','>');

$select->limit(10)->offset(1);
$select->limit(10,1);
$select->offset(1,10);

$select->page($pageNum,$itemsPerPage);
```

Results of a select
```php
$mixed = $select->cell(); // first column of first row in result set
$mixed = $select->cell(4); // fifth column of first row in result set

$array = $select->row(); // first row of result set, numeric indexes

$array = $select->assoc(); // first row of result set, associative indexes

$array = $select->column(); // first column of result set
$array = $select->column(4); // fifth column of result set

$array = $select->table(); // complete result set

$int = $select->count(); // COUNT(*)
```
