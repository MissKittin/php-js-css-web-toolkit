<?php
	/*
	 * sec_bruteforce.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Hint:
	 *  you can setup Redis credentials by environment variables
	 *  variables:
	 *   TEST_REDIS=yes (default: no)
	 *   TEST_REDIS_HOST (default: 127.0.0.1)
	 *   TEST_REDIS_PORT (default: 6379)
	 *   TEST_REDIS_DBINDEX (default: 0)
	 *   TEST_REDIS_USER
	 *   TEST_REDIS_PASSWORD
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite) (default: sqlite)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  redis extension is recommended
	 */

	if(!extension_loaded('PDO'))
	{
		echo 'PDO extension is not loaded'.PHP_EOL;
		exit(1);
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

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/sec_bruteforce');
		foreach([
			'sec_bruteforce.sqlite3',
			'sec_bruteforce.json',
			'sec_bruteforce.json.lock',
			'sec_bruteforce_ondemand.json',
			'sec_bruteforce_ondemand.json.lock',
			'sec_bruteforce_timeout.json',
			'sec_bruteforce_timeout.json.lock',
			'sec_bruteforce_timeout_ondemand.json',
			'sec_bruteforce_timeout_ondemand.json.lock',
			'sec_bruteforce_clean_database.json',
			'sec_bruteforce_clean_database.json.lock',
			'sec_bruteforce_timeout_clean_database.json',
			'sec_bruteforce_timeout_clean_database.json.lock',
			'sec_bruteforce_resume.json',
			'sec_bruteforce_resume.json.lock',
			'sec_bruteforce_ondemand_resume.json',
			'sec_bruteforce_ondemand_resume.json.lock',
		] as $file)
			@unlink(__DIR__.'/tmp/sec_bruteforce/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	if(getenv('TEST_DB_TYPE') !== false)
	{
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
			foreach(['host', 'port', 'dbname', 'user', 'password'] as $_pdo['_parameter'])
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

					if(!extension_loaded('pdo_pgsql'))
						throw new Exception('pdo_pgsql extension is not loaded');

					$pdo_handler=new PDO('pgsql:'
						.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
						.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
						.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
						.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
						.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
					);
				break;
				case 'mysql':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

					if(!extension_loaded('pdo_mysql'))
						throw new Exception('pdo_mysql extension is not loaded');

					$pdo_handler=new PDO('mysql:'
						.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
						.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
						.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
						$_pdo['credentials'][$_pdo['type']]['user'],
						$_pdo['credentials'][$_pdo['type']]['password']
					);
				break;
				case 'sqlite':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;
				break;
				default:
					echo '  -> '.$_pdo['type'].' driver is not supported [FAIL]'.PHP_EOL;
			}
		} catch(Throwable $error) {
			echo ' Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(isset($pdo_handler))
		{
			$pdo_handler->exec('DROP TABLE sec_bruteforce');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_clean_database');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_timeout_clean_database');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_timeout');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_mixed_temp_ban');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_mixed_perm_ban');
		}
	}
	if(!isset($pdo_handler))
	{
		if(!extension_loaded('pdo_sqlite'))
		{
			echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
			exit(1);
		}

		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce/sec_bruteforce.sqlite3');
	}

	if(getenv('TEST_REDIS') === 'yes')
	{
		if(!extension_loaded('redis'))
		{
			echo 'redis extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Configuring Redis'.PHP_EOL;

		$_redis=[
			'credentials'=>[
				'host'=>'127.0.0.1',
				'port'=>6379,
				'dbindex'=>0,
				'user'=>null,
				'password'=>null
			],
			'connection_options'=>[
				'timeout'=>0,
				'retry_interval'=>0,
				'read_timeout'=>0
			]
		];

		foreach(['host', 'port', 'dbindex', 'user', 'password'] as $_redis['_parameter'])
		{
			$_redis['_variable']='TEST_REDIS_'.strtoupper($_redis['_parameter']);
			$_redis['_value']=getenv($_redis['_variable']);

			if($_redis['_value'] !== false)
			{
				echo '  -> Using '.$_redis['_variable'].'="'.$_redis['_value'].'" as Redis '.$_redis['_parameter'].PHP_EOL;
				$_redis['credentials'][$_redis['_parameter']]=$_redis['_value'];
			}
		}

		if($_redis['credentials']['user'] !== null)
			$_redis['_credentials_auth']['user']=$_redis['credentials']['user'];
		if($_redis['credentials']['password'] !== null)
			$_redis['_credentials_auth']['pass']=$_redis['credentials']['password'];

		try {
			$redis_handler=new Redis();

			if($redis_handler->connect(
				$_redis['credentials']['host'],
				$_redis['credentials']['port'],
				$_redis['connection_options']['timeout'],
				null,
				$_redis['connection_options']['retry_interval'],
				$_redis['connection_options']['read_timeout']
			) === false){
				echo '  -> Redis connection error'.PHP_EOL;
				unset($redis_handler);
			}

			if(
				(isset($redis_handler)) &&
				(isset($_redis['_credentials_auth'])) &&
				(!$redis_handler->auth($_redis['_credentials_auth']))
			){
				echo '  -> Redis auth error'.PHP_EOL;
				unset($redis_handler);
			}

			if(
				(isset($redis_handler)) &&
				(!$redis_handler->select($_redis['credentials']['dbindex']))
			){
				echo '  -> Redis database select error'.PHP_EOL;
				unset($redis_handler);
			}
		} catch(Throwable $error) {
			echo ' Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(isset($redis_handler))
		{
			$redis_handler->del('bruteforce_redis_test__1.2.3.4');
			$redis_handler->del('bruteforce_redis_test_resume__1.2.3.4');
			$redis_handler->del('bruteforce_redis_test_timeout__1.2.3.4');
		}
	}

	function on_ban_callback()
	{
		++$GLOBALS['_on_ban_count'];
	}
	function setup_objects()
	{
		global $pdo_handler;
		global $redis_handler;

		$objects=[
			'bruteforce_pdo'=>new bruteforce_pdo([
				'pdo_handler'=>$pdo_handler,
				'table_name'=>'sec_bruteforce',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]),
			'bruteforce_json'=>new bruteforce_json([
				'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]),
			'bruteforce_json_ondemand'=>new bruteforce_json_ondemand([
				'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_ondemand.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_ondemand.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			])
		];

		if(isset($redis_handler))
			$objects['bruteforce_redis']=new bruteforce_redis([
				'redis_handler'=>$redis_handler,
				'prefix'=>'bruteforce_redis_test__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]);

		return $objects;
	}
	function setup_resume_objects()
	{
		global $pdo_handler;
		global $redis_handler;

		$objects=[
			'bruteforce_pdo'=>new bruteforce_pdo([
				'pdo_handler'=>$pdo_handler,
				'table_name'=>'sec_bruteforce',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]),
			'bruteforce_json'=>new bruteforce_json([
				'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_resume.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_resume.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]),
			'bruteforce_json_ondemand'=>new bruteforce_json_ondemand([
				'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_ondemand_resume.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_ondemand_resume.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			])
		];

		if(isset($redis_handler))
			$objects['bruteforce_redis']=new bruteforce_redis([
				'redis_handler'=>$redis_handler,
				'prefix'=>'bruteforce_redis_test_resume__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]);

		return $objects;
	}
	function setup_timeout_objects()
	{
		global $pdo_handler;
		global $redis_handler;

		$objects=[
			'bruteforce_timeout_pdo'=>new bruteforce_timeout_pdo([
				'pdo_handler'=>$pdo_handler,
				'table_name'=>'sec_bruteforce_timeout',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2,
				'on_ban'=>'on_ban_callback'
			]),
			'bruteforce_timeout_json'=>new bruteforce_timeout_json([
				'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2,
				'on_ban'=>'on_ban_callback'
			]),
			'bruteforce_timeout_json_ondemand'=>new bruteforce_timeout_json_ondemand([
				'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_ondemand.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_ondemand.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2,
				'on_ban'=>'on_ban_callback'
			])
		];

		if(isset($redis_handler))
			$objects['bruteforce_timeout_redis']=new bruteforce_timeout_redis([
				'redis_handler'=>$redis_handler,
				'prefix'=>'bruteforce_redis_test_timeout__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2,
				'on_ban'=>'on_ban_callback'
			]);

		return $objects;
	}

	$errors=[];

	foreach(setup_objects() as $class_name=>$class)
	{
		echo ' -> Testing '.$class_name.PHP_EOL;

		$GLOBALS['_on_ban_count']=0;

		echo '  -> add/check/get_attempts';
			for($i=1; $i<=3; ++$i)
			{
				$class->add();

				$check=(!$class->check());
				if($i === 3)
					$check=$class->check();

				if($check && ($class->get_attempts() === $i))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]=$class_name.' add/check/get_attempts';
				}
			}
			if($GLOBALS['_on_ban_count'] === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$class_name.' add/check/get_attempts on_ban counter checking';
			}

		echo '  -> del/check/get_attempts';
			$class->del();

			if((!$class->check()) && ($class->get_attempts() === 0))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$class_name.' del/check';
			}

		echo '  -> add/sleep 3/add/clean_database'.PHP_EOL;
				$class->add();
				sleep(3);
				$class->add();
				$class->clean_database(2);
				$class->del();
	}

	echo ' -> Testing save'.PHP_EOL;
		foreach(setup_resume_objects() as $class_name=>$class)
		{
			echo '  -> '.$class_name.PHP_EOL;

			$GLOBALS['_on_ban_count']=0;

			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' save add/check/get_attempts';
					}
				}
				if($GLOBALS['_on_ban_count'] === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' save add/check/get_attempts on_ban counter checking';
				}

				unset($class);
		}
	echo ' -> Testing resume'.PHP_EOL;
		foreach(setup_resume_objects() as $class_name=>$class)
		{
			echo '  -> '.$class_name.PHP_EOL;

			echo '   -> check/get_attempts';
				if($class->check() && ($class->get_attempts() === 3))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]=$class_name.' resume check/get_attempts';
				}
				echo PHP_EOL;
		}

	foreach(setup_timeout_objects() as $class_name=>$class)
	{
		echo ' -> Testing '.$class_name.PHP_EOL;

		echo '  -> phase 1 '.PHP_EOL;
			$GLOBALS['_on_ban_count']=0;
			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' add/check/get_attempts phase 1';
					}
				}
				if($GLOBALS['_on_ban_count'] === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' add/check/get_attempts phase 1 on_ban counter checking';
				}
			echo '   -> del/check/get_attempts';
				$class->del();

				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' del/check/get_attempts phase 1';
				}

		echo '  -> phase 2 '.PHP_EOL;
			$GLOBALS['_on_ban_count']=0;
			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' add/check/get_attempts phase 2';
					}
				}
				if($GLOBALS['_on_ban_count'] === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' add/check/get_attempts phase 2 on_ban counter checking';
				}
			echo '   -> sleep 2'.PHP_EOL;
				sleep(2);
			echo '   -> check/get_attempts';
				if($class->check() && ($class->get_attempts() === 3))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' check/get_attempts phase 2';
				}
			echo '   -> del/check/get_attempts';
				$class->del();

				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' del/check/get_attempts phase 2';
				}

		echo '  -> phase 3 '.PHP_EOL;
			$GLOBALS['_on_ban_count']=0;
			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' add/check/get_attempts phase 3';
					}
				}
				if($GLOBALS['_on_ban_count'] === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' add/check/get_attempts phase 3 on_ban counter checking';
				}
			echo '   -> sleep 3'.PHP_EOL;
				sleep(3);
			echo '   -> check/get_attempts';
				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' check/get_attempts phase 3';
				}
			echo '   -> del/check/get_attempts';
				$class->del();

				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' del/check/get_attempts phase 3';
				}
	}

	echo ' -> Testing bruteforce_mixed (PDO)'.PHP_EOL;
		$tempban_hook=new bruteforce_timeout_pdo([
			'pdo_handler'=>$pdo_handler,
			'table_name'=>'sec_bruteforce_mixed_temp_ban',
			'max_attempts'=>3,
			'ip'=>'1.2.3.4',
			'ban_time'=>2
		]);
		$permban_hook=new bruteforce_pdo([
			'pdo_handler'=>$pdo_handler,
			'table_name'=>'sec_bruteforce_mixed_perm_ban',
			'max_attempts'=>3,
			'ip'=>'1.2.3.4'
		]);

		for($i=1; $i<=2; ++$i)
		{
			echo '  -> temp ban no '.$i.PHP_EOL;
				echo '   -> phase 1';
					for($y=1; $y<=3; ++$y)
						$tempban_hook->add();

					if(bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='bruteforce_mixed temp ban '.$i.' phase 1';
					}
				echo '   -> sleep 3'.PHP_EOL;
					sleep(3);
				echo '   -> phase 2';
					if(!bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='bruteforce_mixed temp ban '.$i.' phase 2';
					}
		}
		echo '  -> perm ban'.PHP_EOL;
			echo '   -> phase 1';
				for($y=1; $y<=3; ++$y)
					$tempban_hook->add();

				if(bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='bruteforce_mixed perm ban '.$i.' phase 1';
				}
			echo '   -> sleep 3'.PHP_EOL;
				sleep(3);
			echo '   -> phase 2';
				if(bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='bruteforce_mixed perm ban '.$i.' phase 2/1';
				}
				if($permban_hook->check())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='bruteforce_mixed perm ban phase 2/2';
				}

	echo ' -> Testing clean_database'.PHP_EOL;
		echo '  -> bruteforce_pdo'.PHP_EOL;
			echo '   -> add/sleep 2/add/clean_database'.PHP_EOL;
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.3.4'
				]);
				$class->add();
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_clean_database',
					'max_attempts'=>3,
					'ip'=>'5.6.7.8'
				]);
				$class->add();
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->add();
			// -> sleep 2
				sleep(2);
			// -> add
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.7.9'
				]);
				$class->add();
			// -> clean_database
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->clean_database(1);
			echo '   -> check';
				$query=$pdo_handler->query('SELECT COUNT(*) FROM sec_bruteforce_clean_database');
				if(count($query->fetch(PDO::FETCH_NUM)) === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='clean_database bruteforce_pdo';
				}
		echo '  -> bruteforce_timeout_pdo'.PHP_EOL;
			echo '   -> add/sleep 2/add/clean_database'.PHP_EOL;
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.3.4'
				]);
				$class->add();
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_clean_database',
					'max_attempts'=>3,
					'ip'=>'5.6.7.8'
				]);
				$class->add();
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->add();
			// -> sleep 2
				sleep(2);
			// -> add
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.7.9'
				]);
				$class->add();
			// -> clean_database
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_clean_database',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->clean_database(1);
			echo '   -> check';
				$query=$pdo_handler->query('SELECT COUNT(*) FROM sec_bruteforce_timeout_clean_database');
				if(count($query->fetch(PDO::FETCH_NUM)) === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='clean_database bruteforce_timeout_pdo';
				}
		echo '  -> bruteforce_json'.PHP_EOL;
			echo '   -> add/sleep 2/add/clean_database'.PHP_EOL;
				$class=new bruteforce_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.3.4'
				]);
				$class->add();
				$class->__destruct();
				$class=new bruteforce_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'5.6.7.8'
				]);
				$class->add();
				$class->__destruct();
				$class=new bruteforce_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->add();
				$class->__destruct();
			// -> sleep 2
				sleep(2);
			// -> add
				$class=new bruteforce_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.7.9'
				]);
				$class->add();
				$class->__destruct();
			// -> clean_database
				$class=new bruteforce_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->clean_database(1);
				$class->__destruct();
			echo '   -> check';
				if(count(json_decode(file_get_contents(__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_clean_database.json'), true)) === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='clean_database bruteforce_json';
				}
		echo '  -> bruteforce_timeout_json'.PHP_EOL;
			echo '   -> add/sleep 2/add/clean_database'.PHP_EOL;
				$class=new bruteforce_timeout_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.3.4'
				]);
				$class->add();
				$class->__destruct();
				$class=new bruteforce_timeout_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'5.6.7.8'
				]);
				$class->add();
				$class->__destruct();
				$class=new bruteforce_timeout_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->add();
				$class->__destruct();
			// -> sleep 2
				sleep(2);
			// -> add
				$class=new bruteforce_timeout_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.7.9'
				]);
				$class->add();
				$class->__destruct();
			// -> clean_database
				$class=new bruteforce_timeout_json([
					'file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json',
					'lock_file'=>__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json.lock',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->clean_database(1);
				$class->__destruct();
			echo '   -> check';
				if(count(json_decode(file_get_contents(__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout_clean_database.json'), true)) === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='clean_database bruteforce_timeout_json';
				}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>