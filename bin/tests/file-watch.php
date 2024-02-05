<?php
	/*
	 * file-watch.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  proc_* functions are required
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

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		system(
			PHP_BINARY.' '.__DIR__.'/../'.basename(__FILE__).' '
			.'"'.PHP_BINARY.' '.__DIR__.'/tmp/file-watch/process.php" '
			.__DIR__.'/tmp/file-watch/src'
		);
		exit();
	}

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../../lib/rmdir_recursive.php') === false)
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
		@rmdir_recursive(__DIR__.'/tmp/file-watch');
		mkdir(__DIR__.'/tmp/file-watch');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/file-watch/src');
		file_put_contents(
			__DIR__.'/tmp/file-watch/process.php',
			'<?php file_put_contents(__DIR__."/output.txt", "S".file_get_contents(__DIR__."/src/input.txt")."E"); ?>'
		);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', '');
		file_put_contents(__DIR__.'/tmp/file-watch/output.txt', '');
	echo ' [ OK ]'.PHP_EOL;

	$failed=0;

	echo ' -> Starting tool';
		try {
			$_serve_test_handler=_serve_test(PHP_BINARY.' '.$argv[0].' serve');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

	echo ' -> Testing output file';
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'content');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'ScontentE')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			++$failed;
		}
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'mcontent');
		sleep(3);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'SmcontentE')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			++$failed;
		}
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'mmcontent');
		sleep(3);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'SmmcontentE')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			++$failed;
		}

	if(is_resource($_serve_test_handler))
	{
		echo ' -> Stopping tool'.PHP_EOL;

		$_serve_test_handler_status=@proc_get_status($_serve_test_handler);
		if(isset($_serve_test_handler_status['pid']))
			@exec('taskkill.exe /F /T /PID '.$_serve_test_handler_status['pid'].' 2>&1');

		proc_terminate($_serve_test_handler);
		proc_close($_serve_test_handler);
	}

	if($failed === 3)
		exit(1);
?>