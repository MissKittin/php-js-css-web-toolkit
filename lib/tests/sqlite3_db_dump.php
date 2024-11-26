<?php
	/*
	 * sqlite3_db_dump.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 * or
	 *  SQLite3 class is required
	 */

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@unlink(__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test database';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/sqlite3_db_dump');

		$test_db=[];

		if(class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers()))
		{
			echo ' (PDO)';
			$test_db[0]=new PDO('sqlite:'.__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3');
		}
		else if(class_exists('SQLite3'))
		{
			echo ' (SQLite3)';
			$test_db[1]=new SQLite3(__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3');
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

		$database->exec('CREATE VIEW testview AS SELECT columna FROM tablea');
		$database->exec('CREATE TRIGGER testtrigger AFTER INSERT ON tablea BEGIN INSERT INTO tablea(columna, columnb) VALUES("testtrigger", "testtrigger"); END');

		unset($test_db);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing sqlite3_db_dump';
		if(class_exists('SQLite3'))
		{
			//echo PHP_EOL.PHP_EOL.sqlite3_db_dump(new SQLite3(__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3')).PHP_EOL.PHP_EOL;
			//echo ' ('.md5(sqlite3_db_dump(new SQLite3(__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3'))).')';
			if(
				md5(sqlite3_db_dump(new SQLite3(__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3')))
				===
				'9d470faff77f16abd46cc4927fc28a41'
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing sqlite3_pdo_dump';
		if(class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers()))
		{
			//echo PHP_EOL.PHP_EOL.sqlite3_db_dump(new SQLite3(__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3')).PHP_EOL.PHP_EOL;
			//echo ' ('.md5(sqlite3_pdo_dump(new PDO('sqlite:'.__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3'))).')';
			if(
				md5(sqlite3_pdo_dump(new PDO('sqlite:'.__DIR__.'/tmp/sqlite3_db_dump/sqlite3_db_dump.sqlite3')))
				===
				'9d470faff77f16abd46cc4927fc28a41'
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	if($failed)
		exit(1);
?>