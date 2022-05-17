<?php
	/*
	 * ob_cache.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
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

	echo ' -> Testing ob_phpredis_cache'.PHP_EOL;
		try {
			echo '  -> permanent cache';

			$ob_phpredis_cache=new Redis();
			$ob_phpredis_cache->connect('127.0.0.1', 6379);

			ob_start();
			ob_phpredis_cache($ob_phpredis_cache, 'cache_1', 0, false, 'ob_cache_test_');
			echo 'good value';
			ob_end_clean();

			if($ob_phpredis_cache->get('ob_cache_test_cache_1') === 'good value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='ob_phpredis_cache permanent cache failed';
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='ob_phpredis_cache permanent cache: '.$error->getMessage();
		}
		try {
			echo '  -> temporary cache';

			$ob_phpredis_cache=new Redis();
			$ob_phpredis_cache->connect('127.0.0.1', 6379);

			ob_start();
			ob_phpredis_cache($ob_phpredis_cache, 'cache_2', 1, false, 'ob_cache_test_');
			echo 'good value';
			ob_end_clean();

			sleep(4);

			ob_start();
			ob_phpredis_cache($ob_phpredis_cache, 'cache_2', 0, false, 'ob_cache_test_');
			echo 'new value';
			ob_end_clean();

			if($ob_phpredis_cache->get('ob_cache_test_cache_2') === 'new value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='ob_phpredis_cache permanent cache failed';
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='ob_phpredis_cache temporary cache: '.$error->getMessage();
		}
		try {
			$ob_phpredis_cache=new Redis();
			$ob_phpredis_cache->connect('127.0.0.1', 6379);

			$ob_phpredis_cache->del('ob_cache_test_cache_1');
			$ob_phpredis_cache->del('ob_cache_test_cache_2');
		} catch(Throwable $error) {}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.PHP_EOL;

		exit(1);
	}
?>