<?php
	/*
	 * cache_container.php library test
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
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		foreach([
			'cache_container.json',
			'cache_container.json.lock',
			'cache_container_realtime.json',
			'cache_container_realtime.json.lock',
			'cache_container.sqlite3'
		] as $file)
			@unlink(__DIR__.'/tmp/'.$file);
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
			$pdo_handler->exec('DROP TABLE cache_container');
	}
	if(!isset($pdo_handler))
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/cache_container.sqlite3');

	$cache_drivers=[
		'cache_driver_none'=>null,
		'cache_driver_file'=>[
			'file'=>__DIR__.'/tmp/cache_container.json',
			'lock_file'=>__DIR__.'/tmp/cache_container.json.lock'
		],
		'cache_driver_file_realtime'=>[
			'file'=>__DIR__.'/tmp/cache_container_realtime.json',
			'lock_file'=>__DIR__.'/tmp/cache_container_realtime.json.lock'
		],
		'cache_driver_pdo'=>[
			'pdo_handler'=>$pdo_handler
		]
	];
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

		if(!$GLOBALS['_redis_handler']->connect($_redis_host, $_redis_port))
		{
			echo ' -> cache_driver_redis connection error [SKIP]'.PHP_EOL;
			$GLOBALS['_redis_handler']=null;
		}
		else
		{
			$cache_drivers['cache_driver_redis']=[
				'redis_handler'=>$GLOBALS['_redis_handler'],
				'prefix'=>'cache_container_test__'
			];

			echo ' -> Removing Redis records';
				foreach([
					'increment_test',
					'incrementb_test',
					'decrement_test',
					'timeout_test',
					'flush_test'
				] as $key)
					$GLOBALS['_redis_handler']->del('cache_container_test__'.$key);
			echo ' [ OK ]'.PHP_EOL;
		}

		unset($_redis_host);
		unset($_redis_port);
	}
	else
		echo ' -> cache_driver_redis redis extension is not loaded [SKIP]'.PHP_EOL;

	if(isset($argv[1]))
	{
		unset($cache_drivers['cache_driver_none']);
		unset($cache_drivers['cache_driver_file']);
		unset($cache_drivers['cache_driver_file_realtime']);
		unset($cache_drivers['cache_driver_redis']);
	}

	$errors=[];
	$pdo_errors=[];

	foreach(['cache_container', 'cache_container_lite'] as $cache_container)
	{
		echo ' -> Testing container '.$cache_container.PHP_EOL;

		foreach($cache_drivers as $driver_name=>$driver_params)
		{
			if(($cache_container === 'cache_container_lite') && ($driver_name === 'cache_driver_none'))
			{
				echo '  -> Skipping cache_driver_none'.PHP_EOL;
				continue;
			}

			echo '  -> Testing driver '.$driver_name.PHP_EOL;

			try {
				$current_container=new $cache_container(new $driver_name($driver_params));

				if($cache_container === 'cache_container')
				{
					echo '   -> put_temp/get';
						$current_container->put_temp('put_temp_test', 'good');
						if($current_container->get('put_temp_test') === 'good')
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							$errors[$cache_container.' => '.$driver_name]='[TEST] put_temp/get failed';
							echo ' [FAIL]'.PHP_EOL;
						}
				}
				else
					echo '   -> Skipping put_temp/get'.PHP_EOL;

				if($cache_container === 'cache_container')
				{
					echo '   -> put_temp 2/isset';
						$current_container->put_temp('put_tempb_test', 'good', 2);
						sleep(3);
						if(!$current_container->isset('put_tempb_test'))
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							$errors[$cache_container.' => '.$driver_name]='[TEST] put_temp 2/isset failed';
							echo ' [FAIL]'.PHP_EOL;
						}
				}
				else
					echo '   -> Skipping put_temp 2/isset'.PHP_EOL;

				echo '   -> put/increment/get';
					$current_container->put('increment_test', 2);
					$current_container->increment('increment_test');
					if($current_container->get('increment_test') == 3)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/increment/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/increment 3/get';
					$current_container->put('incrementb_test', 2);
					$current_container->increment('incrementb_test', 3);
					if($current_container->get('incrementb_test') == 5)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/increment 3/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/decrement/get';
					$current_container->put('decrement_test', 3);
					$current_container->decrement('decrement_test');
					if($current_container->get('decrement_test') == 2)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/decrement/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/decrement 3/get';
					$current_container->put('decrement_test', 5);
					$current_container->decrement('decrement_test', 3);
					if($current_container->get('decrement_test') == 2)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/decrement 3/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> isset';
					if($current_container->isset('decrement_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> pull/isset';
					if($current_container->pull('decrement_test') == 2)
						echo ' [ OK ]';
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] pull/isset pull failed';
						echo ' [FAIL]';
					}
					if(!$current_container->isset('decrement_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] pull/isset isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> unset/isset';
					$current_container->unset('increment_test');
					if(!$current_container->isset('increment_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] unset/isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put 2/isset';
					$current_container->put('timeout_test', 'value', 2);
					sleep(3);
					if(!$current_container->isset('timeout_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put 2/isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/flush/get';
					$current_container->put('flush_test', 'example');
					$current_container->flush();
					if($current_container->get('flush_test') === null)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/flush/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}
			} catch(Throwable $error) {
				echo '  <- Testing driver '.$driver_name.' [FAIL]'.PHP_EOL;
				$errors[$cache_container.' => '.$driver_name]=$error->getMessage();
				$pdo_errors[$cache_container.' => '.$driver_name]=$pdo_handler->errorInfo()[2];
			}
		}
	}

	if(
		($GLOBALS['_redis_handler'] !== null) &&
		(@$argv[1] !== '--no-redis-clean')
	){
		echo ' -> Removing Redis records';
			foreach([
				'increment_test',
				'incrementb_test',
				'decrement_test',
				'timeout_test',
				'flush_test'
			] as $key)
				$GLOBALS['_redis_handler']->del('cache_container_test__'.$key);
		echo ' [ OK ]'.PHP_EOL;
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error_class=>$error_content)
			echo $error_class.': '.$error_content.PHP_EOL;

		if(!empty($pdo_errors))
			{
				echo PHP_EOL;

				foreach($pdo_errors as $method=>$error)
					echo $method.': '.$error.PHP_EOL;
			}

		exit(1);
	}
?>