<?php
	/*
	 * queue_worker.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *  run
	 *   first terminal with the serve-fifo
	 *   second terminal with serve-pdo
	 *   third terminal with serve-redis
	 *   fourth terminal with serve-file
	 *   argument to start the queue servers manually
	 *   and run with argument noautoserve to use it, e.g:
		php queue_worker.php serve-fifo &
		php queue_worker.php serve-pdo &
		TEST_REDIS=yes php queue_worker.php serve-redis &
		php queue_worker.php serve-file &
		php queue_worker.php noautoserve
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
	 *  rmdir_recursive.php library is required
	 *  PDO extension is recommended
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 */

	$_serve_test_handle_fifo=null;
	$_serve_test_handle_redis=null;
	function _serve_test($command)
	{
		if(!function_exists('proc_open'))
			throw new Exception('proc_open function is not available');

		$process_pipes=null;
		$process_handle=proc_open(
			$command,
			[
				0=>['pipe', 'r'],
				1=>['pipe', 'w'],
				2=>['pipe', 'w']
				//1=>['file', 'stdout.txt', 'a'],
				//2=>['file', 'stderr.txt', 'a']
			],
			$process_pipes,
			getcwd(),
			getenv()
		);

		sleep(1);

		if(!is_resource($process_handle))
			throw new Exception('Process cannot be started');

		foreach($process_pipes as $pipe)
			fclose($pipe);

		return $process_handle;
	}

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../lib/rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../rmdir_recursive.php') === false)
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

	echo ' -> Creating worker test directory [1]';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/queue_worker');
		@mkdir(__DIR__.'/tmp/queue_worker/pdo');
	echo ' [ OK ]'.PHP_EOL;

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
					.	__DIR__.'/../../bin/get-composer.php '
					.	__DIR__.'/tmp/.composer'
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
					system('"'.PHP_BINARY.'" '.$_composer_binary.' '
					.	'--no-cache '
					.	'--working-dir='.__DIR__.'/tmp/.composer '
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
		{
			$_iterator=null;

			do
			{
				try {
					$_keys=$redis_handle->scan($_iterator, 'queue_worker_test__');
				} catch(Throwable $_error) {
					$_keys=false;
				}

				if($_keys === false)
					break;

				foreach($_keys as $_key)
					$redis_handle->del($_key);
			}
			while($_iterator > 0);
		}
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
	}
	if(
		(!isset($pdo_handle)) &&
		class_exists('PDO') &&
		in_array('sqlite', PDO::getAvailableDrivers())
	)
		$pdo_handle=new PDO('sqlite:'.__DIR__.'/tmp/queue_worker/pdo/queue_worker.sqlite3');

	if(isset($argv[1]))
		switch($argv[1])
		{
			case 'serve-fifo':
				echo ' -> Removing temporary files';
					rmdir_recursive(__DIR__.'/tmp/queue_worker/fifo');
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Creating worker test directory [2]';
					mkdir(__DIR__.'/tmp/queue_worker/fifo');
					file_put_contents(__DIR__.'/tmp/queue_worker/fifo/functions.php', ''
					.	'<?php '
					.		'function queue_worker_main($input_data, $worker_meta)'
					.		'{'
					.			'$worker_meta["worker_fifo"]=null;'
					.			'file_put_contents('
					.				'__DIR__."/output-raw",'
					.				'var_export($input_data, true).var_export($worker_meta, true));'
					.			'file_put_contents('
					.				'__DIR__."/output",'
					.				'md5(var_export($input_data, true).var_export($worker_meta, true)));'
					.		'} '
					.	'?>'
					);
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Starting queue worker fifo...'.PHP_EOL.PHP_EOL;
					try {
						queue_worker_fifo::start_worker(
							__DIR__.'/tmp/queue_worker/fifo/fifo',
							__DIR__.'/tmp/queue_worker/fifo/functions.php',
							false,
							1,
							false,
							false
						);
					} catch(Throwable $error) {
						echo 'Error: '.$error->getMessage().PHP_EOL;
						exit(1);
					}

				exit();
			case 'serve-pdo':
				if(!isset($pdo_handle))
				{
					echo 'PDO handle is not configured - set enviroment variables'.PHP_EOL;
					exit(1);
				}

				$pdo_handle->exec('DROP TABLE IF EXISTS queue_worker_test');

				echo ' -> Removing temporary files';
					@unlink(__DIR__.'/tmp/queue_worker/pdo/functions.php');
					@unlink(__DIR__.'/tmp/queue_worker/pdo/output');
					@unlink(__DIR__.'/tmp/queue_worker/pdo/output-raw');
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Creating worker test directory [2]';
					file_put_contents(__DIR__.'/tmp/queue_worker/pdo/functions.php', ''
					.	'<?php '
					.		'function queue_worker_main($input_data, $worker_meta)'
					.		'{'
					.			'$worker_meta_x["worker_fifo"]=null;'
					.			'$worker_meta=array_merge($worker_meta_x, $worker_meta);'
					.			'unset($worker_meta["pdo_handle"]);'
					.			'unset($worker_meta["table_name"]);'
					.			'file_put_contents('
					.				'__DIR__."/output-raw",'
					.				'var_export($input_data, true).var_export($worker_meta, true));'
					.			'file_put_contents('
					.				'__DIR__."/output",'
					.				'md5(var_export($input_data, true).var_export($worker_meta, true)));'
					.		'} '
					.	'?>'
					);
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Starting queue worker PDO...'.PHP_EOL.PHP_EOL;
					try {
						queue_worker_pdo::start_worker(
							$pdo_handle,
							__DIR__.'/tmp/queue_worker/pdo/functions.php',
							'queue_worker_test',
							false,
							1,
							false
						);
					} catch(Throwable $error) {
						echo 'Error: '.$error->getMessage().PHP_EOL;
						exit(1);
					}

				exit();
			case 'serve-redis':
				if(getenv('TEST_REDIS') !== 'yes')
				{
					echo 'TEST_REDIS=yes env variable is not set'.PHP_EOL;
					exit(1);
				}

				echo ' -> Removing temporary files';
					rmdir_recursive(__DIR__.'/tmp/queue_worker/redis');
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Creating worker test directory [2]';
					mkdir(__DIR__.'/tmp/queue_worker/redis');
					file_put_contents(__DIR__.'/tmp/queue_worker/redis/functions.php', ''
					.	'<?php '
					.		'function queue_worker_main($input_data, $worker_meta)'
					.		'{'
					.			'$worker_meta_x["worker_fifo"]=null;'
					.			'$worker_meta=array_merge($worker_meta_x, $worker_meta);'
					.			'unset($worker_meta["redis_handle"]);'
					.			'file_put_contents('
					.				'__DIR__."/output-raw",'
					.				'var_export($input_data, true).var_export($worker_meta, true));'
					.			'file_put_contents('
					.				'__DIR__."/output",'
					.				'md5(var_export($input_data, true).var_export($worker_meta, true)));'
					.		'} '
					.	'?>'
					);
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Starting queue worker redis...'.PHP_EOL.PHP_EOL;
					try {
						queue_worker_redis::start_worker(
							$redis_handle,
							__DIR__.'/tmp/queue_worker/redis/functions.php',
							'queue_worker_test__',
							false,
							1,
							false
						);
					} catch(Throwable $error) {
						echo 'Error: '.$error->getMessage().PHP_EOL;
						exit(1);
					}

				exit();
			case 'serve-file':
				echo ' -> Removing temporary files';
					rmdir_recursive(__DIR__.'/tmp/queue_worker/file');
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Creating worker test directory [2]';
					mkdir(__DIR__.'/tmp/queue_worker/file');
					mkdir(__DIR__.'/tmp/queue_worker/file/workdir');
					file_put_contents(__DIR__.'/tmp/queue_worker/file/functions.php', ''
					.	'<?php '
					.		'function queue_worker_main($input_data, $worker_meta)'
					.		'{'
					.			'$worker_meta_x["worker_fifo"]=null;'
					.			'$worker_meta=array_merge($worker_meta_x, $worker_meta);'
					.			'unset($worker_meta["worker_dir"]);'
					.			'file_put_contents('
					.				'__DIR__."/output-raw",'
					.				'var_export($input_data, true).var_export($worker_meta, true));'
					.			'file_put_contents('
					.				'__DIR__."/output",'
					.				'md5(var_export($input_data, true).var_export($worker_meta, true)));'
					.		'} '
					.	'?>'
					);
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Starting queue worker file...'.PHP_EOL.PHP_EOL;
					try {
						queue_worker_file::start_worker(
							__DIR__.'/tmp/queue_worker/file/workdir',
							__DIR__.'/tmp/queue_worker/file/functions.php',
							false,
							1,
							false
						);
					} catch(Throwable $error) {
						echo 'Error: '.$error->getMessage().PHP_EOL;
						exit(1);
					}

				exit();
		}

	if(isset($argv[1]) && ($argv[1] === 'noautoserve'))
	{
		if(!file_exists(__DIR__.'/tmp/queue_worker'))
		{
			if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
			{
				echo 'Run tests/'.basename(__FILE__).' serve-fifo'.PHP_EOL;
				echo '!!! AND !!!'.PHP_EOL;
			}

			echo 'Run tests/'.basename(__FILE__).' serve-pdo'.PHP_EOL;
			echo '!!! AND !!!'.PHP_EOL;
			echo 'Run tests/'.basename(__FILE__).' serve-redis'.PHP_EOL;
			echo '!!! AND !!!'.PHP_EOL;
			echo 'Run tests/'.basename(__FILE__).' serve-file'.PHP_EOL;
			exit(1);
		}
	}
	else
	{
		try {
			echo ' -> Starting test fifo server';

			if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				echo ' [SKIP]'.PHP_EOL;
			else
			{
				$_serve_test_handle_fifo=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve-fifo');
				echo ' [ OK ]'.PHP_EOL;
			}
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			echo 'Use tests/'.basename(__FILE__).' serve'.PHP_EOL;
			echo ' and run tests/'.basename(__FILE__).' noautoserve'.PHP_EOL;
			exit(1);
		}

		try {
			echo ' -> Starting test PDO server';
			$_serve_test_handle_pdo=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve-pdo');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			echo 'Use tests/'.basename(__FILE__).' serve'.PHP_EOL;
			echo ' and run tests/'.basename(__FILE__).' noautoserve'.PHP_EOL;
			exit(1);
		}

		try {
			echo ' -> Starting test redis server';

			if(getenv('TEST_REDIS') === 'yes')
			{
				$_serve_test_handle_redis=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve-redis');
				sleep(2);
				echo ' [ OK ]'.PHP_EOL;
			}
			else
				echo ' [SKIP]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			echo 'Use tests/'.basename(__FILE__).' serve'.PHP_EOL;
			echo ' and run tests/'.basename(__FILE__).' noautoserve'.PHP_EOL;
			exit(1);
		}

		try {
			echo ' -> Starting test file server';

			$_serve_test_handle_file=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve-file');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			echo 'Use tests/'.basename(__FILE__).' serve'.PHP_EOL;
			echo ' and run tests/'.basename(__FILE__).' noautoserve'.PHP_EOL;
			exit(1);
		}
	}

	echo ' -> Waiting'.PHP_EOL;
		sleep(2);

	$failed=false;

	echo ' -> Testing queue_worker_fifo write';
		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			echo ' [SKIP]'.PHP_EOL;
		else
		{
			try {
				(new queue_worker_fifo(__DIR__.'/tmp/queue_worker/fifo/fifo'))->write([
					'name'=>'John',
					'file'=>'./tmp/john',
					'mail'=>'john@example.com'
				]);
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
				$failed=true;
			}
			sleep(2);
			if(is_file(__DIR__.'/tmp/queue_worker/fifo/output'))
			{
				echo ' [ OK ]';

				if(file_get_contents(__DIR__.'/tmp/queue_worker/fifo/output') === '6e4d191c7a5e070ede846a9d91fd1dfe')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/fifo/output invalid md5 sum'.PHP_EOL;
					$failed=true;
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/fifo/output does not exists'.PHP_EOL;
				$failed=true;
			}
		}

	echo ' -> Testing queue_worker_pdo write';
		if(isset($pdo_handle))
		{
			try {
				(new queue_worker_pdo($pdo_handle, 'queue_worker_test'))->write([
					'name'=>'John',
					'file'=>'./tmp/john',
					'mail'=>'john@example.com'
				]);
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
				$failed=true;
			}
			sleep(6);
			if(is_file(__DIR__.'/tmp/queue_worker/pdo/output'))
			{
				echo ' [ OK ]';

				if(file_get_contents(__DIR__.'/tmp/queue_worker/pdo/output') === '6e4d191c7a5e070ede846a9d91fd1dfe')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/pdo/output invalid md5 sum'.PHP_EOL;
					$failed=true;
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/pdo/output does not exists'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing queue_worker_redis write';
		if(getenv('TEST_REDIS') === 'yes')
		{
			try {
				(new queue_worker_redis($redis_handle, 'queue_worker_test__'))->write([
					'name'=>'John',
					'file'=>'./tmp/john',
					'mail'=>'john@example.com'
				]);
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
				$failed=true;
			}
			sleep(6);
			if(is_file(__DIR__.'/tmp/queue_worker/redis/output'))
			{
				echo ' [ OK ]';

				if(file_get_contents(__DIR__.'/tmp/queue_worker/redis/output') === '6e4d191c7a5e070ede846a9d91fd1dfe')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/redis/output invalid md5 sum'.PHP_EOL;
					$failed=true;
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/redis/output does not exists'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing queue_worker_file write';
		try {
			(new queue_worker_file(__DIR__.'/tmp/queue_worker/file/workdir'))->write([
				'name'=>'John',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			]);
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
			$failed=true;
		}
		sleep(6);
		if(is_file(__DIR__.'/tmp/queue_worker/file/output'))
		{
			echo ' [ OK ]';

			if(file_get_contents(__DIR__.'/tmp/queue_worker/file/output') === '6e4d191c7a5e070ede846a9d91fd1dfe')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/file/output invalid md5 sum'.PHP_EOL;
				$failed=true;
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/file/output does not exists'.PHP_EOL;
			$failed=true;
		}

	foreach([
		$_serve_test_handle_fifo,
		$_serve_test_handle_pdo,
		$_serve_test_handle_redis,
		$_serve_test_handle_file
	] as $_serve_test_handle_i=>$_serve_test_handle)
		if(is_resource($_serve_test_handle))
		{
			echo ' -> Stopping test server '.$_serve_test_handle_i.PHP_EOL;

			$_serve_test_handle_status=@proc_get_status($_serve_test_handle);

			if(isset($_serve_test_handle_status['pid']))
			{
				if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					@exec('taskkill.exe /F /T /PID '.$_serve_test_handle_status['pid'].' 2>&1');
				else
				{
					$_ch_pid=$_serve_test_handle_status['pid'];
					$_ch_pid_ex=$_ch_pid;

					while(($_ch_pid_ex !== null) && ($_ch_pid_ex !== ''))
					{
						$_ch_pid=$_ch_pid_ex;
						$_ch_pid_ex=@shell_exec('pgrep -P '.$_ch_pid);
					}

					if($_ch_pid === $_serve_test_handle_status['pid'])
						proc_terminate($_serve_test_handle);
					else
						@exec('kill '.rtrim($_ch_pid).' 2>&1');
				}
			}

			proc_close($_serve_test_handle);
		}

	if($failed)
		exit(1);
?>