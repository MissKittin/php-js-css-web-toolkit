<?php
	/*
	 * sec_lv_encrypter.php library test
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
	 *  openssl extension is required
	 *  mbstring extensions is required
	 *  predis-connect.php library is required for predis
	 *  memcached extension is recommended
	 *  PDO extension is recommended
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  redis extension is recommended
	 *  get-composer.php tool is recommended for predis
	 */

	foreach(['openssl', 'mbstring'] as $extension)
		if(!extension_loaded($extension))
		{
			echo $extension.' extension is not loaded'.PHP_EOL;
			exit(1);
		}

	ob_start();

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

	if(isset($argv[1]) && ($argv[1] === '_restart_test_'))
		echo ' -> Removing temporary files [SKIP]'.PHP_EOL;
	else
	{
		echo ' -> Removing temporary files';

		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/sec_lv_encrypter');
		@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter.sqlite3');
		@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_pdo_handler_key');

		echo ' [ OK ]'.PHP_EOL;
	}

	if(getenv('TEST_DB_TYPE') !== false)
	{
		if(!extension_loaded('PDO'))
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

					if(!extension_loaded('pdo_pgsql'))
						throw new Exception('pdo_pgsql extension is not loaded');

					if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
						$pdo_handler=new PDO('pgsql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
							.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
							.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
						);
					else
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

					if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
						$pdo_handler=new PDO('mysql:'
							.'unix_socket='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
							$_pdo['credentials'][$_pdo['type']]['user'],
							$_pdo['credentials'][$_pdo['type']]['password']
						);
					else
						$pdo_handler=new PDO('mysql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
							.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
							$_pdo['credentials'][$_pdo['type']]['user'],
							$_pdo['credentials'][$_pdo['type']]['password']
						);
				break;
				case 'sqlite':
					if(!extension_loaded('pdo_sqlite'))
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
	}
	if(isset($pdo_handler))
	{
		if(!(isset($argv[1]) && ($argv[1] === '_restart_test_')))
			$pdo_handler->exec('DROP TABLE sec_lv_encrypter_pdo_session_handler');
	}
	else if(
		(!isset($pdo_handler)) &&
		extension_loaded('PDO') &&
		extension_loaded('pdo_sqlite')
	)
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter.sqlite3');

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

			if(!file_exists(__DIR__.'/tmp/.composer/vendor/predis'))
			{
				echo '  -> Installing Predis'.PHP_EOL;

				@mkdir(__DIR__.'/tmp');
				@mkdir(__DIR__.'/tmp/.composer');

				if(file_exists(__DIR__.'/../../bin/composer.phar'))
					system('"'.PHP_BINARY.'" '.__DIR__.'/../../bin/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require predis/predis');
				else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
					system('"'.PHP_BINARY.'" '.__DIR__.'/tmp/.composer/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require predis/predis');
				else if(file_exists(__DIR__.'/../../bin/get-composer.php'))
				{
					system('"'.PHP_BINARY.'" '.__DIR__.'/../../bin/get-composer.php '.__DIR__.'/tmp/.composer');
					system('"'.PHP_BINARY.'" '.__DIR__.'/tmp/.composer/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require predis/predis');
				}
				else
				{
					echo 'Error: get-composer.php tool not found'.PHP_EOL;
					exit(1);
				}
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
				echo '  <- predis package is not installed [FAIL]'.PHP_EOL;
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
						$redis_handler=new predis_phpredis_proxy(new \Predis\Client($_redis['_predis'][0]));
					else
						$redis_handler=new predis_phpredis_proxy(new \Predis\Client($_redis['_predis'][1]));

					$redis_handler->connect();
				} catch(Throwable $error) {
					echo ' Error: '.$error->getMessage().PHP_EOL;
					exit(1);
				}
		}
		else
		{
			if(!extension_loaded('redis'))
			{
				echo 'redis extension is not loaded'.PHP_EOL;
				exit(1);
			}

			echo '  -> Connecting to the redis server (phpredis)'.PHP_EOL;

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
		}
	}

	if(getenv('TEST_MEMCACHED') === 'yes')
	{
		if(!extension_loaded('memcached'))
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

		$memcached_handler=new Memcached();

		if(!$memcached_handler->addServer(
			$_memcached['credentials']['host'],
			$_memcached['credentials']['port']
		)){
			echo '  -> Memcached connection error'.PHP_EOL;
			unset($memcached_handler);
		}
	}

	$restart_test=false;
	$errors=[];

	echo ' -> Testing lv_encrypter';
		$lv_encrypter=new lv_encrypter(lv_encrypter::generate_key());
		$secret_message=$lv_encrypter->encrypt('Secret message');
		if($lv_encrypter->decrypt($secret_message) === 'Secret message')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='lv_encrypter';
		}

	echo ' -> Testing lv_cookie_encrypter [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_session_encrypter [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_cookie_session_handler [SKIP]'.PHP_EOL;

	echo ' -> Testing lv_pdo_session_handler';
		if(isset($pdo_handler))
		{
			if(is_file(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_pdo_handler_key'))
			{
				$lv_pdo_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_pdo_handler_key');
				$lv_pdo_session_handler_do_save=false;
			}
			else
			{
				$lv_pdo_session_handler_key=lv_encrypter::generate_key();
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_pdo_handler_key', $lv_pdo_session_handler_key);
				$lv_pdo_session_handler_do_save=true;
			}

			session_set_save_handler(new lv_pdo_session_handler([
				'key'=>$lv_pdo_session_handler_key,
				'pdo_handler'=>$pdo_handler,
				'table_name'=>'sec_lv_encrypter_pdo_session_handler'
			]), true);
			session_id('123abc');
			session_start([
				'use_cookies'=>0,
				'cache_limiter'=>''
			]);
			if($lv_pdo_session_handler_do_save)
			{
				echo PHP_EOL.'  -> the $_SESSION will be saved to the database [SKIP]';

				$_SESSION['test_variable_a']='test_value_a';
				$_SESSION['test_variable_b']='test_value_b';

				$restart_test=true;
			}
			else
			{
				echo PHP_EOL.'  -> the $_SESSION was fetched from the database';
					if(isset($_SESSION['test_variable_a']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_pdo_session_handler fetch check phase 1';
					}
					if(isset($_SESSION['test_variable_b']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_pdo_session_handler fetch check phase 2';
					}
			}
			session_write_close();
			unset($_SESSION['test_variable_a']);
			unset($_SESSION['test_variable_b']);

			$output=$pdo_handler->query('SELECT * FROM sec_lv_encrypter_pdo_session_handler')->fetchAll();
			if(isset($output[0]['payload']))
			{
				$lv_pdo_session_handler_encrypter=new lv_encrypter($lv_pdo_session_handler_key);
				if($lv_pdo_session_handler_encrypter->decrypt($output[0]['payload'], false) === 'test_variable_a|s:12:"test_value_a";test_variable_b|s:12:"test_value_b";')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='lv_pdo_session_handler';
					$restart_test=false;
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='lv_pdo_session_handler';
				$restart_test=false;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing lv_redis_session_handler';
		if(isset($redis_handler))
		{
			if(is_file(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_redis_handler_key'))
			{
				$lv_redis_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_redis_handler_key');
				$lv_redis_session_handler_do_save=false;
			}
			else
			{
				$lv_redis_session_handler_key=lv_encrypter::generate_key();
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_redis_handler_key', $lv_redis_session_handler_key);
				$lv_redis_session_handler_do_save=true;
			}

			session_set_save_handler(new lv_redis_session_handler([
				'key'=>$lv_redis_session_handler_key,
				'redis_handler'=>$redis_handler,
				'prefix'=>'sec_lv_encrypter_redis_session_handler__'
			]), true);
			session_id('123abc');
			session_start([
				'use_cookies'=>0,
				'cache_limiter'=>''
			]);
			if($lv_redis_session_handler_do_save)
			{
				echo PHP_EOL.'  -> the $_SESSION will be saved to the database [SKIP]';

				$_SESSION['test_variable_ax']='test_value_ax';
				$_SESSION['test_variable_bx']='test_value_bx';

				$restart_test=true;
			}
			else
			{
				echo PHP_EOL.'  -> the $_SESSION was fetched from the database';
					if(isset($_SESSION['test_variable_ax']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_redis_session_handler fetch check phase 1';
					}
					if(isset($_SESSION['test_variable_bx']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_redis_session_handler fetch check phase 2';
					}
			}
			session_write_close();
			unset($_SESSION['test_variable_ax']);
			unset($_SESSION['test_variable_bx']);

			$output=$redis_handler->get('sec_lv_encrypter_redis_session_handler__123abc');
			if($output === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='lv_redis_session_handler';
				$restart_test=false;
			}
			else
			{
				$lv_redis_session_handler_encrypter=new lv_encrypter($lv_redis_session_handler_key);
				if($lv_redis_session_handler_encrypter->decrypt($output, false) === 'test_variable_ax|s:13:"test_value_ax";test_variable_bx|s:13:"test_value_bx";')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='lv_redis_session_handler';
					$restart_test=false;
				}
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing lv_memcached_session_handler';
		if(isset($memcached_handler))
		{
			if(is_file(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_memcached_handler_key'))
			{
				$lv_memcached_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_memcached_handler_key');
				$lv_memcached_session_handler_do_save=false;
			}
			else
			{
				$lv_memcached_session_handler_key=lv_encrypter::generate_key();
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_memcached_handler_key', $lv_memcached_session_handler_key);
				$lv_memcached_session_handler_do_save=true;
			}

			session_set_save_handler(new lv_memcached_session_handler([
				'key'=>$lv_memcached_session_handler_key,
				'memcached_handler'=>$memcached_handler,
				'prefix'=>'sec_lv_encrypter_memcached_session_handler__'
			]), true);
			session_id('123abc');
			session_start([
				'use_cookies'=>0,
				'cache_limiter'=>''
			]);
			if($lv_memcached_session_handler_do_save)
			{
				echo PHP_EOL.'  -> the $_SESSION will be saved to the database [SKIP]';

				$_SESSION['test_variable_ax']='test_value_ax';
				$_SESSION['test_variable_bx']='test_value_bx';

				$restart_test=true;
			}
			else
			{
				echo PHP_EOL.'  -> the $_SESSION was fetched from the database';
					if(isset($_SESSION['test_variable_ax']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_memcached_session_handler fetch check phase 1';
					}
					if(isset($_SESSION['test_variable_bx']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_memcached_session_handler fetch check phase 2';
					}
			}
			session_write_close();
			unset($_SESSION['test_variable_ax']);
			unset($_SESSION['test_variable_bx']);

			$output=$memcached_handler->get('sec_lv_encrypter_memcached_session_handler__123abc');
			if($output === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='lv_memcached_session_handler';
				$restart_test=false;
			}
			else
			{
				$lv_memcached_session_handler_encrypter=new lv_encrypter($lv_memcached_session_handler_key);
				if($lv_memcached_session_handler_encrypter->decrypt($output, false) === 'test_variable_ax|s:13:"test_value_ax";test_variable_bx|s:13:"test_value_bx";')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='lv_memcached_session_handler';
					$restart_test=false;
				}
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	ob_end_flush();

	if($restart_test)
	{
		echo ' -> Restarting test'.PHP_EOL;

		system('"'.PHP_BINARY.'" '.$argv[0].' _restart_test_', $restart_test_result);

		if($restart_test_result !== 0)
			$errors[]='restart test';
	}
	else
	{
		if(isset($redis_handler))
		{
			$redis_handler->del('sec_lv_encrypter_redis_session_handler__123abc');
			unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_redis_handler_key');
		}

		if(isset($memcached_handler))
		{
			$memcached_handler->delete('sec_lv_encrypter_memcached_session_handler__123abc');
			unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_memcached_handler_key');
		}
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>