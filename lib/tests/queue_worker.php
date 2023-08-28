<?php
	/*
	 * queue_worker.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

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

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		echo ' -> Removing temporary files';
			rmdir_recursive(__DIR__.'/tmp/queue_worker');
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Creating worker test directory';
			@mkdir(__DIR__.'/tmp');
			mkdir(__DIR__.'/tmp/queue_worker');
			file_put_contents(
				__DIR__.'/tmp/queue_worker/functions.php', ''
				.'<?php '
				.	'function queue_worker_main($input_data, $worker_meta)'
				.	'{'
				.		'$worker_meta["worker_fifo"]=null;'
				.		'file_put_contents('
				.			'__DIR__."/output",'
				.			'md5(var_export($input_data, true).var_export($worker_meta, true)));'
				.	'} '
				.'?>'
			);
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Starting queue worker...'.PHP_EOL.PHP_EOL;
		try {
			queue_worker::start_worker(
				__DIR__.'/tmp/queue_worker/fifo',
				__DIR__.'/tmp/queue_worker/functions.php',
				false,
				true,
				false
			);
		} catch(Throwable $error) {
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		exit();
	}

	if(!file_exists(__DIR__.'/tmp/queue_worker'))
	{
		echo 'Run tests/queue_worker.php serve'.PHP_EOL;
		exit(1);
	}

	echo ' -> Waiting'.PHP_EOL;
		sleep(2);

	echo ' -> Testing queue_worker write';
		try {
			(new queue_worker(__DIR__.'/tmp/queue_worker/fifo'))->write([
				'name'=>'John',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			]);
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}
		sleep(2);
		if(is_file(__DIR__.'/tmp/queue_worker/output'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/output does not exists'.PHP_EOL;
			exit(1);
		}
		if(file_get_contents(__DIR__.'/tmp/queue_worker/output') === '6e4d191c7a5e070ede846a9d91fd1dfe')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/output invalid md5 sum'.PHP_EOL;
			exit(1);
		}
?>