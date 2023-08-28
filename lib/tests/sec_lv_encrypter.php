<?php
	/*
	 * sec_lv_encrypter.php library test
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
	 *  openssl extension is required
	 *  mbstring extensions is required
	 *  PDO extension is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  redis extension is recommended
	 */

	foreach(['openssl', 'mbstring', 'PDO'] as $extension)
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

	@mkdir(__DIR__.'/tmp');
	@mkdir(__DIR__.'/tmp/sec_lv_encrypter');

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
	}
	if(!isset($pdo_handler))
	{
		if(!extension_loaded('pdo_sqlite'))
		{
			echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
			exit(1);
		}

		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter.sqlite3');
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
	}

	$restart_test=false;
	$errors=[];

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

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

	ob_end_flush();

	if($restart_test)
	{
		echo ' -> Restarting test'.PHP_EOL;

		system(PHP_BINARY.' '.$argv[0], $restart_test_result);

		if($restart_test_result !== 0)
			$errors[]='restart test';
	}
	else
		if(isset($redis_handler))
		{
			$redis_handler->del('sec_lv_encrypter_redis_session_handler__123abc');
			unlink(__DIR__.'/tmp/sec_lv_encrypter/sec_lv_encrypter_redis_handler_key');
		}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>