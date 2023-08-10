<?php
	/*
	 * sec_lv_encrypter.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
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
	 *  openssl extension is required
	 *  mbstring extensions is required
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 */

	foreach(['openssl', 'mbstring', 'PDO', 'pdo_sqlite'] as $extension)
		if(!extension_loaded($extension))
		{
			echo $extension.' extension is not loaded'.PHP_EOL;
			exit(1);
		}

	ob_start();

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			ob_end_flush();
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	@mkdir(__DIR__.'/tmp');

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
	}
	if(!isset($pdo_handler))
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/sec_lv_encrypter.sqlite3');

	$restart_test=false;
	$errors=[];

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/sec_lv_encrypter.sqlite3');
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
		if(is_file(__DIR__.'/tmp/sec_lv_encrypter_pdo_handler_key'))
		{
			$lv_pdo_session_handler_key=file_get_contents(__DIR__.'/tmp/sec_lv_encrypter_pdo_handler_key');
			$lv_pdo_session_handler_do_save=false;
		}
		else
		{
			$lv_pdo_session_handler_key=lv_encrypter::generate_key();
			file_put_contents(__DIR__.'/tmp/sec_lv_encrypter_pdo_handler_key', $lv_pdo_session_handler_key);
			$lv_pdo_session_handler_do_save=true;
		}

		session_set_save_handler(new lv_pdo_session_handler([
			'key'=>$lv_pdo_session_handler_key,
			'pdo_handler'=>$pdo_handler,
			'table_name'=>'lv_pdo_session_handler'
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

		$output=$pdo_handler->query('SELECT * FROM lv_pdo_session_handler')->fetchAll();
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

	ob_end_flush();

	if($restart_test)
	{
		echo ' -> Restarting test'.PHP_EOL;

		system(PHP_BINARY.' '.$argv[0], $restart_test_result);

		if($restart_test_result !== 0)
			$errors[]='restart test';
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>