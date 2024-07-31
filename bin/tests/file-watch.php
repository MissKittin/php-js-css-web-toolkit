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
				1=>['file', __DIR__.'/tmp/file-watch/stdout.txt', 'a'],
				2=>['file', __DIR__.'/tmp/file-watch/stderr.txt', 'a']
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
		$extended='';

		if(isset($argv[2]) && ($argv[2] === 'extended'))
			$extended=' --extended';

		system('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'"'
			.	((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '"""'.PHP_BINARY.'"""' : str_replace(' ', '\ ', PHP_BINARY)).' '
			.	((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '"""'.__DIR__.'/tmp/file-watch/process.php'.'"""' : str_replace(' ', '\ ', __DIR__.'/tmp/file-watch/process.php')).' '
		.	'" '
		.	'"'.__DIR__.'/tmp/file-watch/src"'
		.	$extended
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
		file_put_contents(__DIR__.'/tmp/file-watch/process.php', ''
		.	'<?php '
		.		'file_put_contents(__DIR__."/output.txt", "S".file_get_contents(__DIR__."/src/input.txt")."E");'
		.		'if(file_exists(__DIR__."/src/input2.txt"))'
		.			'file_put_contents(__DIR__."/output.txt", "X".file_get_contents(__DIR__."/src/input2.txt")."Y", FILE_APPEND);'
		.	' ?>'
		);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', '');
		file_put_contents(__DIR__.'/tmp/file-watch/output.txt', '');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Starting tool (standard)';
		try {
			$_serve_test_handler=_serve_test('"'.PHP_BINARY.'" "'.$argv[0].'" serve');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

	echo ' -> Testing output file';
		sleep(1);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'content');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'ScontentE')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		sleep(1);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'mcontent');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'SmcontentE')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		sleep(1);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'mmcontent');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'SmmcontentE')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if(is_resource($_serve_test_handler))
	{
		echo ' -> Stopping tool (standard)'.PHP_EOL;

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

	echo ' -> Starting tool (extended)';
		try {
			$_serve_test_handler=_serve_test('"'.PHP_BINARY.'" "'.$argv[0].'" serve extended');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

	echo ' -> Testing output file';
		sleep(1);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'content');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'ScontentE')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		sleep(1);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input.txt', 'mcontent');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'SmcontentE')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		sleep(1);
		file_put_contents(__DIR__.'/tmp/file-watch/src/input2.txt', 'mmcontent');
		sleep(1);
		if(file_get_contents(__DIR__.'/tmp/file-watch/output.txt') === 'SmcontentEXmmcontentY')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if(is_resource($_serve_test_handler))
	{
		echo ' -> Stopping tool (extended)'.PHP_EOL;

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