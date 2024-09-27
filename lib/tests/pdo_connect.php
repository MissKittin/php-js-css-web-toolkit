<?php
	/*
	 * pdo_connect.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite) (default: sqlite)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_SOCKET (has priority over the HOST)
	 *    eg. /var/run/postgresql
	 *    note: path to the directory, not socket
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_SOCKET (has priority over the HOST)
	 *    eg. /var/run/mysqld/mysqld.sock
	 *   TEST_MYSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  PDO extension is required
	 *  var_export_contains.php library is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 */

	function _test_driver($_db_driver, &$errors, $skip_seeded_file_check)
	{
		echo ' -> Testing pdo_connect with '.$_db_driver.PHP_EOL;
			$pdo_handler=pdo_connect(__DIR__.'/tmp/pdo_connect/db_'.$_db_driver);

		echo '  -> returns PDO instance';
			if($pdo_handler instanceof PDO)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$_db_driver.' instanceof PDO failed';
			}

		echo '  -> database seeded';
			if($skip_seeded_file_check)
				echo ' [SKIP]';
			else
			{
				if(file_exists(__DIR__.'/tmp/pdo_connect/db_'.$_db_driver.'/database_seeded'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]=$_db_driver.' database_seeded file not exists';
				}
			}
			$query=$pdo_handler->query('SELECT * FROM pdo_connect_test_table');
			if($query === false)
			{
				echo ' [FAIL]';
				$errors[]=$_db_driver.' PDO query() failed';
			}
			else
			{
				echo ' [ OK ]';

				$result_a="array(0=>array('id'=>'1','a'=>'aa','b'=>'ab',),1=>array('id'=>'2','a'=>'ba','b'=>'bb',),)";
				$result_b="array(0=>array('id'=>1,'a'=>'aa','b'=>'ab',),1=>array('id'=>2,'a'=>'ba','b'=>'bb',),)"; // id can be (int)1 or (string)"1"
				$query_result=$query->fetchAll(PDO::FETCH_NAMED);

				//echo ' ('.var_export_contains($query_result, '', true).')';
				if(
					var_export_contains($query_result, $result_a) ||
					var_export_contains($query_result, $result_b)
				)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$_db_driver.' PDO fetchAll() failed';
				}
			}
	}

	if(!class_exists('PDO'))
	{
		echo 'PDO extension is not loaded'.PHP_EOL;
		exit(1);
	}

	$_db_driver=getenv('TEST_DB_TYPE');
	if($_db_driver === false)
		$_db_driver='sqlite';
	if(!in_array($_db_driver, ['sqlite', 'pgsql', 'mysql']))
	{
		echo $_db_driver.' driver is not supported'.PHP_EOL;
		exit(1);
	}

	if(!in_array($_db_driver, PDO::getAvailableDrivers()))
	{
		echo 'pdo_'.$_db_driver.' extension is not loaded'.PHP_EOL;
		exit(1);
	}

	foreach(['rmdir_recursive.php', 'var_export_contains.php'] as $library)
	{
		echo ' -> Including '.$library;
			if(is_file(__DIR__.'/../lib/'.$library))
			{
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.$library))
			{
				if(@(include __DIR__.'/../'.$library) === false)
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
	}

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

	$errors=[];

	echo ' -> Setting up database credentials'.PHP_EOL;
		$_db_credentials=[
			'pgsql'=>[
				'host'=>'127.0.0.1',
				'port'=>'5432',
				'dbname'=>'php_toolkit_tests',
				'user'=>'postgres',
				'password'=>'postgres'
			],
			'mysql'=>[
				'host'=>'[::1]',
				'port'=>'3306',
				'dbname'=>'php_toolkit_tests',
				'user'=>'root',
				'password'=>''
			]
		];
		foreach(['pgsql', 'mysql'] as $database)
			foreach(['host', 'port', 'socket', 'dbname', 'user', 'password'] as $parameter)
			{
				$variable='TEST_'.strtoupper($database.'_'.$parameter);
				$value=getenv($variable);

				if($value !== false)
				{
					echo '  -> Using '.$variable.'="'.$value.'" as '.$database.' '.$parameter.PHP_EOL;
					$_db_credentials[$database][$parameter]=$value;
				}
			}

	echo ' -> Removing temporary files';
		rmdir_recursive(__DIR__.'/tmp/pdo_connect');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating database definitions';
		@mkdir(__DIR__.'/tmp');
		mkdir(__DIR__.'/tmp/pdo_connect');
		mkdir(__DIR__.'/tmp/pdo_connect/db_sqlite');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite/config.php', '<?php
			return [
				"db_type"=>"sqlite",
				"host"=>$db."/database.sqlite3"
			];
		?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite/seed.php', '<?php
			$pdo_handler->exec(\'
				CREATE TABLE pdo_connect_test_table(
					id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
					a TEXT,
					b TEXT
				)
			\');
			$pdo_handler->exec(\'
				INSERT INTO pdo_connect_test_table(a, b) VALUES
					("aa", "ab"),
					("ba", "bb")
			\');
		?>');
		mkdir(__DIR__.'/tmp/pdo_connect/db_sqlite_memory');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite_memory/config.php', '<?php
			return [
				"db_type"=>"sqlite",
				"host"=>":memory:"
			];
		?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite_memory/seed.php', '<?php
			$pdo_handler->exec(\'
				CREATE TABLE pdo_connect_test_table(
					id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
					a TEXT,
					b TEXT
				)
			\');
			$pdo_handler->exec(\'
				INSERT INTO pdo_connect_test_table(a, b) VALUES
					("aa", "ab"),
					("ba", "bb")
			\');
		?>');
		mkdir(__DIR__.'/tmp/pdo_connect/db_sqlite_seeded_path');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite_seeded_path/config.php', '<?php
			return [
				"db_type"=>"sqlite",
				"host"=>$db."/database.sqlite3",
				"seeded_path"=>$db
			];
		?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite_seeded_path/seed.php', '<?php
			$pdo_handler->exec(\'
				CREATE TABLE pdo_connect_test_table(
					id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
					a TEXT,
					b TEXT
				)
			\');
			$pdo_handler->exec(\'
				INSERT INTO pdo_connect_test_table(a, b) VALUES
					("aa", "ab"),
					("ba", "bb")
			\');
		?>');
		mkdir(__DIR__.'/tmp/pdo_connect/db_pgsql');
		if(isset($_db_credentials['pgsql']['socket']))
			file_put_contents(__DIR__.'/tmp/pdo_connect/db_pgsql/config.php', '<?php
				return [
					"db_type"=>"pgsql",
					"socket"=>"'.$_db_credentials['pgsql']['socket'].'",
					"db_name"=>"'.$_db_credentials['pgsql']['dbname'].'",
					"charset"=>"UTF8",
					"user"=>"'.$_db_credentials['pgsql']['user'].'",
					"password"=>"'.$_db_credentials['pgsql']['password'].'"
				];
			?>');
		else
			file_put_contents(__DIR__.'/tmp/pdo_connect/db_pgsql/config.php', '<?php
				return [
					"db_type"=>"pgsql",
					"host"=>"'.$_db_credentials['pgsql']['host'].'",
					"port"=>"'.$_db_credentials['pgsql']['port'].'",
					"db_name"=>"'.$_db_credentials['pgsql']['dbname'].'",
					"charset"=>"UTF8",
					"user"=>"'.$_db_credentials['pgsql']['user'].'",
					"password"=>"'.$_db_credentials['pgsql']['password'].'"
				];
			?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_pgsql/seed.php', '<?php
			$pdo_handler->exec(\'DROP TABLE IF EXISTS pdo_connect_test_table\');
			$pdo_handler->exec(\'
				CREATE TABLE pdo_connect_test_table(
					id SERIAL PRIMARY KEY,
					a TEXT,
					b TEXT
				)
			\');
			$pdo_handler->exec("
				INSERT INTO pdo_connect_test_table(a, b) VALUES
					(\'aa\', \'ab\'),
					(\'ba\', \'bb\')
			");
		?>');
		mkdir(__DIR__.'/tmp/pdo_connect/db_mysql');
		if(isset($_db_credentials['mysql']['socket']))
			file_put_contents(__DIR__.'/tmp/pdo_connect/db_mysql/config.php', '<?php
				return [
					"db_type"=>"mysql",
					"socket"=>"'.$_db_credentials['mysql']['socket'].'",
					"db_name"=>"'.$_db_credentials['mysql']['dbname'].'",
					"charset"=>"utf8mb4",
					"user"=>"'.$_db_credentials['mysql']['user'].'",
					"password"=>"'.$_db_credentials['mysql']['password'].'"
				];
			?>');
		else
			file_put_contents(__DIR__.'/tmp/pdo_connect/db_mysql/config.php', '<?php
				return [
					"db_type"=>"mysql",
					"host"=>"'.$_db_credentials['mysql']['host'].'",
					"port"=>"'.$_db_credentials['mysql']['port'].'",
					"db_name"=>"'.$_db_credentials['mysql']['dbname'].'",
					"charset"=>"utf8mb4",
					"user"=>"'.$_db_credentials['mysql']['user'].'",
					"password"=>"'.$_db_credentials['mysql']['password'].'"
				];
			?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_mysql/seed.php', '<?php
			$pdo_handler->exec(\'DROP TABLE IF EXISTS pdo_connect_test_table\');
			$pdo_handler->exec(\'
				CREATE TABLE pdo_connect_test_table(
					id INTEGER NOT NULL AUTO_INCREMENT,
					a TEXT,
					b TEXT,
					PRIMARY KEY (id)
				)
			\');
			$pdo_handler->exec(\'
				INSERT INTO pdo_connect_test_table(a, b) VALUES
					("aa", "ab"),
					("ba", "bb")
			\');
		?>');
	echo ' [ OK ]'.PHP_EOL;

	try {
		if($_db_driver === 'sqlite')
		{
			_test_driver($_db_driver, $errors, true);
			_test_driver($_db_driver.'_memory', $errors, true);
			_test_driver($_db_driver.'_seeded_path', $errors, false);
		}
		else
			_test_driver($_db_driver, $errors, false);
	} catch(Throwable $error) {
		echo ' <- Testing pdo_connect with '.$_db_driver.' [FAIL]'.PHP_EOL;
		$errors[]=$_db_driver.' caught: '.$error->getMessage();
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.PHP_EOL;

		exit(1);
	}
?>