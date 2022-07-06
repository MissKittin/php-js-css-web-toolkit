<?php
	/*
	 * pdo_connect.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_DBNAME (default: test_database)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_DBNAME (default: test-database)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  PDO extension is required
	 *  pdo_sqlite extension is recommended
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 */

	if(!extension_loaded('PDO'))
	{
		echo 'PDO extension is not loaded'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including rmdir_recursive.php';
		if(@(include __DIR__.'/../lib/rmdir_recursive.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$errors=[];

	echo ' -> Setting up database credentials'.PHP_EOL;
		$db_credentials=[
			'pgsql'=>[
				'host'=>'127.0.0.1',
				'port'=>'5432',
				'dbname'=>'test_database',
				'user'=>'postgres',
				'password'=>'postgres'
			],
			'mysql'=>[
				'host'=>'[::1]',
				'port'=>'3306',
				'dbname'=>'test-database',
				'user'=>'root',
				'password'=>''
			]
		];
		foreach(['pgsql', 'mysql'] as $database)
			foreach(['host', 'port', 'dbname', 'user', 'password'] as $parameter)
			{
				$variable='TEST_'.strtoupper($database.'_'.$parameter);
				$value=getenv($variable);

				if($value !== false)
				{
					echo '  -> Using '.$variable.'="'.$value.'" as '.$database.' '.$parameter.PHP_EOL;
					$db_credentials[$database][$parameter]=$value;
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
			$db_type="sqlite";
			$db_host=$db."/database.sqlite3";
		?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_sqlite/seed.php', '<?php
			$pdo_handler->exec(\'
				CREATE TABLE test_table(
					id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
					a TEXT,
					b TEXT
				)
			\');
			$pdo_handler->exec(\'
				INSERT INTO test_table(a, b) VALUES
					("aa", "ab"),
					("ba", "bb")
			\');
		?>');
		mkdir(__DIR__.'/tmp/pdo_connect/db_pgsql');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_pgsql/config.php', '<?php
			$db_type="pgsql";
			$db_host="'.$db_credentials['pgsql']['host'].'";
			$db_port="'.$db_credentials['pgsql']['port'].'";
			$db_name="'.$db_credentials['pgsql']['dbname'].'";
			$db_charset="UTF8";
			$db_user="'.$db_credentials['pgsql']['user'].'";
			$db_password="'.$db_credentials['pgsql']['password'].'";
		?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_pgsql/seed.php', '<?php
			$pdo_handler->exec(\'DROP TABLE test_table\');
			$pdo_handler->exec(\'
				CREATE TABLE test_table(
					id SERIAL PRIMARY KEY,
					a TEXT,
					b TEXT
				)
			\');
			$pdo_handler->exec("
				INSERT INTO test_table(a, b) VALUES
					(\'aa\', \'ab\'),
					(\'ba\', \'bb\')
			");
		?>');
		mkdir(__DIR__.'/tmp/pdo_connect/db_mysql');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_mysql/config.php', '<?php
			$db_type="mysql";
			$db_host="'.$db_credentials['mysql']['host'].'";
			$db_port="'.$db_credentials['mysql']['port'].'";
			$db_name="'.$db_credentials['mysql']['dbname'].'";
			$db_charset="utf8mb4";
			$db_user="'.$db_credentials['mysql']['user'].'";
			$db_password="'.$db_credentials['mysql']['password'].'";
		?>');
		file_put_contents(__DIR__.'/tmp/pdo_connect/db_mysql/seed.php', '<?php
			$pdo_handler->exec(\'DROP TABLE test_table\');
			$pdo_handler->exec(\'
				CREATE TABLE test_table(
					id INT NOT NULL AUTO_INCREMENT,
					a TEXT,
					b TEXT,
					PRIMARY KEY (id)
				)
			\');
			$pdo_handler->exec(\'
				INSERT INTO test_table(a, b) VALUES
					("aa", "ab"),
					("ba", "bb")
			\');
		?>');
	echo ' [ OK ]'.PHP_EOL;

	foreach(['sqlite', 'pgsql', 'mysql'] as $database)
		if(extension_loaded('pdo_'.$database))
		{
			try {
				echo ' -> Testing pdo_connect with '.$database.PHP_EOL;
					$pdo_handler=pdo_connect(__DIR__.'/tmp/pdo_connect/db_'.$database);

				echo '  -> returns PDO instance';
					if($pdo_handler instanceof PDO)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]=$database.' instanceof PDO failed';
						continue;
					}

				echo '  -> database seeded';
					if(file_exists(__DIR__.'/tmp/pdo_connect/db_'.$database.'/database_seeded'))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]=$database.' database_seeded file not exists';
					}
					$query=$pdo_handler->query('SELECT * FROM test_table');
					if($query === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]=$database.' PDO query() failed';
					}
					else
					{
						echo ' [ OK ]';

						$result="array(0=>array('id'=>'1','a'=>'aa','b'=>'ab',),1=>array('id'=>'2','a'=>'ba','b'=>'bb',),)";
						if($database === 'pgsql')
							$result="array(0=>array('id'=>1,'a'=>'aa','b'=>'ab',),1=>array('id'=>2,'a'=>'ba','b'=>'bb',),)";

						if(str_replace(["\n", ' '], '', var_export($query->fetchAll(PDO::FETCH_NAMED), true)) === $result)
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$errors[]=$database.' PDO fetchAll() failed';
						}
					}
			} catch(Throwable $error) {
				echo ' <- Testing pdo_connect with '.$database.' [FAIL]'.PHP_EOL;
				$errors[]=$database.' caught: '.$error->getMessage();
			}
		}
		else
			echo ' -> Testing pdo_connect with '.$database.' [SKIP]'.PHP_EOL;

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.PHP_EOL;

		exit(1);
	}
?>