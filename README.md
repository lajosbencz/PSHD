PSHD
====

An OOP attempt to simplify common SQL commands.

Currently supported drivers: `mysql`, `pgsql`, `sqlite`

#### Initialize
With `dsn`
```php
$db = new PSHD\PSHD(array(
	'dsn' => 'sqlite:/var/www/test.db',
));
```

With `driver` and `socket`
```php
$db = new PSHD\PSHD(array(
	'driver' => 'mysql',
	'socket' => '/tmp/mysql.lock',
));
```

With `driver` and `host`
```php
$db = new PSHD\PSHD(array(
	'driver' => 'mysql',
	'host' => 'localhost',
));
```

#### Options
```php
$db = new PSHD\PSHD(array(

	/*
	 * required init options here...
	 */

	// can only be set when constructed
	'user' => 'usr',
	'password' => 'pwd',
	'database' => 'test',
	'charset' => 'utf8',
	'port' => 3306,
	'persist' => true,

	// can be changed with public properties later on
	'autoCommit' => true,
	'nameWrapper' => '`',
	'idField' => 'id',
	'idFieldPlace' => '{I}',
	'tablePrefix' => 'pfx_',
	'tablePrefixPlace' => '{P}',
	'defaultPageLimit' => 15,
	'joinChar' => '|',
	'leftJoinChar' => '<',
	'innerJoinChar' => '+',
	'rightJoinChar' => '>',
	'subSelectChar' => '^',
));

$db->autoCommit = true;
$db->nameWrapper = '`'
$db->idField = 'id';
$db->idFieldPlace = '{I}';
$db->tablePrefix = 'pfx_';
$db->tablePrefixPlace = '{P}';
$db->defaultPageLimit = 15;
$db->joinChar = '|';
$db->leftJoinChar = '<';
$db->innerJoinChar = '+';
$db->rightJoinChar = '>';
$db->subSelectChar = '^';
```


#### Methods

```php
// don't connect at construct
$db = new PSHD\PSHD($config, false);
$db->setExceptionHandler($handler);
$bool = $db->connect();

// get PDO class
$pdo = $db->getPDO();

// is connected to database
$bool = $db->isConnected();

// properly wrap names
$string = $db->nameWrap('unsafeName');
$string = $db->placeHolders("SELECT * FROM {P}test WHERE {I}%2=0");

// command callback, mainly for debug
$db->setQueryCallback(function($query, $parameters=array()) {
	// process it..
});
// pass in bool to enable/disable while keeping callback
$db->setQueryCallback(false);

// exception handler
$db->setExceptionHandler(function($query, $parameters=array(), $exception=null) {
	// handle it ...
});
// pass in bool to enable/disable while keeping callback
$db->setExceptionHandler(false);

// creates Literal wrapper, parameters can be set
$literal = $db->literal("expiry<NOW() OR status=?",array(2));

// creates Where wrapper, parameters can be set
$where = $db->where(4); // translates to 'WHERE <options.idField>=4'

// execute command without parameters
$bool = $db->execute("TRUNCATE TABLE {P}test");
// TRUNCATE TABLE pfx_test

// execute command with parameters
$bool = $db->query("DELETE FROM {P}test WHERE {I}%2=?",array(0));
// DELETE FROM pfx_test WHERE id%2=0

// creates PSHD statement
$statement = $db->statement("SELECT * FROM {P}test WHERE {I}%2=?");
$bool = $statement->execute(array(0));

// creates PSHD result
$result = $db->result("SELECT {I}, col1, col2 FROM {P}test WHERE {I}%2=?",array(0));
while(($array = $result->assoc())) {
	echo 'val1: '.$array['col1'];
	echo 'val2: '.$array['col2'];
}

// checks for record
$bool = $db->exists('test',"id%2=?",array(0));
// SELECT COUNT(*) FROM `pfx_test` WHERE id%2=0

// insert new record(s)
$db->insert('test',array(
	array(
		'{I}' => 42,
		'col1' => 'bar1',
		'col2' => 'bar2',
	),
	array(
    	'col1' => 'foo1',
    	'col2' => 'foo2',
    )
);
// INSERT INTO pfx_test (`id`,`col1`,`col2`) VALUES (42,'val1','val2')

// create chainable Select
$array = $db->select('col1')->select('col2')->from('test')->where(42)->assoc();
// SELECT col1, col2 FROM pfx_test WHERE id=42

// update records
$int = $db->update('test',array(
	'sessionId' => 'sessionuid',
	'comment' => "NOW()",
	'expires' => $db->literal("NOW() + INTERVAL ? MINUTE",array(5)),
),array(
	'userName' => 'usr',
));
// UPDATE `pfx_test`
// SET `sessionId`='sessionuid', `comment`='NOW()', `expires`=NOW() + INTERVAL 5 MINUTE
// WHERE `userName` LIKE 'usr'

// delete records
$int = $db->delete('test',"id!=?",array(42));
// DELETE FROM `pfx_test` WHERE id!=42
```

#### Exceptions
```php
// Passing a callable parameter will cause original exceptions to be suppressed
$db->setExceptionHandler(function($message, $parameters, $exception) {
	print '<pre>';
	print $message."\r\n";
	if(count($parameters)>0) print_r($parameters);
	print '</pre>';
	throw $exception;
});

// Passing false will disable suppression
$db->setErrorHandler(false);

// Passing true re-enable suppression
$db->setErrorHandler(true);

// Passing null will clear and permanently disable suppression
$db->setErrorHandler(null);
```

#### Transaction
```php
$db->begin();
try {
	// related SQL queries...
	$db->commit();
} catch(Exception $exception) {
	$db->revert();
}
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

$select->limit(10)->offset(20);
$select->limit(10,20);
$select->offset(20,10);
$select->page(3, 10);

$select->page($pageNum,$itemsPerPage);

// joins
$select = $db->select('t1.col1');
$select->select('<test t2.col1 t2_col1, col2 t2_col2');
$select->from('test t1');
$select->where("t1.{I}=?",array(42)));
$array = $select->assoc();
// SELECT t1.col1, t2.col1 t2_col1, t2.col2 t2_col2
// FROM pfx_test t1
// LEFT JOIN pfx_test t2 ON t2.id=t1.test_id
// WHERE t1.id = 42
```

Results of a select
```php
// first column of first row in result set
$mixed = $select->cell();
// fifth column of first row in result set
$mixed = $select->cell(4);

// first row of result set, numeric indexes
$array = $select->row();

// first row of result set, associative indexes
$array = $select->assoc();

// first column of result set
$array = $select->column();
// fifth column of result set
$array = $select->column(4);

// complete result set
$array = $select->table();

// COUNT(*)
$int = $select->count();
```

#### Models
```php

class foo_Model extends \PSHD\Model {
	protected function _private() {
		return array();
	}
	protected function _readonly() {
		return array('id');
	}
	protected function _public() {
		return array('col1','col2');
	}
	protected function _init() {
	}
}

namespace \otherNS {
	class other_Model extends \PSHD\Model {
		// ...
	}
}

$model = $db->model('test',5); // test_Model
$model = $db->select()->from('test')->where(5)->model(); // test_Model
$result = $db->result("SELECT col1,col2 FROM {P}test WHERE {I}=?",array(5));
$model = $result->model('otherNS\\other_Model',false); // other_Model

print $model->getId();
print $model['col1'];
print $model->col2;
$model->ool2 = 'new value';
$model->push();
```

