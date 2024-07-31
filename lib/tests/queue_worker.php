<?php
	/*
	 * queue_worker.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *  run one with the serve-fifo and second with the serve-redis argument to start the queue servers manually
	 *   and run with argument noautoserve to use it
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
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	$_serve_test_handler_fifo=null;
	$_serve_test_handler_redis=null;
	function _serve_test($command)
	{
		if(!function_exists('proc_open'))
			throw new Exception('proc_open function is not available');

		$process_pipes=null;
		$process_handler=proc_open(
			$command,
			[
				0=>['pipe', 'r'],
				1=>['pipe', 'w'],
				2=>['pipe', 'w']
			],
			$process_pipes,
			getcwd(),
			getenv()
		);

		sleep(1);

		if(!is_resource($process_handler))
			throw new Exception('Process cannot be started');

		foreach($process_pipes as $pipe)
			fclose($pipe);

		return $process_handler;
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

		if(isset($redis_handler))
		{
			$_iterator=null;

			do
			{
				try {
					$_keys=$redis_handler->scan($_iterator, 'queue_worker_test__');
				} catch(Throwable $_error) {
					$_keys=false;
				}

				if($_keys === false)
					break;

				foreach($_keys as $_key)
					$redis_handler->del($_key);
			}
			while($_iterator > 0);
		}
	}

	if(isset($argv[1]))
		switch($argv[1])
		{
			case 'serve-fifo':
				echo ' -> Removing temporary files';
					rmdir_recursive(__DIR__.'/tmp/queue_worker/fifo');
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Creating worker test directory';
					@mkdir(__DIR__.'/tmp');
					@mkdir(__DIR__.'/tmp/queue_worker');
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

				echo ' -> Creating worker test directory';
					@mkdir(__DIR__.'/tmp');
					@mkdir(__DIR__.'/tmp/queue_worker');
					mkdir(__DIR__.'/tmp/queue_worker/redis');
					file_put_contents(__DIR__.'/tmp/queue_worker/redis/functions.php', ''
					.	'<?php '
					.		'function queue_worker_main($input_data, $worker_meta)'
					.		'{'
					.			'$worker_meta_x["worker_fifo"]=null;'
					.			'$worker_meta=array_merge($worker_meta_x, $worker_meta);'
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
						$redis_handler,
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
		}

	if(isset($argv[1]) && ($argv[1] === 'noautoserve'))
	{
		if(!file_exists(__DIR__.'/tmp/queue_worker'))
		{
			echo 'Run tests/'.basename(__FILE__).' serve-fifo'.PHP_EOL;
			echo '!!! AND !!!'.PHP_EOL;
			echo 'Run tests/'.basename(__FILE__).' serve-redis'.PHP_EOL;
			exit(1);
		}
	}
	else
	{
		try {
			echo ' -> Starting test fifo server';
			$_serve_test_handler_fifo=_serve_test('"'.PHP_BINARY.'" "'.$argv[0].'" serve-fifo');
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
				$_serve_test_handler_redis=_serve_test('"'.PHP_BINARY.'" "'.$argv[0].'" serve-redis');
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
	}

	echo ' -> Waiting'.PHP_EOL;
		sleep(2);

	$failed=false;

	echo ' -> Testing queue_worker_fifo write';
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
				echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/output invalid md5 sum'.PHP_EOL;
				$failed=true;
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/fifo/output does not exists'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing queue_worker_redis write';
		if(getenv('TEST_REDIS') === 'yes')
		{
			try {
				(new queue_worker_redis($redis_handler, 'queue_worker_test__'))->write([
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
					echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/output invalid md5 sum'.PHP_EOL;
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

	foreach([$_serve_test_handler_fifo, $_serve_test_handler_redis] as $_serve_test_handler_i=>$_serve_test_handler)
		if(is_resource($_serve_test_handler))
		{
			echo ' -> Stopping test server '.$_serve_test_handler_i.PHP_EOL;

			$_serve_test_handler_status=@proc_get_status($_serve_test_handler);

			if(isset($_serve_test_handler_status['pid']))
			{
				if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					@exec('taskkill.exe /F /T /PID '.$_serve_test_handler_status['pid'].' 2>&1');
				else
				{
					$_ch_pid=$_serve_test_handler_status['pid'];
					$_ch_pid_ex=$_ch_pid;

					while(($_ch_pid_ex !== null) && ($_ch_pid_ex !== ''))
					{
						$_ch_pid=$_ch_pid_ex;
						$_ch_pid_ex=@shell_exec('pgrep -P '.$_ch_pid);
					}

					if($_ch_pid === $_serve_test_handler_status['pid'])
						proc_terminate($_serve_test_handler);
					else
						@exec('kill '.rtrim($_ch_pid).' 2>&1');
				}
			}

			proc_close($_serve_test_handler);
		}

	if($failed)
		exit(1);
?>