<?php
	/*
	 * Quickly dump SQLite3 database
	 *
	 * Warning:
	 *  sqlite3_db_dump.php library is required
	 * Warning:
	 *   sqlite3 extension is required
	 *  or
	 *   PDO extension is required
	 *   pdo_sqlite extension is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
		{
			if(file_exists(__DIR__.'/lib/'.$library))
			{
				require __DIR__.'/lib/'.$library;
				continue;
			}

			if(file_exists(__DIR__.'/../lib/'.$library))
			{
				require __DIR__.'/../lib/'.$library;
				continue;
			}

			if($required)
				throw new Exception($library.' library not found');
		}
	}

	try {
		load_library(['sqlite3_db_dump.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if($argc < 2)
	{
		echo 'Usage: '.$argv[0].' path/to/database.file [path/to/output-file.sql]'.PHP_EOL;
		echo ' if path/to/output-file.sql is not specified, prints to stdout'.PHP_EOL;
		exit(1);
	}

	if(!file_exists($argv[1]))
	{
		echo $argv[1].' does not exists'.PHP_EOL;
		exit(1);
	}

	try {
		if(class_exists('SQLite3'))
			$output=sqlite3_db_dump(
				new SQLite3($argv[1])
			);
		else if(
			class_exists('PDO') &&
			in_array('sqlite', PDO::getAvailableDrivers())
		)
			$output=sqlite3_pdo_dump(
				new PDO('sqlite:'.$argv[1])
			);
		else
			throw new Exception('PDO and pdo_sqlite nor sqlite3 extensions are not loaded');
	} catch(Throwable $error) {
		$function_used='sqlite3_pdo_dump';

		if(class_exists('SQLite3'))
			$function_used='sqlite3_db_dump';

		echo 'Error ('.$function_used.'): '.$error->getMessage().PHP_EOL;

		exit(1);
	}

	if($output === '')
	{
		echo 'Database dump is empty'."\n";
		exit();
	}

	if(!isset($argv[2]))
	{
		echo $output;
		exit();
	}

	if(file_put_contents($argv[2], $output) === false)
	{
		echo 'Unable to save output file'.PHP_EOL;
		exit(1);
	}
?>