<?php
	/*
	 * cache_container.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *  looks for a tool at ../../bin
	 *
	 * Hint:
	 *  you can setup Redis credentials by environment variables
	 *  variables:
	 *   TEST_REDIS=yes (default: no)
	 *   TEST_REDIS_HOST (default: 127.0.0.1)
	 *   TEST_REDIS_SOCKET (has priority over the HOST)
	 *    eg. /var/run/redis/redis.sock
	 *   TEST_REDIS_PORT (default: 6379)
	 *   TEST_REDIS_DBINDEX (default: 0)
	 *   TEST_REDIS_USER
	 *   TEST_REDIS_PASSWORD
	 *  you can also use the Predis package instead of PHPRedis extension:
	 *   TEST_REDIS_PREDIS=yes (default: no)
	 *
	 * Hint:
	 *  you can setup Memcached credentials by environment variables
	 *  variables:
	 *   TEST_MEMCACHED=yes (default: no)
	 *   TEST_MEMCACHED_HOST (default: 127.0.0.1)
	 *   TEST_MEMCACHED_SOCKET (has priority over the HOST)
	 *    eg. /var/run/memcached/memcached.sock
	 *   TEST_MEMCACHED_PORT (default: 11211)
	 *  you can also use the memcached.php package instead of memcached extension:
	 *   TEST_MEMCACHED_CM=yes (default: no)
	 *
	 * Hint:
	 *  you can force APCu to be enabled via an environment variable:
	 *   TEST_APCU=yes (default: no)
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
	 *  apcu extension is recommended
	 *  memcached extension is recommended or
	 *   clickalicious_memcached.php library is recommended
	 *  PDO extension is recommended
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  redis extension is recommended or
	 *   predis_connect.php library is recommended
	 *  get-composer.php tool is recommended for predis
	 *  shmop extension is recommended
	 */

	if(
		function_exists('apcu_enabled') &&
		(getenv('TEST_APCU') === 'yes') &&
		(!apcu_enabled())
	){
		if(
			isset($argv[1]) &&
			($argv[1] !== 'apcu-force')
		)
			echo ' -> Force APCu apc.enable_cli=1 [FAIL]'.PHP_EOL;
		else
		{
			echo ' -> Force APCu apc.enable_cli=1'.PHP_EOL;

			system(''
			.	'"'.PHP_BINARY.'" '
			.	'-d apc.enable_cli=1 '
			.	'"'.$argv[0].'" '
			.	'apcu-force'
			,	$test_result
			);
			exit($test_result);
		}
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
			$pdo_handle->exec('DROP TABLE IF EXISTS cache_container_test');
	}
	if(
		(!isset($pdo_handle)) &&
		class_exists('PDO') &&
		in_array('sqlite', PDO::getAvailableDrivers())
	)
		$pdo_handle=new PDO('sqlite:'.__DIR__.'/tmp/cache_container/cache_container.sqlite3');

	if(getenv('TEST_REDIS') === 'yes')
	{
		echo ' -> Configuring Redis'.PHP_EOL;

		$_redis=[
			'credentials'=>[
				'host'=>'127.0.0.1',
				'port'=>6379,
				'socket'=>null,
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

		foreach(['host', 'port', 'socket', 'dbindex', 'user', 'password'] as $_redis['_parameter'])
		{
			$_redis['_variable']='TEST_REDIS_'.strtoupper($_redis['_parameter']);
			$_redis['_value']=getenv($_redis['_variable']);

			if($_redis['_value'] !== false)
			{
				echo '  -> Using '.$_redis['_variable'].'="'.$_redis['_value'].'" as Redis '.$_redis['_parameter'].PHP_EOL;
				$_redis['credentials'][$_redis['_parameter']]=$_redis['_value'];
			}
		}

		if($_redis['credentials']['socket'] !== null)
		{
			$_redis['credentials']['host']='unix://'.$_redis['credentials']['socket'];
			$_redis['credentials']['port']=0;
		}

		if($_redis['credentials']['user'] !== null)
			$_redis['_credentials_auth']['user']=$_redis['credentials']['user'];
		if($_redis['credentials']['password'] !== null)
			$_redis['_credentials_auth']['pass']=$_redis['credentials']['password'];

		if(getenv('TEST_REDIS_PREDIS') === 'yes')
		{
			echo '  -> Including predis_connect.php';
				if(is_file(__DIR__.'/../lib/predis_connect.php'))
				{
					if(@(include __DIR__.'/../lib/predis_connect.php') === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../predis_connect.php'))
				{
					if(@(include __DIR__.'/../predis_connect.php') === false)
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

			if(!file_exists(__DIR__.'/tmp/.composer/vendor/predis/predis'))
			{
				@mkdir(__DIR__.'/tmp');
				@mkdir(__DIR__.'/tmp/.composer');

				if(file_exists(__DIR__.'/../../bin/composer.phar'))
					$_composer_binary=__DIR__.'/../../bin/composer.phar';
				else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
					$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
				else if(file_exists(__DIR__.'/../../bin/get-composer.php'))
				{
					echo '  -> Downloading composer'.PHP_EOL;

					system(''
					.	'"'.PHP_BINARY.'" '
					.	'"'.__DIR__.'/../../bin/get-composer.php" '
					.	'"'.__DIR__.'/tmp/.composer"'
					);

					if(!file_exists(__DIR__.'/tmp/.composer/composer.phar'))
					{
						echo '  <- composer download failed [FAIL]'.PHP_EOL;
						exit(1);
					}

					$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
				}
				else
				{
					echo 'Error: get-composer.php tool not found'.PHP_EOL;
					exit(1);
				}

				echo '  -> Installing predis/predis'.PHP_EOL;
					system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
					.	'--no-cache '
					.	'"--working-dir='.__DIR__.'/tmp/.composer" '
					.	'require predis/predis'
					);
			}

			echo '  -> Including composer autoloader';
				if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;

			if(!class_exists('\Predis\Client'))
			{
				echo '  <- predis/predis package is not installed [FAIL]'.PHP_EOL;
				exit(1);
			}

			echo '  -> Configuring Predis'.PHP_EOL;
				$_redis['_predis']=[
					[
						'scheme'=>'tcp',
						'host'=>$_redis['credentials']['host'],
						'port'=>$_redis['credentials']['port'],
						'database'=>$_redis['credentials']['dbindex']
					],
					[
						'scheme'=>'unix',
						'path'=>$_redis['credentials']['socket'],
						'database'=>$_redis['credentials']['dbindex']
					]
				];

				if($_redis['credentials']['password'] !== null)
				{
					$_redis['_predis'][0]['password']=$_redis['credentials']['password'];
					$_redis['_predis'][1]['password']=$_redis['credentials']['password'];
				}

			echo '  -> Connecting to the redis server (predis)'.PHP_EOL;
				try {
					if($_redis['credentials']['socket'] === null)
						$redis_handle=new predis_phpredis_proxy(new \Predis\Client($_redis['_predis'][0]));
					else
						$redis_handle=new predis_phpredis_proxy(new \Predis\Client($_redis['_predis'][1]));

					$redis_handle->connect();
				} catch(Throwable $error) {
					echo ' Error: '.$error->getMessage().PHP_EOL;
					exit(1);
				}
		}
		else
		{
			if(!class_exists('Redis'))
			{
				echo 'redis extension is not loaded'.PHP_EOL;
				exit(1);
			}

			echo '  -> Connecting to the redis server (phpredis)'.PHP_EOL;

			try {
				$redis_handle=new Redis();

				if($redis_handle->connect(
					$_redis['credentials']['host'],
					$_redis['credentials']['port'],
					$_redis['connection_options']['timeout'],
					null,
					$_redis['connection_options']['retry_interval'],
					$_redis['connection_options']['read_timeout']
				) === false){
					echo '  -> Redis connection error'.PHP_EOL;
					unset($redis_handle);
				}

				if(
					(isset($redis_handle)) &&
					(isset($_redis['_credentials_auth'])) &&
					(!$redis_handle->auth($_redis['_credentials_auth']))
				){
					echo '  -> Redis auth error'.PHP_EOL;
					unset($redis_handle);
				}

				if(
					(isset($redis_handle)) &&
					(!$redis_handle->select($_redis['credentials']['dbindex']))
				){
					echo '  -> Redis database select error'.PHP_EOL;
					unset($redis_handle);
				}
			} catch(Throwable $error) {
				echo ' Error: '.$error->getMessage().PHP_EOL;
				exit(1);
			}
		}

		if(isset($redis_handle))
			foreach([
				'increment_test',
				'incrementb_test',
				'decrement_test',
				'timeout_test',
				'flush_test'
			] as $_redis['_key'])
				$redis_handle->del('cache_container_test__'.$_redis['_key']);
	}

	if(getenv('TEST_MEMCACHED') === 'yes')
	{
		$_memcached_client='Memcached';

		if(getenv('TEST_MEMCACHED_CM') === 'yes')
		{
			$_memcached_client='clickalicious_memcached';

			echo '  -> Including clickalicious_memcached.php';
				if(is_file(__DIR__.'/../lib/clickalicious_memcached.php'))
				{
					if(@(include __DIR__.'/../lib/clickalicious_memcached.php') === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../clickalicious_memcached.php'))
				{
					if(@(include __DIR__.'/../clickalicious_memcached.php') === false)
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

			if(!file_exists(__DIR__.'/tmp/.composer/vendor/clickalicious/memcached.php'))
			{
				@mkdir(__DIR__.'/tmp');
				@mkdir(__DIR__.'/tmp/.composer');

				if(file_exists(__DIR__.'/../../bin/composer.phar'))
					$_composer_binary=__DIR__.'/../../bin/composer.phar';
				else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
					$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
				else if(file_exists(__DIR__.'/../../bin/get-composer.php'))
				{
					echo '  -> Downloading composer'.PHP_EOL;

					system(''
					.	'"'.PHP_BINARY.'" '
					.	'"'.__DIR__.'/../../bin/get-composer.php" '
					.	'"'.__DIR__.'/tmp/.composer"'
					);

					if(!file_exists(__DIR__.'/tmp/.composer/composer.phar'))
					{
						echo '  <- composer download failed [FAIL]'.PHP_EOL;
						exit(1);
					}

					$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
				}
				else
				{
					echo 'Error: get-composer.php tool not found'.PHP_EOL;
					exit(1);
				}

				echo '  -> Installing clickalicious/memcached.php'.PHP_EOL;
					system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
					.	'--no-cache '
					.	'"--working-dir='.__DIR__.'/tmp/.composer" '
					.	'require clickalicious/memcached.php'
					);
			}

			echo '  -> Including composer autoloader';
				if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;

			if(!class_exists('\Clickalicious\Memcached\Client'))
			{
				echo '  <- clickalicious/memcached.php package is not installed [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(!class_exists('Memcached'))
		{
			echo 'memcached extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Configuring Memcached'.PHP_EOL;

		$_memcached=[
			'credentials'=>[
				'host'=>'127.0.0.1',
				'port'=>11211,
				'socket'=>null
			]
		];

		foreach(['host', 'port', 'socket'] as $_memcached['_parameter'])
		{
			$_memcached['_variable']='TEST_MEMCACHED_'.strtoupper($_memcached['_parameter']);
			$_memcached['_value']=getenv($_memcached['_variable']);

			if($_memcached['_value'] !== false)
			{
				echo '  -> Using '.$_memcached['_variable'].'="'.$_memcached['_value'].'" as Memcached '.$_memcached['_parameter'].PHP_EOL;
				$_memcached['credentials'][$_memcached['_parameter']]=$_memcached['_value'];
			}
		}

		if($_memcached['credentials']['socket'] !== null)
		{
			$_memcached['credentials']['host']=$_memcached['credentials']['socket'];
			$_memcached['credentials']['port']=0;
		}

		$memcached_handle=new $_memcached_client();

		if(!$memcached_handle->addServer(
			$_memcached['credentials']['host'],
			$_memcached['credentials']['port']
		)){
			echo '  -> Memcached connection error'.PHP_EOL;
			unset($memcached_handle);
		}

		if(isset($memcached_handle))
			foreach([
				'increment_test',
				'incrementb_test',
				'decrement_test',
				'timeout_test',
				'flush_test'
			] as $_memcached['_key'])
				$memcached_handle->delete('cache_container_test__'.$_memcached['_key']);
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
		]
	];

	if(isset($pdo_handle))
		$cache_drivers['cache_driver_pdo']=[
			'pdo_handle'=>$pdo_handle,
			'table_name'=>'cache_container_test'
		];
	else
		echo ' -> Skipping cache_driver_pdo'.PHP_EOL;

	if(isset($redis_handle))
		$cache_drivers['cache_driver_redis']=[
			'redis_handle'=>$redis_handle,
			'prefix'=>'cache_container_test__'
		];
	else
		echo ' -> Skipping cache_driver_redis'.PHP_EOL;

	if(isset($memcached_handle))
		$cache_drivers['cache_driver_memcached']=[
			'memcached_handle'=>$memcached_handle,
			'prefix'=>'cache_container_test__'
		];
	else
		echo ' -> Skipping cache_driver_memcached'.PHP_EOL;

	if(function_exists('apcu_enabled') && (getenv('TEST_APCU') === 'yes'))
	{
		if(apcu_enabled())
			$cache_drivers['cache_driver_apcu']=[
				'prefix'=>'cache_container_test__'
			];
		else
			echo ' -> Skipping cache_driver_apcu - APCu disabled'.PHP_EOL;
	}
	else
		echo ' -> Skipping cache_driver_apcu'.PHP_EOL;

	if(function_exists('shmop_open'))
		$cache_drivers['cache_driver_shm']=[];
	else
		echo ' -> Skipping cache_driver_shm'.PHP_EOL;

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
					if($driver_name === 'cache_driver_memcached')
						echo ' [SKIP]'.PHP_EOL;
					else
					{
						$current_container->put('flush_test', 'example');
						$current_container->flush();
						if($current_container->get('flush_test') === null)
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							$errors[$cache_container.' => '.$driver_name]='[TEST] put/flush/get failed';
							echo ' [FAIL]'.PHP_EOL;
						}
					}
			} catch(Throwable $error) {
				echo '  <- Testing driver '.$driver_name.' [FAIL]'.PHP_EOL;
				$errors[$cache_container.' => '.$driver_name]=$error->getMessage();
				$pdo_errors[$cache_container.' => '.$driver_name]=$pdo_handle->errorInfo()[2];
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