MySQL OOP Class PHP (v.1.0)
------------

This is a simple to use MySQL class that easily bolts on to any existing PHP application, streamlining your MySQL interactions.

Setup
-----

Simply include this class into your project like so:

```php
<?php
	//Simply include this file on your page
	require_once("MySQL.class.php");

	//Set up all yor paramaters for connection
	$db = new MySQL("localhost","username","password","database",$persistent=false);
  
?>
```

Usage
-----

To use this class, you'd first init the object like so (using example credentials):

`$db = new MySQL("localhost","username","password","database",$persistent=false);`

Provided you see no errors, you are now connected and can execute full MySQL queries using:

`$db->ExecuteSQL($query);`

`ExecuteSQL()` will return an array of results, or a true (if an UPDATE or DELETE).

There are other functions such as `Insert()`, `Delete()` and `Select()` which may or may not help with your queries to the database.

Example
-------

To show you how easy this class is to use, consider you have a table called *admin*, which contains the following:

```
+----+--------------+
| id | username     |
+----+--------------+
|  1 | superuser    |
|  2 | admin         |
+----+--------------+
```

To add a user, you'd simply use:

```
$newUser = array('username' => 'user');
$db->Insert($newUser, 'admin');
```

And voila:

```
+----+---------------+
| id | username      |
+----+---------------+
|  1 | superuser     |
|  2 | admin         |
|  3 | user          |
+----+---------------+
```

To get the results into a usable array, just use `$db->Select('admin')` ...for example, doing the following:

`print_r($db->Select('admin'));`

will yield:

```
Array
(
    [0] => Array
        (
            [id] => 1
            [username] => superuser
        )

    [1] => Array
        (
            [id] => 2
            [username] => admin
        )

    [2] => Array
        (
            [id] => 3
            [username] => user
        )

)
```