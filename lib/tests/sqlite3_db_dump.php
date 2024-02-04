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
		@unlink(__DIR__.'/tmp/sqlite3_db_dump.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test database';
		@mkdir(__DIR__.'/tmp');

		if(extension_loaded('PDO') && extension_loaded('pdo_sqlite'))
		{
			echo ' (PDO)';
			$test_db=new PDO('sqlite:'.__DIR__.'/tmp/sqlite3_db_dump.sqlite3');
		}
		else if(class_exists('SQLite3'))
		{
			echo ' (SQLite3)';
			$test_db=new SQLite3(__DIR__.'/tmp/sqlite3_db_dump.sqlite3');
			$test_db->busyTimeout(5000);
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

		foreach(['a', 'b', 'c'] as $table)
		{
			$test_db->exec('CREATE TABLE table'.$table.'(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, columna TEXT, columnb TEXT)');
			foreach(['a', 'b', 'c', 'd', 'e', 'f'] as $row)
				$test_db->exec('INSERT INTO table'.$table.'(columna, columnb) VALUES("cella'.$row.'", "cellb'.$row.'")');
		}
		unset($test_db);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing sqlite3_db_dump';
		if(class_exists('SQLite3'))
		{
			//echo ' ('.md5(sqlite3_db_dump(__DIR__.'/tmp/sqlite3_db_dump.sqlite3')).')';
			if(md5(sqlite3_db_dump(__DIR__.'/tmp/sqlite3_db_dump.sqlite3')) === '60071847ffd1fa2efce1fc9a606b15fe')
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
		if(extension_loaded('PDO') && extension_loaded('pdo_sqlite'))
		{
			//echo ' ('.md5(sqlite3_pdo_dump(__DIR__.'/tmp/sqlite3_db_dump.sqlite3')).')';
			if(md5(sqlite3_pdo_dump(__DIR__.'/tmp/sqlite3_db_dump.sqlite3')) === '60071847ffd1fa2efce1fc9a606b15fe')
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