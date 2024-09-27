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
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  predis-connect.php library is required for predis
	 *  memcached extension is recommended
	 *  PDO extension is recommended
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  redis extension is recommended
	 *  get-composer.php tool is recommended for predis
	 */

	namespace Test
	{
		foreach([
			'openssl'=>'openssl_random_pseudo_bytes',
			'mbstring'=>'mb_strlen'
		] as $extension=>$function)
			if(!function_exists($function))
			{
				echo $extension.' extension is not loaded'.PHP_EOL;
				exit(1);
			}

		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		ob_start();

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
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

		echo ' -> Mocking classes and functions';
			interface SessionHandlerInterface extends \SessionHandlerInterface {}
			interface SessionIdInterface extends \SessionIdInterface {}
			class Exception extends \Exception {}
			class SessionHandler extends \SessionHandler {}
			if(class_exists('Memcached'))
			{
				class Memcached extends \Memcached {}
			}
			if(class_exists('PDO'))
			{
				class PDO extends \PDO {}
			}
			if(class_exists('Redis'))
			{
				class Redis extends \Redis {}
			}
			function setcookie(
				$name,
				$value='',
				$expires=0,
				$path='',
				$domain='',
				$secure=false,
				$httponly=false
			){
				$_COOKIE[$name]=$value;
				$GLOBALS['TEST_COOKIE_META'][$name]=[
					$name,
					$value,
					$expires,
					$path,
					$domain,
					$secure,
					$httponly
				];

				return true;
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
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
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sess_lvetest');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_session_handler_key');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_handler_key');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookies');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_large_handler_key');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookies_large');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter.sqlite3');
			@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_pdo_handler_key');

			echo ' [ OK ]'.PHP_EOL;
		}

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

						if(!in_array('mysql', PDO::getAvailableDrivers()))
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
		}
		if(isset($pdo_handler))
		{
			if(!(isset($argv[1]) && ($argv[1] === '_restart_test_')))
				$pdo_handler->exec('DROP TABLE IF EXISTS sec_lv_encrypter_pdo_session_handler');
		}
		else if(
			(!isset($pdo_handler)) &&
			class_exists('PDO') &&
			in_array('sqlite', PDO::getAvailableDrivers())
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
							$redis_handler=new \predis_phpredis_proxy(new \Predis\Client($_redis['_predis'][0]));
						else
							$redis_handler=new \predis_phpredis_proxy(new \Predis\Client($_redis['_predis'][1]));

						$redis_handler->connect();
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
			if(!class_exists('Memcached'))
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
			if(isset($argv[1]) && ($argv[1] === '_restart_test_'))
				echo ' [SKIP]'.PHP_EOL;
			else
			{
				$lv_encrypter=new lv_encrypter(lv_encrypter::generate_key());
				$secret_message=$lv_encrypter->encrypt('Secret message');
				if($lv_encrypter->decrypt($secret_message) === 'Secret message')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='lv_encrypter';
				}
			}

		echo ' -> Testing lv_cookie_encrypter';
			if(isset($argv[1]) && ($argv[1] === '_restart_test_'))
				echo ' [SKIP]'.PHP_EOL;
			else
			{
				$_COOKIE=[];
				$cookie_encrypter=new lv_cookie_encrypter(lv_encrypter::generate_key());
				$cookie_encrypter->setcookie('secretname', 'secretvalue');
				if($cookie_encrypter->getcookie('secretname') === 'secretvalue')
					echo ' [ OK ]';
				else
				{
					echo '[FAIL]';
					$errors[]='lv_cookie_encrypter test 1';
				}
				if($cookie_encrypter->getcookie('nonexistentname') === null)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo '[FAIL]'.PHP_EOL;
					$errors[]='lv_cookie_encrypter test 2';
				}
			}

		echo ' -> Testing lv_session_encrypter';
			session_save_path(__DIR__.'/tmp/sec_lv_encrypter');
			if(is_file(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_session_handler_key'))
			{
				$lv_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_session_handler_key');
				$lv_session_handler_do_save=false;
			}
			else
			{
				$lv_session_handler_key=lv_encrypter::generate_key();
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_session_handler_key', $lv_session_handler_key);
				$lv_session_handler_do_save=true;
			}
			session_set_save_handler(new lv_session_encrypter($lv_session_handler_key), true);
			session_id('lvetest');
			session_start();
			if($lv_session_handler_do_save)
			{
				echo PHP_EOL.'  -> the $_SESSION will be saved to the database [SKIP]'.PHP_EOL;

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
						$errors[]='lv_session_handler fetch check phase 1';
					}
					if(isset($_SESSION['test_variable_b']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='lv_session_handler fetch check phase 2';
					}
			}
			session_write_close();
			unset($_SESSION['test_variable_a']);
			unset($_SESSION['test_variable_b']);

		echo ' -> Testing lv_cookie_session_handler';
			$_COOKIE=[];
			if(is_file(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_handler_key'))
			{
				$lv_cookie_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_handler_key');
				$_COOKIE=unserialize(file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookies'));
				$lv_cookie_session_handler_do_save=false;
			}
			else
			{
				$lv_cookie_session_handler_key=lv_encrypter::generate_key();
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_handler_key', $lv_cookie_session_handler_key);
				$lv_cookie_session_handler_do_save=true;
			}
			lv_cookie_session_handler::register_handler(['key'=>$lv_cookie_session_handler_key]);
			lv_cookie_session_handler::session_start();
			if($lv_cookie_session_handler_do_save)
			{
				echo PHP_EOL.'  -> the $_SESSION will be saved to the database [SKIP]'.PHP_EOL;

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
						$errors[]='lv_cookie_session_handler fetch check phase 1';
					}
					if(isset($_SESSION['test_variable_b']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='lv_cookie_session_handler fetch check phase 2';
					}
			}
			session_write_close();
			unset($_SESSION['test_variable_a']);
			unset($_SESSION['test_variable_b']);
			unset($_SESSION['test_variable_large']);
			if($lv_cookie_session_handler_do_save)
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookies', serialize($_COOKIE));

		echo ' -> Testing lv_cookie_session_handler (large)';
			$_COOKIE=[];
			if(is_file(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_large_handler_key'))
			{
				$lv_cookie_large_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_large_handler_key');
				$_COOKIE=unserialize(file_get_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookies_large'));
				$lv_cookie_large_session_handler_do_save=false;
			}
			else
			{
				$lv_cookie_large_session_handler_key=lv_encrypter::generate_key();
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookie_large_handler_key', $lv_cookie_large_session_handler_key);
				$lv_cookie_large_session_handler_do_save=true;
			}
			//lv_cookie_session_handler::register_handler(['key'=>$lv_cookie_large_session_handler_key]);
			lv_cookie_session_handler::session_start();
			if($lv_cookie_large_session_handler_do_save)
			{
				echo PHP_EOL.'  -> the $_SESSION will be saved to the database [SKIP]'.PHP_EOL;

				$_SESSION['test_variable_a']='test_value_a';
				$_SESSION['test_variable_b']='test_value_b';
				$_SESSION['test_variable_large']='h97q3qwg61wxa7tqq580nm7cza376q7e9phfx0au9gbcj7nz0507th7axpynji7wy4h1aw7g5zkqu4p5f23y69fzhq6bcmcab7cp8wmjb0007zxxfg83v9ai74mnuf16php6n1jnxpgde49rg8tfp79g4xp6k39rhb4nhdn8dj340jqj1xa106r05wyh6bm284bbzr03yt9amw3qkuwa7nqeahfkvqgfkbxr2yrw5djwfqvz8qz196uzub2p8345m09i3e193t4cr2uba7e2tk7czrfukdh4jbe0v48umcrpw7gyh642g628776fqneu9k1wa3kiq9fz85had5j5hrvdba4pb8fjtd53010nbnhafbzgiu92x48m2m4mirib7taay75pm1ic2x068whg9yi6tpq17ekjd3qh8yt2nq0pmzen38zbgxacx6hyvb1185h84nuf97pqu3gkjfvgeykzm6mtpuqgpq2bcqfp0pc82pa3haanfi0zjhqhbucakvgv35ww4xdzx0uqqadpajmj9drtw6qc3zp4t2t0048846a67r2pu0uue31gu5pfi8p8zt150itkweaq6y2d9hewz6etphu941433jd0iggui7j6iy6802452n1iga01z2xm4prg0j1098p69gn0mxi40hgi0tnfpig1r3npm40rzghxn1xt9573998dvrb1965jgxgj6d4j2jk1ab725kw1zp0g5jzagdb7wz450396bhxabwkuri64r9ip205twu83z762h99f5rm7gmp8nx046vzundpeg21qzu5bny7zp5cb9kxqjvffuba4iyvr25rur6ng05z73hh8c43j27k1h24h570zv0k3xu0iaw83ci3t5y0w94mmpjnvf0rn6i7fgm3jn24i2jgafb1nm774cxixb79uzy8kquqrdek87r1ceetmv9fxnx2rkp177wv5enr7du9ui7gk9g6az8xmzuupv3eqwcbnmpgi8h818ifceutf255q5edtynvzuc632j527j1uf9vppmki4wgcrt3ijzbdcxk1pybfrmjeb421ukw8kxw5q82xt70neet86ctb27fwgk9vrnd2kjm9xjd1k3qz6edcqx64gn418zp4c283kutzepegqhzdpbnypauag5xx0m120hwhnib31v5gxaaxy9yqyg1k0p8p9cx1ujt02j15x0z685d5j9ujwkf67bfbunfnfjg64arm3h2c056ahpxvr8z14ity1u8cjtg4u7cu8fggqyw0eipa6m8jt7a6m60wun07kx83mvcfawt9f7javuet6r445gwjbth7r474z7utt7amk1rmaww4qfcydptpvm7a4646rq2cbajcnhkae5zk1xh8bqaj203i7y6u775jw0ey5pz6btevw8fkz9nfghwdia8z4ce92u002evp3e093ax9ba8m9gqxrkm7r1a19du9erwbkv3itt2mvgw6j46rb3tvuhymqciwxzm90z0cxyd5uwkeg5616fyq3m3x0d82zazvfn88m7iyqa4q8j1tv9c3pfr27qmy21z0t34qhyk13hhhu25u69x1fkn2qq5r53fb9utkevd04zhq8gjaj6qv8wthhfq7xkj7d738k6p6ne2zwmyc14vfwmhqfq0nf47n9mzvz5n4fw7pjbivc7ev2pwjehxffu1iexfyt0cxjkukfy1dc73t6h1b6jpirrcfk2bfrydii7fbxwt0mtana3dzk7fi7c7602rpjp3jt6hnrvwhrnqufbunjk5b0zhdn5bzyq999zyvby806weqbeafztm4zuxd41bi91a1hk3dnuy8i1ktgt1ek1hct13e1kn0ieh6km47ifeynhh6z46vbuyyk116z3jy8qpjvm5uykygxccukna35k4696ruku9tfmt1hbha2yhbi6pnr0qqmmq3jgyxn4n7ivj4rid2rpccm0myq25pet6yf5xx1nw77ua85umnyz4vibxf1z6ki7gz9cr8m3aww1i00zq57fbqkrbaf3yymdntj4kugf6zbehdbz2qaty510x68huad8c7vgf8y6vcy4q25hbq4qppxxjcpgjvk8b1fkbjmj5y826p238r26gy3rqa9b5590cm8adart7jfznjzk78cjxmqemkj80rxthdgw86pf611373bw7j70thfu1fndvz0v0ddxgnuavggcn2nrawt6k1gujim1dz1uqzn99c0i2fvu1r1jcjpbjbhay51gwr2hmzq21v2rcq6x9qgeh7j2bej8kezgjt4uj2adan8pcuyi6ct51g18x8fiu6j1nmhdkgjbcd10raztg3eia73aa1aaf57bi448anbh2h08ncn300u87hvqjxjc5vrh95mwednm0vjv0tbzkqefbxur6k76xne5m8v4kj5pggp637jdq23jg8ewxx1406rr6pa6fbyr3rnvrf8dx2g04u8pgr7va8gz891qyw14fcqdet8pam5tqju902cucmn349tirybh8x5v4w0h2xw878n428m8t5tdebxqufuby73xc870tf8aicdcrg8vhr28eekdu6yt6j8rky79d07t7h99r1dkuxaa06dw4gr3m46p03bj93uq98e1d0ajxtxr8cd4pb1hnwi2y6nxrra1enbkpabrhtfuj8bf6pna75remc90qcnna6v5h67hfuwbrkynn7h34ejvaweb6wedf7f9tebt3eicdp5w6diqj2f99j7ewn67gnanijrexh86tt70r4242cndc3f3cm1zp7f1vgyvwwb4q603x37rwwqtwm9n17e3tebz9crt6mi7i940nfq9wd0z0633a52jkatertm21mgk1c8xtvvtb7dfwz8dqdwqbw2d2fagv59h07qjc8d8trpn705egvv89z94bgximx8t546ugh56rau6d8hxr1m6uiidgjjiajtv8vdddg75tt48mge65pxu7fxgdyz66gbfxwtmcj4351awn4b69m0eh0n89eq20ki2ffunipq0qhkg0bd4gydbgiy7031t7deiqfg73c1vj2b85mjfmemxj71h1gvryg3dbyijtg8uy0j1qd4pry3bixekzkainv79afukpr4xq1139n9y3t0x5t3jgmk4vhwpzi8guevjeruq5bx9ktjcft2utxuek4mjyc52yf85gwwwbzzdzbf7khd8a0ar7bg0iftdmy2zxyc2nt1tq2k8xv5cxuyjaj8n8vgquwr0zgyrr71zqxm856mw95v6tdwd1r00xjfw0y7dq28dnz6hy8227n4hp1jnh028v75xj6p3nbbk47tkkpw45exqjm9i09uyxqz5nywz36ttkfakj87mjupdwmmuvw5ihr8nzc841jf4dm5jt799m572tjc4vnqc99jebt70h6dmuirmr0wa0aztzmf1faxawean2qi8nttp0qd0n2npy8qn5ar5578tg8ez81vp02ackjtf3ivf85cfeqajhri5yavhpeb94eh8c5hxd5zy71afmmxxjc2mcup6f4t730r22zjjkd5uyuha7m6ce5n4bh58g6f9t7m5tivt018yc3vw3vzryyviq9wp22bf6duzt4rznjy46y389p04i5ycmdg8dqaa4rcp1w0pud0t8p26ef1qcffpv076yrthd73havuypqwxgznyq87x36c9wzffjejq05awj4rmi2rfbfxxg779qpaknum20n9a5ghw2gc5taj0gn40xpg89zwtuaad2egrxv05ba1kk4vetraapx0czpjdj5w4y7xqvyqe9a426bx6xuwbmdzwr198ycd45vzt3fzyyxe1e0cukw7g4n1wq85d7m4qt9uzn8ftr6pa0vgt6bq3divddgb1x5i9x8u08qhideh2ijd0pyq2ajp0ruwmmbdw7kp734mrvfcc2ebidmvepbhvm7fdpvgkpp5r4njeap46vu2guwzqwce7x05x7yhxx5b74gbfetqa1ne30zrzgiqibi3ry9qga758dtmnte6mu8wvm0h3tc0kx1gm90nqefbt0vwktqykhazjb1jbe35qvud8etjwk46zxyawqd6b44cyxpzin7wdghgyuzjjwm5p6yd9nkki6gc3b5qajazy0j2mr2auzxwpjc4prc4fqp3t5y9y5a74q780ha44c16bmb42mvkqfxhqzrmwxruuznw1h0d5pwpjm2dz70avg04qdxb0dy21jb23w5mau1257n3w5ww6e34xy7h8wcicqq6vp1kzaczg139pcz56xfjk8nrgzurdfcehhgz41paxeu6q6hqnghy0agb8y3zzh0c0i36dxh5ebuavfjazgyv47q2nv5a68tj0hcrw4pfpnyk7ugihgfte180nui71f0cg9r5hncj59hpx21gym2i33gz6fhtrqvrp8pe24jqdpyqu6h12xxe3gbgmj63gbmmubvg3iqwfpgh9rp77tf7bhi6thxwdqp5ud1bd4pzcbxuvqi97yhx50mnxtmwurumgbe4jt6g6brey4fgwm9ucpijnq2p0x6hy4enxkpg5muajiveuw94vq0pr0vfuc1paiiuu2zb5bxpizcciigbh50fh3mvvz3bp3jge7z3earn17h1g07i2c4pmdvx1entqqjaqnq0gmuzwpp90cmrgrnzj4b8vjum7c3yhm3899wudwyfvi5gn6b9pgxx5rb3kbytarttj49w9h43veccqn11gbynenbcpn85mhruxqi5cp8xv1ee27upinu9iitd9f8kr0yi0gpw81imj491dg4k2rg9dnaqruywedn3e6mvdhg57rdmkkui72vtmtj704r1y1wtypc7yncjfe2cjb23tzt92q2c0e0vdkha6g937dq4umebbzk89xc7adv6irnrqf5txk87czxf40xvd130w7f7na6z04ty86v62hhm79qvyvjfxbyvx4x6f05akpv9u8f5fmb3xfud8tjhz7ybx9r0g9zh4xz2y6ac5fyhxjhcbhkz9k2cfg9eie6631jmyq8i7ruhm2qt2i6mr3kcchbu6nvmmudb4vqaeffqpmdk88bzepcwavxpptyeemb0cc0qtunnduy137d4wy7wnr6ij9c91gbnk9jvk1ghku8x53a7jb8wej28jqe605mcpzr5gc3e8gqhkh7knribmxdv2p11fqiy6ttiuezhrf1j8m273yfmwvk84ufrj7j7uamctu3g2qqm1vbexjm8k7u1ywxwvqaay0xcntd5reij0amqy430k9zbgzbfrw2n64xa3x058fdk4h1fdcj7nj112cxn0vd4d1y8h6ijy64meuvunndd2gtpnr974pd30rgfpqr86m7xdyipcenunr6m9e5gde33fyrwzjirqd1hgjjachk5iuqd312g7qa7vkiq3npin33a8zwxda6em5gni9d21wrnetm2zje6avud1quanhqa91vh5erm3erg71ed7hcg4v4k2pygxdn1j7t5yv9qbhgp93n9ifpqvyjw5jyr5h1a9yipqv8vkx4eracfgni1ihuh8fcv7ihx6rnqq4ibwn3028jhi4jqic7hdqc7yvgaxpwd3pd4pwdw962e8vhprihby4htv99jxmrmchxz91ahmnf5g3bv132vqgh2660pz06x3iqxgynai96p8aqgn89mb2jzb961y23dgheirmi2h0425aneeqf676k2544gxw4g8v4cyv501pdhzbq3y8kdbbyrk5cvamjenviadhci0f305mqaw2p19fprtjipdqhjpnj3jb04r7cthg4vz3v9yc4dhiziimf05cfap6tfipipdwbtxx9yr19e0y1pf427bgytm1g63tvdm6uec927e142e9qm2z078uaht16dmad5ihfn1w8mixre1tvvftk3nqp6mdfymeeix7wbve9u24crxyc2i5rwjwf2qxmtcquq9kt7cjafnk5igx3634j2ja29ua6576u9f47a3cx8rv5w8m589mn1zkumriz7zqfxwg9am18cvbc0up94j1ghcjcppcznnhyhwt3gyrjx53wd5bkevz58n8h1zzr3cw48n2np7f88fm59rg0uvigdm52pd1v3ztg2rgmni5htbi4ck2ucce8c5ucqg86kbqbfv5d2jh4tqmiujthkvyb2kmkz4n6azgdfjrpm898u5356ghqi3cky427wyf0f7ufuy5ay789kivbzjfkdwgbpnk605fe5w6y15pukztzypnvn3ch1km2xktw9yqeken7n3znju6duj995g2qbpj1wtqp3a7a9etb7xag3i20kew8wq8enxqg8m1nf2drar76jp7pht6xfp3xw582i2ac2gxeaw5a980uw1n9zy1tptgnjrc0jcpp589xtd0iu93yfc8t36uumw0g9tbttu6gtt6319eyrthy54k0yru1n7nd25bmb61vw6qmm16etrk5aqth1azgg1h7drn7cb11vmuqt0a0tnhcdev4uaqmwch6dejq1wgyh7c6qf2ek4064vjc2favt1ku53hu0qrwv9yc9fd3xx46ut5c2x09q0x8f6vr2hm4izftur5hb2tizm4tvmbcafdpkg9pqj77nqegh6uw7zuhpjn7w1kb4zhig472ed1u0j90jwjfh3x4q8utcrtcq3rp9dr83yaye4dpp4j4nvb1ma2hi0ddcg1itip1epndyww8cwppxcw7cx23fbbkjgwhgfehmjpuv1ahxw6gcpk9hnfbb0k277wg51n2ev3yue5mmiyfbe92jp504hwb5h703fnyzxqr861mn1cj6j6duf9prq9fyc3wb6915dbiuyica6umfwb66ccntfg3ax0afp5ejcykwnec5bw9pdmc3giwg9nxfmt2gvj77ng4kf8kwmr29hu05hqm0c24e6n2d9y2gumtge0rt1p1jpr7udbzyt6hw3tkhgci5zyreu76p3n9hupfkj3n0yihq4tk80tvkh8tf6ki7mm4bvuc4mna899mby24h4r6jjg25wzqe0hqaz2wuvnutrrpbvbcbhk6a8cmztpx2j57vjud732ruh6mq8f3reuckvc887142ic8za8q1uxiynbtetgj75rcfva3mjfctniufnzqdk624qwy2hhh5d0qtt0w56pkkcknb1zhqpqy9eymby4bwadip7jec2ugn4ttfb9vejjbjah9qcbaifxdneu1xtpcb1e0rq68d9i98uq924tvmmndm361h2i5xkanfrwmqvi21d6tc06283mbm6pu8c9vjehyiak2m0xf0jdmpyvuk7jigy3k3dqk4qk5anbm4c9h0hv5qj90xvjiw9fw0h8rcchatxz32pkhh3ibq4xua030vwweckucgg6kra6ba1tbe7kjdi4hirmdv17hg471uthq0h5va4tjjpbgrmbhtg8fx66y63ufybrupkxm9nix6wk6kgc5k78awi32cf84k7c9n02z97aact2cbxprvqnvt7xuq1ijkuvydtuc3c239n9xk4pmd3zibddf11f8jgxknvwcwf783q2yivt3wmay5xrnu2t1tiyay23b4prdd55n98d23a8kk7cfzc28yzc9e7dymg6rygdu1hezk54e5n9vf4d7kxzjnym8dq5kp5vq7c0m92mm8wim7k9mpeaj9mnhj072mg96ihdhcf8kkypx32e1869wh0f41in0029gx3f9n4gujk9qhzzdxvh32i30xan0rfzhj5zkgtvrw3w6dcpxhp95x0zgt6t8ca4hirzcp2rax65mnea9qvthjdw7gx6har7qgwz21frxpq796qjeup99m2q240u5iey1dzqtqn0bgz02q642qj65t0qqifw01mzygma757mcpa3i0jp7byka0h2vf4rqnr811xn6mxg94ux3ahjm5ti8e5p6g0hmhyh2z0hvmf876efenrzc701ey1dhcqcc1u26jzjjnm41ayi6cichtz5p3whby0pmp12r48pi1h602mkt4uce87476cx552ehzt5047f15jv0cgc25vrq76tyhnf76ku6tnea9y89vziw1ue6rnugaem8qnyfvu60gft8fk4rdbj54i02w6277jf4nay0fafwajx5h8vp68vydjawa4cf6qyd9q42gffyk7ia8v04cb64euv6eu5dty2u0zuz0xypgpnyhuyj7kz97ywt38g5apxtt26rcy1i1fkzpgxj8rc5n41erv980z9zguch8hifmrkh94brz55789pkegxnddfhie20cffdb18te8197b36gbiwwuhgkubz0p1a1002jwy7xvik9vxvgxjx2pquc7bi5p86dgzzrc2vj6icchpg64qugtun3vwz930w0c3ng4vwargwkkuav6jy8w3c0m2m0q3wtak88kpb146va4aj9dykpgeewuk33k5ujwywi0hnu22vw9b8q2gaf0ip1auvw5edu1jftkrw0m7pf57xhw2yuh8qt2dbk630y6d9evj3ua6mntk3yqnkccw52h620cqwgkdf0dkkng2qmvhiarc4bij5a01znmthwgj82zxigt2ezv76f4gerha3y322qpvph7jer8y7epjnaz1kxnkcym5rktxejy67gkebz6v4y9ay37cwu8mq6e5ip3r57t0iaeqka1v4vkj8bqmuj04144y9b5ky0pv8fmuvu4wk5xvp4engt59bh5ehd10ftvbbn5xkgamzkqfi414x1uzdrfpc1icby8kqi03kwjajx8pqepy6d49w7vuu2bd6kz9iph63a1wj68bwtrv0upkajb2az270x12b05ywddf6ki842b9g7y15eqphteb2h34gf6zzv4zy4tkz2v9jarazr1da4g4m39whgvitikehc4e766fa3w48jraj60ympgcntbdtdnhjenk4mbtu3h6rc5zenuyu3ykujcf30x9645uzd07b1w6gtgf6bba397d8bfhkxi8n0vq4a2k4wwrndzi20u3uqgvzfr5p2iimr3p3hhva4yxvr8cw138drh3zv5i391cy9kuy56giqt4dk6kjeuvjkrn6zq188ix8b667a6ukb26he4uq55y3ernngdndyfju6hh54g0gpd1eqz0e63c0nejivbu4n8r978zjjueb6kzcwqrq3cj8p2iznz1dzyk9d69edt9mmn98kvxggyxh4hy0vj677f178g58dyq2ic4uc5f1wc0nyv2u07163b5i2u720xju9pfw4r2pphgyqbnwppccibwiyj3nq59nzckux8z3k8yy1hvdwtunrnaq1cr4brrtqt7ftr8aby5m8z8rkch4jgzv7aihbez3314e2di5jdd5giifiy5k17kevqm4pyqd9uw87uviyk0bjj3a21g31mez235ityag7j03b16mqnr4uhkpm1pngn070pftcmjyikfetec69pxp8m9bchc26mxq0rw3gfaftzt8402m44qpu862j6mychy9bf9btbwh372htk8r9cdd3a04ve536g5a7tat535k8tj26ua65zbxdby507y2ajdk5t6a2cgpg2hqthxh8f2t7jfcyze1ntncancghuutaqq6fk29fnay824jvrmq4wgze2adggeun82xjkcquuzuyv72dk7n7vtwf60j270tmm7ek23kewvmdx98183ip69azjtvxxny5wk4mm3xe89ghy0nk4iy5wd25e1141pdcrcv3jf7x534g9z3909ekwavph53njrmp0arj7bu5b7r2kctuyykkjwjcz6xk5xi3x2hrbmeu8nhifcdefnn5a9npiixymc4bw3byj5wf72tagx94inn2idp15h5d4iuhrfh5gamfpc1c8k3w906mbyeu3a2z277w9htxzv3t1w57ajr4vf6tx5ucke8j8ixuzgpdhpd7703tww6t8te4603h87i'; // 10000

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
						$errors[]='lv_cookie_session_handler (large) fetch check phase 1';
					}
					if(isset($_SESSION['test_variable_b']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='lv_cookie_session_handler (large) fetch check phase 2';
					}
					if(isset($_SESSION['test_variable_large']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='lv_cookie_session_handler (large) fetch check phase 3';
					}
			}
			session_write_close();
			unset($_SESSION['test_variable_a']);
			unset($_SESSION['test_variable_b']);
			unset($_SESSION['test_variable_large']);
			if($lv_cookie_large_session_handler_do_save)
				file_put_contents(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_cookies_large', serialize($_COOKIE));

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

			system('"'.PHP_BINARY.'" "'.$argv[0].'" _restart_test_', $restart_test_result);

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
	}
?>