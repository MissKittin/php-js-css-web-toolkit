<?php
	/*
	 * pdo_migrate.php library test
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
	 *  PDO extension is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  var_export_contains.php library is required
	 */

	function create_test_migrations($simulate_failure_a, $simulate_failure_b, $simulate_failure_c, $driver)
	{
		$failure_a='';
		$failure_b='';
		$failure_c='';
		$SAMPLETEXT='"SAMPLETEXT"';

		if($simulate_failure_a)
			$failure_a='return false;';

		if($simulate_failure_b)
			$failure_b='return false;';

		if($simulate_failure_c)
			$failure_c='return false;';

		switch($driver)
		{
			case 'pgsql':
				$id='SERIAL PRIMARY KEY,';
				$SAMPLETEXT="\'SAMPLETEXT\'";
			break;
			case 'mysql':
				$id='INTEGER NOT NULL, PRIMARY KEY(id),';
			break;
			case 'sqlite':
				$id='INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,';
		}

		file_put_contents(__DIR__.'/tmp/pdo_migrate/migrations/0000-00-00_00-00-00_create-table.php', '<?php '
		.	'return function($pdo_handle, $mode)'
		.	'{'
		.		'try {'
		.			'switch($mode)'
		.			'{'
		.				"case 'apply':"
		//.					'echo " {apply 1}";'
		.					'$result=$pdo_handle->exec('."'"
		.						'CREATE TABLE pdo_migrate_test_table'
		.						'('
		.							'id '.$id
		.							'samplecolumn TEXT'
		.						')'
		.					"'".');'
		.				'break;'
		.				"case 'rollback':"
		//.					'echo " {rollback 1}";'
		.					$failure_c // simulate a failure
		.					'$result=$pdo_handle->exec('."'"
		.						'DROP TABLE IF EXISTS pdo_migrate_test_table'
		.					"'".');'
		.			'}'
		.			$failure_a // simulate a failure
		.			'if($result === false)'
		.				'return false;'
		.		'} catch(PDOException $error) {'
		.			'return false;'
		.		'}'
		.		'return true;'
		.	'};'
		);
		file_put_contents(__DIR__.'/tmp/pdo_migrate/migrations/0000-00-00_00-00-01_seed-table.php', '<?php '
		.	'return function($pdo_handle, $mode)'
		.	'{'
		.		'try {'
		.			'switch($mode)'
		.			'{'
		.				"case 'apply':"
		//.					'echo " {apply 2}";'
		.					'$result=$pdo_handle->exec('."'"
		.						'INSERT INTO pdo_migrate_test_table'
		.						'('
		.							'id,'
		.							'samplecolumn'
		.						') VALUES ('
		.							'1,'
		.							$SAMPLETEXT
		.						')'
		.					"'".');'
		.				'break;'
		.				"case 'rollback':"
		//.					'echo " {rollback 2}";'
		.					'$result=$pdo_handle->exec('."'"
		.						'DELETE FROM pdo_migrate_test_table'
		.					"'".');'
		.			'}'
		.			$failure_b // simulate a failure
		.			'if($result === false)'
		.				'return false;'
		.		'} catch(PDOException $error) {'
		.			'return false;'
		.		'}'
		.		'return true;'
		.	'};'
		);
	}

	if(!class_exists('PDO'))
	{
		echo 'PDO extension is not loaded'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including var_export_contains.php';
		if(is_file(__DIR__.'/../lib/var_export_contains.php'))
		{
			if(@(include __DIR__.'/../lib/var_export_contains.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../var_export_contains.php'))
		{
			if(@(include __DIR__.'/../var_export_contains.php') === false)
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
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/pdo_migrate');
		@mkdir(__DIR__.'/tmp/pdo_migrate/migrations');
		@unlink(__DIR__.'/tmp/pdo_migrate/pdo_migrate.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	if(getenv('TEST_DB_TYPE') !== false)
	{
		if(!class_exists('PDO'))
		{
			echo 'PDO extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Configuring PDO'.PHP_EOL;

		$_pdo=[
			'type'=>getenv('TEST_DB_TYPE'),
			'credentials'=>[
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
			]
		];

		foreach(['pgsql', 'mysql'] as $_pdo['_database'])
			foreach(['host', 'port', 'socket', 'dbname', 'user', 'password'] as $_pdo['_parameter'])
			{
				$_pdo['_variable']='TEST_'.strtoupper($_pdo['_database'].'_'.$_pdo['_parameter']);
				$_pdo['_value']=getenv($_pdo['_variable']);

				if($_pdo['_value'] !== false)
				{
					echo '  -> Using '.$_pdo['_variable'].'="'.$_pdo['_value'].'" as '.$_pdo['_database'].' '.$_pdo['_parameter'].PHP_EOL;
					$_pdo['credentials'][$_pdo['_database']][$_pdo['_parameter']]=$_pdo['_value'];
				}
			}

		try /* some monsters */ {
			switch($_pdo['type'])
			{
				case 'pgsql':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

					if(!in_array('pgsql', PDO::getAvailableDrivers()))
						throw new Exception('pdo_pgsql extension is not loaded');

					if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
						$pdo_handle=new PDO('pgsql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
							.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
							.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
						);
					else
						$pdo_handle=new PDO('pgsql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
							.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
							.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
							.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
						);
				break;
				case 'mysql':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

					if(!in_array('mysql', PDO::getAvailableDrivers()))
						throw new Exception('pdo_mysql extension is not loaded');

					if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
						$pdo_handle=new PDO('mysql:'
							.'unix_socket='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
							$_pdo['credentials'][$_pdo['type']]['user'],
							$_pdo['credentials'][$_pdo['type']]['password']
						);
					else
						$pdo_handle=new PDO('mysql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
							.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
							$_pdo['credentials'][$_pdo['type']]['user'],
							$_pdo['credentials'][$_pdo['type']]['password']
						);
				break;
				case 'sqlite':
					if(!in_array('sqlite', PDO::getAvailableDrivers()))
						throw new Exception('pdo_sqlite extension is not loaded');

					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;
				break;
				default:
					throw new Exception($_pdo['type'].' driver is not supported');
			}
		} catch(Throwable $error) {
			echo ' Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(isset($pdo_handle))
		{
			$pdo_handle->exec('DROP TABLE IF EXISTS pdo_migrate_test_migrations');
			$pdo_handle->exec('DROP TABLE IF EXISTS pdo_migrate_test_table');
		}
	}
	if(!isset($pdo_handle))
	{
		if(!in_array('sqlite', PDO::getAvailableDrivers()))
		{
			echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
			exit(1);
		}

		$pdo_handle=new PDO('sqlite:'.__DIR__.'/tmp/pdo_migrate/pdo_migrate.sqlite3');
	}

	$failed=false;

	echo ' -> Testing apply migration #1'.PHP_EOL;
		echo '  -> migration failed';
			$callbacks_executed='';
			$exception_caught=false;
			create_test_migrations(true, true, false, $pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME));
			try {
				pdo_migrate([
					'pdo_handle'=>$pdo_handle,
					'table_name'=>'pdo_migrate_test_migrations',
					'directory'=>__DIR__.'/tmp/pdo_migrate/migrations',
					'mode'=>'apply',
					'on_begin'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_begin-';
					},
					'on_skip'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_skip-';
					},
					'on_error'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error-';
					},
					'on_error_rollback'=>function($migration) use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error_rollback-';
					},
					'on_end'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_end-';
					}
				]);
			} catch(Throwable $error) {
				$exception_caught=true;
			}
			if($exception_caught)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_migrations');
				if($query === false)
				{
					echo ' [FAIL] (query)';
					$failed=true;
				}
				else
				{
					$result_a="array(0=>array('id'=>'1','migration'=>'0000-00-00_00-00-00_create-table','failed'=>'1',),)";
					$result_b="array(0=>array('id'=>1,'migration'=>'0000-00-00_00-00-00_create-table','failed'=>1,),)"; // id can be (int)1 or (string)"1"

					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					//echo ' ('.var_export_contains($query_result, '', true).')';
					if(
						var_export_contains($query_result, $result_a) ||
						var_export_contains($query_result, $result_b)
					)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_table');
				if($query === false)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
			} catch(PDOException $error) {
				echo ' [ OK ] (PDOException)';
			}
			//echo ' ('.$callbacks_executed.')';
			if($callbacks_executed === 'on_begin-on_error-on_error_rollback-on_end-')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> migration successful and #2 failed';
			$callbacks_executed='';
			$exception_caught=false;
			create_test_migrations(false, true, false, $pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME));
			try {
				pdo_migrate([
					'pdo_handle'=>$pdo_handle,
					'table_name'=>'pdo_migrate_test_migrations',
					'directory'=>__DIR__.'/tmp/pdo_migrate/migrations',
					'mode'=>'apply',
					'on_begin'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_begin-';
					},
					'on_skip'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_skip-';
					},
					'on_error'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error-';
					},
					'on_error_rollback'=>function($migration) use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error_rollback-';
					},
					'on_end'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_end-';
					}
				]);
			} catch(Throwable $error) {
				$exception_caught=true;
			}
			if($exception_caught)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_migrations');
				if($query === false)
				{
					echo ' [FAIL] (query)';
					$failed=true;
				}
				else
				{
					$result_a="array(0=>array('id'=>'1','migration'=>'0000-00-00_00-00-00_create-table','failed'=>'0',),1=>array('id'=>'2','migration'=>'0000-00-00_00-00-01_seed-table','failed'=>'1',),)";
					$result_b="array(0=>array('id'=>1,'migration'=>'0000-00-00_00-00-00_create-table','failed'=>0,),1=>array('id'=>2,'migration'=>'0000-00-00_00-00-01_seed-table','failed'=>1,),)"; // id can be (int)1 or (string)"1"

					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					//echo ' ('.var_export_contains($query_result, '', true).')';
					if(
						var_export_contains($query_result, $result_a) ||
						var_export_contains($query_result, $result_b)
					)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_table');
				if($query === false)
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
				{
					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					if(empty($query_result))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			//echo ' ('.$callbacks_executed.')';
			if($callbacks_executed === 'on_begin-on_end-on_begin-on_error-on_error_rollback-on_end-')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing apply migration #2'.PHP_EOL;
		echo '  -> migration successful';
			$callbacks_executed='';
			create_test_migrations(false, false, false, $pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME));
			try {
				pdo_migrate([
					'pdo_handle'=>$pdo_handle,
					'table_name'=>'pdo_migrate_test_migrations',
					'directory'=>__DIR__.'/tmp/pdo_migrate/migrations',
					'mode'=>'apply',
					'on_begin'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_begin-';
					},
					'on_skip'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_skip-';
					},
					'on_error'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error-';
					},
					'on_error_rollback'=>function($migration) use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error_rollback-';
					},
					'on_end'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_end-';
					}
				]);
			} catch(Throwable $error) {
				echo ' [FAIL]';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_migrations');
				if($query === false)
				{
					echo ' [FAIL] (query)';
					$failed=true;
				}
				else
				{
					$result_a="array(0=>array('id'=>'1','migration'=>'0000-00-00_00-00-00_create-table','failed'=>'0',),1=>array('id'=>'2','migration'=>'0000-00-00_00-00-01_seed-table','failed'=>'0',),)";
					$result_b="array(0=>array('id'=>1,'migration'=>'0000-00-00_00-00-00_create-table','failed'=>0,),1=>array('id'=>2,'migration'=>'0000-00-00_00-00-01_seed-table','failed'=>0,),)"; // id can be (int)1 or (string)"1"

					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					//echo ' ('.var_export_contains($query_result, '', true).')';
					if(
						var_export_contains($query_result, $result_a) ||
						var_export_contains($query_result, $result_b)
					)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_table');
				if($query === false)
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
				{
					$result_a="array(0=>array('id'=>'1','samplecolumn'=>'SAMPLETEXT',),)";
					$result_b="array(0=>array('id'=>1,'samplecolumn'=>'SAMPLETEXT',),)"; // id can be (int)1 or (string)"1"

					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					//echo ' ('.var_export_contains($query_result, '', true).')';
					if(
						var_export_contains($query_result, $result_a) ||
						var_export_contains($query_result, $result_b)
					)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			//echo ' ('.$callbacks_executed.')';
			if($callbacks_executed === 'on_begin-on_skip-on_begin-on_end-')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing rollback migrations'.PHP_EOL;
		echo '  -> migration #2 success and #1 failed';
			$callbacks_executed='';
			$exception_caught=false;
			create_test_migrations(false, false, true, $pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME));
			try {
				pdo_migrate([
					'pdo_handle'=>$pdo_handle,
					'table_name'=>'pdo_migrate_test_migrations',
					'directory'=>__DIR__.'/tmp/pdo_migrate/migrations',
					'mode'=>'rollback',
					'on_begin'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_begin-';
					},
					'on_skip'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_skip-';
					},
					'on_error'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error-';
					},
					'on_error_rollback'=>function($migration) use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error_rollback-';
					},
					'on_end'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_end-';
					}
				]);
			} catch(Throwable $error) {
				$exception_caught=true;
			}
			if($exception_caught)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_migrations');
				if($query === false)
				{
					echo ' [FAIL] (query)';
					$failed=true;
				}
				else
				{
					$result_a="array(0=>array('id'=>'1','migration'=>'0000-00-00_00-00-00_create-table','failed'=>'1',),)";
					$result_b="array(0=>array('id'=>1,'migration'=>'0000-00-00_00-00-00_create-table','failed'=>1,),)"; // id can be (int)1 or (string)"1"

					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					//echo ' ('.var_export_contains($query_result, '', true).')';
					if(
						var_export_contains($query_result, $result_a) ||
						var_export_contains($query_result, $result_b)
					)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_table');
				if($query === false)
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
				{
					$query_result=$query->fetchAll(PDO::FETCH_NAMED);

					if(empty($query_result))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			//echo ' ('.$callbacks_executed.')';
			if($callbacks_executed === 'on_begin-on_end-on_begin-on_error-on_end-')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> migration #1 success';
			$callbacks_executed='';
			create_test_migrations(false, false, false, $pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME));
			try {
				pdo_migrate([
					'pdo_handle'=>$pdo_handle,
					'table_name'=>'pdo_migrate_test_migrations',
					'directory'=>__DIR__.'/tmp/pdo_migrate/migrations',
					'mode'=>'rollback',
					'on_begin'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_begin-';
					},
					'on_skip'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_skip-';
					},
					'on_error'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error-';
					},
					'on_error_rollback'=>function($migration) use(&$callbacks_executed)
					{
						$callbacks_executed.='on_error_rollback-';
					},
					'on_end'=>function() use(&$callbacks_executed)
					{
						$callbacks_executed.='on_end-';
					}
				]);
			} catch(Throwable $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_migrations');
				if($query === false)
				{
					echo ' [FAIL] (query)';
					$failed=true;
				}
				else
				{
					$query_result=$query->fetchAll(PDO::FETCH_NAMED);
					//echo ' ('.var_export_contains($query_result, '', true).')';
					if(var_export_contains($query_result, 'array()'))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				}
			} catch(PDOException $error) {
				echo ' [FAIL] (PDOException)';
				$failed=true;
			}
			try {
				$query=$pdo_handle->query('SELECT * FROM pdo_migrate_test_table');
				if($query === false)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
			} catch(PDOException $error) {
				echo ' [ OK ] (PDOException)';
			}
			//echo ' ('.$callbacks_executed.')';
			if($callbacks_executed === 'on_begin-on_skip-on_begin-on_end-')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>