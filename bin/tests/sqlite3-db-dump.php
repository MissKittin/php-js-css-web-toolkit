<?php
	/*
	 * sqlite3-db-dump.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 */

	echo ' -> Removing temporary files';
		@unlink(__DIR__.'/tmp/sqlite3-db-dump/sqlite3-db-dump.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test database';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/sqlite3-db-dump');

		$test_db=[];

		if(class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers()))
		{
			echo ' (PDO)';
			$test_db[0]=new PDO('sqlite:'.__DIR__.'/tmp/sqlite3-db-dump/sqlite3-db-dump.sqlite3');
		}
		else if(class_exists('SQLite3'))
		{
			echo ' (SQLite3)';
			$test_db[1]=new SQLite3(__DIR__.'/tmp/sqlite3-db-dump/sqlite3-db-dump.sqlite3');
			$test_db[1]->busyTimeout(5000);
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

		foreach($test_db as $database)
			foreach(['a', 'b', 'c'] as $table)
			{
				$database->exec('CREATE TABLE table'.$table.'(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, columna TEXT, columnb TEXT)');

				foreach(['a', 'b', 'c', 'd', 'e', 'f'] as $row)
					$database->exec('INSERT INTO table'.$table.'(columna, columnb) VALUES("cella'.$row.'", "cellb'.$row.'")');
			}

		unset($test_db);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing tool';
		if(
			md5(shell_exec('"'.PHP_BINARY.'" "'.__DIR__.'/../sqlite3-db-dump.php" '
			.	'"'.__DIR__.'/tmp/sqlite3-db-dump/sqlite3-db-dump.sqlite3"'
			))
			===
			'60071847ffd1fa2efce1fc9a606b15fe'
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>