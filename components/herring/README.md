# The Herring
Doktor Sledzik i mister Zgredzik  
Device for tracking users

## Required PHP extensions
* `PDO` (for tests)
* `pdo_sqlite` (for tests)

## Required libraries
* `measure_exec_time.php`
* `rand_str.php`
* `sortTable.js`
* `has_php_close_tag.php` (for tests)
* `include_into_namespace.php` (for tests)

## Supported databases
* PostgreSQL
* MySQL
* SQLite3

## Methods
* `add()`  
	add a record to the database
* `move_to_archive(int_days)` [returns array_with_row_ids]  
	move records older than int_days to the archive
* `flush_archive()`  
	delete all records from the archive
* `dump_archive_to_csv(string_output_file=null)` [returns string_content|null]  
	if output_file === null returns the result  
	warning: when there is a lot of output data, out of memory may occur - think twice
* `generate_report(string_output_file=null)` [returns string_content|null]  
	if output_file === null returns the result  
	warning: when there is a lot of output data, out of memory may occur - think twice

## Constructor parameters
* `pdo_handler` [object]  
	required
* `table_name_prefix` [string]  
	default: `herring_`
* `timestamp` [int]  
	force timestamp
* `ip` [string]  
	force visitor IP  
	default: `$_SERVER['REMOTE_ADDR']`  
	note: throws an Exception if the parameter and default value are not defined
* `user_agent` [string]  
	force User-Agent  
	default: `$_SERVER['HTTP_USER_AGENT']`
* `cookie_name` [string]  
	set tracking cookie name  
	ot defining means disabling this functionality  
	note: set this and do not set `cookie_value` to generate string
* `cookie_value` [string]  
	force tracking cookie value  
	note: `cookie_name` must be defined
* `referer` [string]  
	force Referer value  
	default: `$_SERVER['HTTP_REFERER']`
* `uri` [string]  
	force request URI  
	default: `$_SERVER['REQUEST_URI']`  
	note: throws an Exception if the parameter and default value are not defined
* `uri_without_get` [bool]  
	remove get parameters from URI  
	default: `true`
* `maintenance_mode` [bool]  
	set to true if you want to generate reports  
	default: `false`
* `setcookie_callback` [callable]  
	define custom setcookie function  
	arguments: `$cookie_name, $cookie_value`

## How it works
Herring works on two tables: visitors and archive.  
The latest logs are loaded into the visitors table.  
The archive table contains entries older than n days, processed and ready for dump or report generation.  
Generating the report may take a while, so it is recommended to generate it outside the main application.

## Note
Throws Exception when there is a database connection error or query execution error.  
The tracking cookie is valid for 2 years, unless a setcookie callback is defined.  
You have to write the report generating tool/interface/cron job yourself

## Hint
Calling `move_to_archive(0)` will move all records to the archive.  
You can add new records directly in the application or through a queue worker.  
You can use cron for database maintenance and report generation.

## Usage
Adding a record to the database:
```
try {
	(new herring([
		'pdo_handler'=>new PDO('sqlite:./var/databases/herring.sqlite3'),
		'cookie_name'=>'herring_id'
	]))->add();
} catch(Exception $error) {
	echo 'Error: '.$error->getMessage();
	exit();
}
```

Exporting data and generating a report:
```
try {
	$herring=new herring([
		'pdo_handler'=>new PDO('sqlite:./var/databases/herring.sqlite3'),
		'maintenance_mode'=>true
	]);

	$herring->move_to_archive(0); // move all records

	$herring->generate_report('./var/log/herring_report_'.date('Y-m-d_H-i-s').'.html');
	$herring->dump_archive_to_csv('./var/log/herring_data_'.date('Y-m-d_H-i-s').'.csv'');

	$herring->flush_archive();
} catch(Exception $error) {
	echo 'Error: '.$error->getMessage().PHP_EOL;
	exit(1);
}
```

## Testing
The test can be performed in two ways:  
short (the default, with a small amount of data)  
and long (with a large amount of data).  
To run long mode, run the test with the `long` parameter.

## Portability
Create a directory `./components/herring/lib`  
and copy the required libraries to this directory.
