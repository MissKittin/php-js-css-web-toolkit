<?php
	/*
	 * ob_cache.php library test
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
	 * Warning:
	 *  predis-connect.php library is required for predis
	 *  memcached extension is recommended
	 *  redis extension is recommended
	 *  get-composer.php tool is recommended for predis
	 */

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
		@mkdir(__DIR__.'/tmp/ob_cache');
		@unlink(__DIR__.'/tmp/ob_cache/ob_cache-1.txt');
		@unlink(__DIR__.'/tmp/ob_cache/ob_cache-2.txt');
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

		if(isset($redis_handler))
		{
			$redis_handler->del('ob_cache_test_cache_1');
			$redis_handler->del('ob_cache_test_cache_2');
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

		if(isset($memcached_handler))
		{
			$memcached_handler->delete('ob_cache_test_cache_1');
			$memcached_handler->delete('ob_cache_test_cache_2');
		}
	}

	$_SERVER['HTTP_ACCEPT_ENCODING']='';
	$errors=[];

	echo ' -> Testing ob_file_cache'.PHP_EOL;
	echo '  -> permanent cache';
		ob_start();
		ob_file_cache(__DIR__.'/tmp/ob_cache/ob_cache-1.txt', 0);
		echo 'good value';
		@ob_end_clean();
		@ob_end_clean();

		if(file_get_contents(__DIR__.'/tmp/ob_cache/ob_cache-1.txt') === 'good value')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='ob_file_cache permanent cache failed';
		}
	echo '  -> temporary cache';
		file_put_contents(__DIR__.'/tmp/ob_cache/ob_cache-2.txt', '');
		sleep(4);

		ob_start();
		ob_file_cache(__DIR__.'/tmp/ob_cache/ob_cache-2.txt', 1);
		echo 'new value';
		@ob_end_clean();

		if(file_get_contents(__DIR__.'/tmp/ob_cache/ob_cache-2.txt') === 'new value')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='ob_file_cache temporary cache failed';
		}

	echo ' -> Testing ob_redis_cache'.PHP_EOL;
		if(isset($redis_handler))
		{
			try {
				echo '  -> permanent cache';

				ob_start();
				ob_redis_cache($redis_handler, 'cache_1', 0, false, 'ob_cache_test_');
				echo 'good value';
				ob_end_clean();

				if($redis_handler->get('ob_cache_test_cache_1') === 'good value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='ob_redis_cache permanent cache failed';
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='ob_redis_cache permanent cache: '.$error->getMessage();
			}
			try {
				echo '  -> temporary cache';

				ob_start();
				ob_redis_cache($redis_handler, 'cache_2', 1, false, 'ob_cache_test_');
				echo 'good value';
				ob_end_clean();

				sleep(4);

				ob_start();
				ob_redis_cache($redis_handler, 'cache_2', 0, false, 'ob_cache_test_');
				echo 'new value';
				ob_end_clean();

				if($redis_handler->get('ob_cache_test_cache_2') === 'new value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='ob_redis_cache permanent cache failed';
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='ob_redis_cache temporary cache: '.$error->getMessage();
			}
		}
		else
			echo ' <- Testing ob_redis_cache [SKIP]'.PHP_EOL;

	echo ' -> Testing ob_memcached_cache'.PHP_EOL;
		if(isset($memcached_handler))
		{
			try {
				echo '  -> permanent cache';

				ob_start();
				ob_memcached_cache($memcached_handler, 'cache_1', 0, false, 'ob_cache_test_');
				echo 'good value';
				ob_end_clean();

				$memcached_handler->get('ob_cache_test_cache_1');
				if($memcached_handler->get('ob_cache_test_cache_1') === 'good value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='ob_memcached_cache permanent cache failed';
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='ob_memcached_cache permanent cache: '.$error->getMessage();
			}
			try {
				echo '  -> temporary cache';

				ob_start();
				ob_memcached_cache($memcached_handler, 'cache_2', 1, false, 'ob_cache_test_');
				echo 'good value';
				ob_end_clean();

				sleep(4);

				ob_start();
				ob_memcached_cache($memcached_handler, 'cache_2', 0, false, 'ob_cache_test_');
				echo 'new value';
				ob_end_clean();

				$memcached_handler->get('ob_cache_test_cache_2');
				if($memcached_handler->get('ob_cache_test_cache_2') === 'new value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='ob_memcached_cache permanent cache failed';
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='ob_memcached_cache temporary cache: '.$error->getMessage();
			}
		}
		else
			echo ' <- Testing ob_memcached_cache [SKIP]'.PHP_EOL;

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.PHP_EOL;

		exit(1);
	}
?>