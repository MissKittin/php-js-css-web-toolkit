<?php
	/*
	 * sec_bruteforce.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Hint:
	 *  you can setup Redis server address and port by environment variables
	 *  variables:
	 *   TEST_REDIS_HOST (default: 127.0.0.1)
	 *   TEST_REDIS_PORT (default: 6379)
	 *  to skip cleaning the Redis database,
	 *   run the test with the --no-redis-clean parameter
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite, overrides first argument)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_DBNAME (default: php-toolkit-tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 *  redis extension is recommended
	 */

	foreach(['PDO', 'pdo_sqlite'] as $extension)
		if(!extension_loaded($extension))
		{
			echo $extension.' extension is not loaded'.PHP_EOL;
			exit(1);
		}

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			ob_end_flush();
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/sec_bruteforce');
		foreach([
			'sec_bruteforce.sqlite3',
			'sec_bruteforce_timeout.sqlite3',
			'sec_bruteforce_resume.sqlite3',

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

			'sec_bruteforce_mixed.sqlite3'
		] as $file)
			@unlink(__DIR__.'/tmp/sec_bruteforce/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	if(getenv('TEST_DB_TYPE') !== false)
		$argv[1]=getenv('TEST_DB_TYPE');
	if(isset($argv[1]))
	{
		$_db_type=$argv[1];
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
				'dbname'=>'php-toolkit-tests',
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
					$_db_credentials[$database][$parameter]=$value;
				}
			}

		try {
			switch($_db_type)
			{
				case 'pgsql':
					if(!extension_loaded('pdo_pgsql'))
						throw new Exception('pdo_pgsql extension is not loaded');

					$pdo_handler=new PDO('pgsql:'
						.'host='.$_db_credentials[$_db_type]['host'].';'
						.'port='.$_db_credentials[$_db_type]['port'].';'
						.'dbname='.$_db_credentials[$_db_type]['dbname'].';'
						.'user='.$_db_credentials[$_db_type]['user'].';'
						.'password='.$_db_credentials[$_db_type]['password'].''
					);
				break;
				case 'mysql':
					if(!extension_loaded('pdo_mysql'))
						throw new Exception('pdo_mysql extension is not loaded');

					$pdo_handler=new PDO('mysql:'
						.'host='.$_db_credentials[$_db_type]['host'].';'
						.'port='.$_db_credentials[$_db_type]['port'].';'
						.'dbname='.$_db_credentials[$_db_type]['dbname'],
						$_db_credentials[$_db_type]['user'],
						$_db_credentials[$_db_type]['password']
					);
			}
		} catch(Throwable $error) {
			echo ' Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(isset($pdo_handler))
		{
			$pdo_handler->exec('DROP TABLE sec_bruteforce');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_cd');
			$pdo_handler->exec('DROP TABLE sec_bruteforce_timeout_cd');
		}
	}
	if(!isset($pdo_handler))
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce/sec_bruteforce.sqlite3');

	$GLOBALS['_redis_handler']=null;
	if(extension_loaded('redis'))
	{
		$GLOBALS['_redis_handler']=new Redis();

		$_redis_host=getenv('TEST_REDIS_HOST');
		$_redis_port=getenv('TEST_REDIS_PORT');

		if($_redis_host === false)
			$_redis_host='127.0.0.1';
		if($_redis_port === false)
			$_redis_port=6379;

		if($GLOBALS['_redis_handler']->connect($_redis_host, $_redis_port))
		{
			echo ' -> Removing Redis records';
				$GLOBALS['_redis_handler']->del('bruteforce_redis_test__1.2.3.4');
				$GLOBALS['_redis_handler']->del('bruteforce_redis_test_resume__1.2.3.4');
				$GLOBALS['_redis_handler']->del('bruteforce_redis_test_timeout__1.2.3.4');
			echo ' [ OK ]'.PHP_EOL;
		}
		else
		{
			echo ' -> bruteforce_redis connection error [SKIP]'.PHP_EOL;
			$GLOBALS['_redis_handler']=null;
		}

		unset($_redis_host);
		unset($_redis_port);
	}
	else
		echo ' -> bruteforce_redis redis extension is not loaded [SKIP]'.PHP_EOL;

	function on_ban_callback()
	{
		++$GLOBALS['_on_ban_count'];
	}
	function setup_objects()
	{
		global $pdo_handler;

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

		if($GLOBALS['_redis_handler'] !== null)
			$objects['bruteforce_redis']=new bruteforce_redis([
				'redis_handler'=>$GLOBALS['_redis_handler'],
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

		if($GLOBALS['_redis_handler'] !== null)
			$objects['bruteforce_redis']=new bruteforce_redis([
				'redis_handler'=>$GLOBALS['_redis_handler'],
				'prefix'=>'bruteforce_redis_test_resume__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'on_ban'=>'on_ban_callback'
			]);

		return $objects;
	}
	function setup_timeout_objects()
	{
		$objects=[
			'bruteforce_timeout_pdo'=>new bruteforce_timeout_pdo([
				'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_timeout.sqlite3'),
				'table_name'=>'sec_bruteforce',
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

		if($GLOBALS['_redis_handler'] !== null)
			$objects['bruteforce_timeout_redis']=new bruteforce_timeout_redis([
				'redis_handler'=>$GLOBALS['_redis_handler'],
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
			'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_mixed.sqlite3'),
			'table_name'=>'temp_ban',
			'max_attempts'=>3,
			'ip'=>'1.2.3.4',
			'ban_time'=>2
		]);
		$permban_hook=new bruteforce_pdo([
			'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce/sec_bruteforce_mixed.sqlite3'),
			'table_name'=>'perm_ban',
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
					'table_name'=>'sec_bruteforce_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.3.4'
				]);
				$class->add();
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_cd',
					'max_attempts'=>3,
					'ip'=>'5.6.7.8'
				]);
				$class->add();
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->add();
			// -> sleep 2
				sleep(2);
			// -> add
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.7.9'
				]);
				$class->add();
			// -> clean_database
				$class=new bruteforce_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->clean_database(1);
			echo '   -> check';
				$query=$pdo_handler->query('SELECT COUNT(*) FROM sec_bruteforce_cd');
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
					'table_name'=>'sec_bruteforce_timeout_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.3.4'
				]);
				$class->add();
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_cd',
					'max_attempts'=>3,
					'ip'=>'5.6.7.8'
				]);
				$class->add();
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->add();
			// -> sleep 2
				sleep(2);
			// -> add
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.7.9'
				]);
				$class->add();
			// -> clean_database
				$class=new bruteforce_timeout_pdo([
					'pdo_handler'=>$pdo_handler,
					'table_name'=>'sec_bruteforce_timeout_cd',
					'max_attempts'=>3,
					'ip'=>'1.2.7.8'
				]);
				$class->clean_database(1);
			echo '   -> check';
				$query=$pdo_handler->query('SELECT COUNT(*) FROM sec_bruteforce_timeout_cd');
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

	if(
		($GLOBALS['_redis_handler'] !== null) &&
		(@$argv[1] !== '--no-redis-clean')
	){
		echo ' -> Removing Redis records';
			$GLOBALS['_redis_handler']->del('bruteforce_redis_test__1.2.3.4');
			$GLOBALS['_redis_handler']->del('bruteforce_redis_test_resume__1.2.3.4');
			$GLOBALS['_redis_handler']->del('bruteforce_redis_test_timeout__1.2.3.4');
		echo ' [ OK ]'.PHP_EOL;
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>