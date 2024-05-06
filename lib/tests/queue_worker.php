<?php
	/*
	 * queue_worker.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *  run with the serve argument to start the queue server manually
	 *   and run with argument noautoserve to use it
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	$_serve_test_handler=null;
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

	if(
		(isset($argv[1]) && ($argv[1] === 'noautoserve')) &&
		(!file_exists(__DIR__.'/tmp/queue_worker'))
	){
		echo 'Run tests/'.basename(__FILE__).' serve'.PHP_EOL;
		exit(1);
	}
	else
		try {
			echo ' -> Starting test server';
			$_serve_test_handler=_serve_test(PHP_BINARY.' '.$argv[0].' serve');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			echo 'Use tests/'.basename(__FILE__).' serve'.PHP_EOL;
			echo ' and run tests/'.basename(__FILE__).' noautoserve'.PHP_EOL;
			exit(1);
		}

	echo ' -> Waiting'.PHP_EOL;
		sleep(2);

	$failed=false;

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
			$failed=true;
		}
		sleep(2);
		if(is_file(__DIR__.'/tmp/queue_worker/output'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/output does not exists'.PHP_EOL;
			$failed=true;
		}
		if(file_get_contents(__DIR__.'/tmp/queue_worker/output') === '6e4d191c7a5e070ede846a9d91fd1dfe')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			echo PHP_EOL.'Error: '.__DIR__.'/tmp/queue_worker/output invalid md5 sum'.PHP_EOL;
			$failed=true;
		}

	if(is_resource($_serve_test_handler))
	{
		echo ' -> Stopping test server'.PHP_EOL;

		$_serve_test_handler_status=@proc_get_status($_serve_test_handler);
		if(isset($_serve_test_handler_status['pid']))
		{
			@exec('taskkill.exe /F /T /PID '.$_serve_test_handler_status['pid'].' 2>&1');

			$ch_pid=$_serve_test_handler_status['pid'];
			$ch_pid_ex=$ch_pid;
			while(($ch_pid_ex !== null) && ($ch_pid_ex !== ''))
			{
				$ch_pid=$ch_pid_ex;
				$ch_pid_ex=@shell_exec('pgrep -P '.$ch_pid);
			}
			if($ch_pid === $_serve_test_handler_status['pid'])
				proc_terminate($_serve_test_handler);
			else
				@exec('kill '.rtrim($ch_pid).' 2>&1');
		}

		proc_close($_serve_test_handler);
	}

	if($failed)
		exit(1);
?>