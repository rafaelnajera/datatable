# DataTable

[![Latest Stable Version](https://poser.pugx.org/rafaelnajera/datatable/v/stable)](https://packagist.org/packages/rafaelnajera/datatable)
[![License](https://poser.pugx.org/rafaelnajera/datatable/license)](https://packagist.org/packages/rafaelnajera/datatable)

An abstraction of an SQL-like table made out of rows with an unique integer id as 
its key. The package provides in-memory and MySQL implementation.


## Installation 

Install the latest version with

```bash
$ composer require rafaelnajera/datatable
```

## Usage

### DataTable 
The main interface is the `DataTable` class, which captures the basic functionality
of an SQL table with unique ids. Implementations take care of creating new
unique ids and of interfacing with the underlying storage mechanism.

```
$dt = new DataTableDescendantClass(...) 
```

#### Create Rows
```
$newRow = [ 'field1' => 'string value', 
            'field2' => 'string value', 
             'field3' => numberValue ];  
$newId  = $dt->createRow($newRow);  // $newId is unique but not necessarily sequential

```

You can use any number of fields and types as longs as this makes
sense with the implementation. An SQL implementation, for instance, 
may require that the fields agree in number and type with the underlying
SQL table. 

#### Error Handling 

Most methods return false if there was an error. In that case, implementations
are required to also provide an integer error code and an error message. 

```
if ($newId === false) {   // there was an error
    $errorCode = $dt->getErrorCode(); // integer code, see constants in class definition
    $errorMessage = $dt->getErrorMessage(); // string message
    return;
}
```

#### Get, seach, update and delete
``` 
// Get row(s)
$row = $dt->getRow($newId);   // $row should be equal to $newRow with an added id field

$rows = $dt->getAllRows();  // returns an array of rows (not necessary ordered by id)

// Search
$foundRows = $dt->findRows(['fieldtoSearch' => 'valueToMatch', 
        'anotherFieldToSearch' => 'anotherValuetoMatch']);  // returns an array of rows

$rowExists = $dt->findRow(['fieldtoSearch' => 'valueToMatch', 
        'anotherFieldToSearch' => 'anotherValuetoMatch']);  // returns a boolean
// $dt->findRow can return false because there were no matches or because there
// was an error. Check the error code if needed.

// Update a row
$row['field1'] = 'new value';   // $row must have a valid id
$result = $dt->updateRow($row); 

// Delete a row
$result = $dt->deleteRow($row['id');
```

### InMemoryDataTable

A `DataTable` implementation using simple PHP arrays, no storage. This makes
it possible to perform tests on data tables without having to set up
a database. 


### MySqlDataTable

A `DataTable` implementation using a MySQL table. 

```
$dt = new MySqlDataTable($pdoDatabaseConnection, $mySqlTableName);
```

`MySqlDataTable` assumes that there is a table setup with at least 
an integer id column without autoincrement. `MySqlDataTable` itself takes
care of generating new incremental ids. 

The table in MySQL can have any number of extra columns of any type. As long
as calls to `createRow` and `updateRow` agree with columns names and types, everything
should work fine. You can even have default values defined in MySQL and leave
those out when calling `createRow`.

### MySqlDataTableWithRandomIds

The same as `MySqlDatable` but ids are not sequential but random

```
$dt = new MySqlDataTableWithRandomIds($pdoDatabaseConnection, $mySqlTableName);

// or

$dt = new MySqlDataTableWithRandomIds($pdoDatabaseConnection, $mySqlTableName, $minId, $maxId);

```

If for some reason it is impossible to create new random unique Ids after a few tries
(because there are no more ids available within the desired ranges), `MySqlDataTableWithRandomIds`
will try incremental ids outside of the given range. If the desired range is
correctly established, this should never happen.

### MySqlUnitemporalDataTable

A MySQL table with time-tagged rows. Every row not only has a unique id, but
also a valid_from and a valid_until time. When using the normal `DataTable` methods
`MySQLUnitemporalDataTable`n behaves exactly the same as MySqlDataTable but
it does not delete any rows, it just makes them invalid.

There is a set of time methods to access previous versions of the data:

``` 
$dt = new MySqlUnitemporalDataTable($pdoDatabaseConnection, $mySqlTableName);


$oldRow = $dt->getRowWithTime($rowId, $time);
```
`$time` can be a Unix timestamp or a string with a valid MySQL time, 
e.g. `'2018-01-01  12:00:00'`.

The underlying MySQL table must have two datetime fields: `valid_from` and 
`valid_until`

The user is responsible for setting the PDO connection with the timezone
that is going to be used in all queries using time parameters. 



