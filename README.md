MySQL OOP Class PHP (v.1.0)
------------

This is a simple to use MySQL class that easily bolts on to any existing PHP application, streamlining your MySQL interactions.

Setup
-----

**Database Credentials**

You will need to change some variable values in the Class, that represent those of your own database. Change the following -

```php
private $db_host = "localhost";  // Change as required
private $db_user = "username";  // Change as required
private $db_pass = "password";  // Change as required
private $db_name = "database";	// Change as required
```

Usage
-----

```php
<?php
	//Simply include this file on your page
	require_once("MySQL.class.php");

	//Set up all yor paramaters for connection
	$db = new Database();
  
?>
```

Example
-------

**Test MySQL**

Start by creating a test table in your database -

```mysql
CREATE TABLE IF NOT EXISTS tbl_test (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  PRIMARY KEY (id)
);

INSERT INTO tbl_test VALUES('','Name 1','name1@email.com');
INSERT INTO tbl_test VALUES('','Name 2','name2@email.com');
INSERT INTO tbl_test VALUES('','Name 3','name3@email.com');
```


**Select Example**

Use the following code to select * rows from the databse using this class

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$db->select('tbl_test'); // Table name
$res = $db->getResult();
print_r($res);
```

Use the following code to specify what is selected from the database using this class

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$db->select('tbl_test','id,name','name="Name 1"','id DESC'); // Table name, Column Names, WHERE conditions, ORDER BY conditions
$res = $db->getResult();
print_r($res);
```

**Join Example**

Start by creating another table in your database -

```mysql
CREATE TABLE IF NOT EXISTS tbl_child (
  id int(11) NOT NULL AUTO_INCREMENT,
  parentId int(11) NOT NULL,
  name varchar(255) NOT NULL,
  PRIMARY KEY (id)
);

INSERT INTO tbl_child VALUES('','1','Child 1');
INSERT INTO tbl_child VALUES('','1','Child 2');
INSERT INTO tbl_child VALUES('','2','Child 1');
```

Use the following code to select rows using a join in the database using this class

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$db->select('tbl_test','tbl_test.id,tbl_test.name,tbl_child.name','tbl_child ON tbl_test.id = parentId','tbl_test.name="Name 1"','id DESC'); // Table name, Column Names, JOIN, WHERE conditions, ORDER BY conditions
$res = $db->getResult();
print_r($res);
```

**Update Example**

Use the following code to update rows in the database using this class

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$db->update('tbl_test',array('name'=>"Name 4",'email'=>"name4@email.com"),'id="1" AND name="Name 1"'); // Table name, column names and values, WHERE conditions
$res = $db->getResult();
print_r($res);
```

**Insert Example**

Use the following code to insert rows into the database using this class

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$data = $db->escapeString("name5@email.com"); // Escape any input before insert
$db->insert('tbl_test',array('name'=>'Name 5','email'=>$data));  // Table name, column names and respective values
$res = $db->getResult();  
print_r($res);
```

**Delete Example**

Use the following code to delete rows from the database with this class

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$db->delete('tbl_test','id=5');  // Table name, WHERE conditions
$res = $db->getResult();  
print_r($res);
```

**Full SQL Example**

Use the following code to enter the full SQL query

```php
<?php
require_once("MySQL.class.php");
$db = new Database();
$db->connect();
$db->query('SELECT id,name FROM tbl_test');
$res = $db->getResult();
foreach($res as $output){
	echo $output["name"]."<br />";
}
```

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/meownosaurus/mysql-crud-oop-class-php/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

