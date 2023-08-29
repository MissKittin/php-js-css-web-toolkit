<?php
	/*
	 * cache_container.php library test
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
		@mkdir(__DIR__.'/tmp/cache_container');
		foreach([
			'cache_container.json',
			'cache_container.json.lock',
			'cache_container_realtime.json',
			'cache_container_realtime.json.lock',
			'cache_container.sqlite3'
		] as $file)
			@unlink(__DIR__.'/tmp/cache_container/'.$file);
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
			$pdo_handler->exec('DROP TABLE cache_container');
	}
	if(!isset($pdo_handler))
	{
		if(!extension_loaded('pdo_sqlite'))
		{
			echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
			exit(1);
		}

		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/cache_container/cache_container.sqlite3');
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
			foreach([
				'increment_test',
				'incrementb_test',
				'decrement_test',
				'timeout_test',
				'flush_test'
			] as $_redis['_key'])
				$redis_handler->del('cache_container_test__'.$_redis['_key']);
	}

	$cache_drivers=[
		'cache_driver_none'=>null,
		'cache_driver_file'=>[
			'file'=>__DIR__.'/tmp/cache_container/cache_container.json',
			'lock_file'=>__DIR__.'/tmp/cache_container/cache_container.json.lock'
		],
		'cache_driver_file_realtime'=>[
			'file'=>__DIR__.'/tmp/cache_container/cache_container_realtime.json',
			'lock_file'=>__DIR__.'/tmp/cache_container/cache_container_realtime.json.lock'
		],
		'cache_driver_pdo'=>[
			'pdo_handler'=>$pdo_handler
		]
	];

	if(isset($redis_handler))
		$cache_drivers['cache_driver_redis']=[
			'redis_handler'=>$redis_handler,
			'prefix'=>'cache_container_test__'
		];
	else
		echo ' -> Skipping cache_driver_redis'.PHP_EOL;

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