<?php
	/*
	 * ob_cache.php library test
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
	 * Warning:
	 *  redis extension is recommended
	 */

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/ob_cache-1.txt');
		@unlink(__DIR__.'/tmp/ob_cache-2.txt');
	echo ' [ OK ]'.PHP_EOL;

	if(extension_loaded('redis'))
	{
		$_redis_host=getenv('TEST_REDIS_HOST');
		$_redis_port=getenv('TEST_REDIS_PORT');

		if($_redis_host === false)
			$_redis_host='127.0.0.1';
		if($_redis_port === false)
			$_redis_port=6379;

		try {
			$ob_redis_cache=new Redis();

			if($ob_redis_cache->connect($_redis_host, $_redis_port))
			{
				echo ' -> Removing Redis records';
					$ob_redis_cache->del('ob_cache_test_cache_1');
					$ob_redis_cache->del('ob_cache_test_cache_2');
				echo ' [ OK ]'.PHP_EOL;
			}

			unset($ob_redis_cache);
		} catch(Throwable $error) {}

		unset($_redis_host);
		unset($_redis_port);
	}

	$_SERVER['HTTP_ACCEPT_ENCODING']='';
	$errors=[];

	echo ' -> Testing ob_file_cache'.PHP_EOL;
	echo '  -> permanent cache';
		ob_start();
		ob_file_cache(__DIR__.'/tmp/ob_cache-1.txt', 0);
		echo 'good value';
		@ob_end_clean();
		@ob_end_clean();

		if(file_get_contents(__DIR__.'/tmp/ob_cache-1.txt') === 'good value')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='ob_file_cache permanent cache failed';
		}
	echo '  -> temporary cache';
		file_put_contents(__DIR__.'/tmp/ob_cache-2.txt', '');
		sleep(4);

		ob_start();
		ob_file_cache(__DIR__.'/tmp/ob_cache-2.txt', 1);
		echo 'new value';
		@ob_end_clean();

		if(file_get_contents(__DIR__.'/tmp/ob_cache-2.txt') === 'new value')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='ob_file_cache temporary cache failed';
		}

	echo ' -> Testing ob_redis_cache'.PHP_EOL;
		if(extension_loaded('redis'))
		{
			$_redis_host=getenv('TEST_REDIS_HOST');
			$_redis_port=getenv('TEST_REDIS_PORT');

			if($_redis_host === false)
				$_redis_host='127.0.0.1';
			if($_redis_port === false)
				$_redis_port=6379;

			try {
				echo '  -> permanent cache';

				$ob_redis_cache=new Redis();
				$ob_redis_cache->connect($_redis_host, $_redis_port);

				ob_start();
				ob_redis_cache($ob_redis_cache, 'cache_1', 0, false, 'ob_cache_test_');
				echo 'good value';
				ob_end_clean();

				if($ob_redis_cache->get('ob_cache_test_cache_1') === 'good value')
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

				$ob_redis_cache=new Redis();
				$ob_redis_cache->connect($_redis_host, $_redis_port);

				ob_start();
				ob_redis_cache($ob_redis_cache, 'cache_2', 1, false, 'ob_cache_test_');
				echo 'good value';
				ob_end_clean();

				sleep(4);

				ob_start();
				ob_redis_cache($ob_redis_cache, 'cache_2', 0, false, 'ob_cache_test_');
				echo 'new value';
				ob_end_clean();

				if($ob_redis_cache->get('ob_cache_test_cache_2') === 'new value')
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
			if(@$argv[1] !== '--no-redis-clean')
				try {
					echo ' -> Removing Redis records';
						$ob_redis_cache=new Redis();
						$ob_redis_cache->connect($_redis_host, $_redis_port);

						$ob_redis_cache->del('ob_cache_test_cache_1');
						$ob_redis_cache->del('ob_cache_test_cache_2');
					echo ' [ OK ]'.PHP_EOL;
				} catch(Throwable $error) {}
		}
		else
			echo ' <- Testing ob_redis_cache [SKIP]'.PHP_EOL;

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.PHP_EOL;

		exit(1);
	}
?>