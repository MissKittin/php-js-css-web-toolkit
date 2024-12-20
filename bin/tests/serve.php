<?php
	/*
	 * serve.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Hint:
	 *  you can change the default HTTP port (8080)
	 *  by setting the TEST_HTTP_PORT environment variable
	 *
	 * Warning:
	 *  curl_file_updown.php library is required
	 *  rmdir_recursive.php library is required
	 *  proc_* functions are required
	 */

	$_serve_test_handle=null;
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

	$http_server_port='8080';

	if(getenv('TEST_HTTP_PORT') !== false)
	{
		$http_server_port=getenv('TEST_HTTP_PORT');
		echo ' -> Using TEST_HTTP_PORT="'.$http_server_port.' as HTTP server port'.PHP_EOL;
	}

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		system('"'.PHP_BINARY.'" '
		.	'"'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--port '.$http_server_port.' '
		.	'--docroot "'.__DIR__.'/tmp/serve"'
		);
		exit();
	}

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	foreach([
		'curl_file_updown.php',
		'rmdir_recursive.php'
	] as $library){
		echo ' -> Including '.$library;
			if(is_file(__DIR__.'/../../lib/'.$library))
			{
				if(@(include __DIR__.'/../../lib/'.$library) === false)
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
	}

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@rmdir_recursive(__DIR__.'/tmp/serve');
		mkdir(__DIR__.'/tmp/serve');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		file_put_contents(
			__DIR__.'/tmp/serve/index.php',
			'<?php echo "OK"; ?>'
		);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Starting tool';
		try {
			$_serve_test_handle=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

	echo ' -> Testing output file';
		curl_file_download(
			'http://127.0.0.1:8080',
			__DIR__.'/tmp/serve/output.txt'
		);
		if(
			file_exists(__DIR__.'/tmp/serve/output.txt') &&
			(file_get_contents(__DIR__.'/tmp/serve/output.txt') === 'OK')
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if(is_resource($_serve_test_handle))
	{
		echo ' -> Stopping tool'.PHP_EOL;

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