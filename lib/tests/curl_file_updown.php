<?php
	/*
	 * curl_file_updown.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *  run with the serve argument to start the http server manually
	 *   and run with argument noautoserve to use it
	 *
	 * Hint:
	 *  you can change the default HTTP port (8080)
	 *  by setting the TEST_HTTP_PORT environment variable
	 *
	 * Warning:
	 *  curl extension is required
	 *  rmdir_recursive.php library is required
	 *  proc_* functions are recommended
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

	if(!function_exists('curl_init'))
	{
		echo 'curl extension is not loaded'.PHP_EOL;
		exit(1);
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

	$http_server_port='8080';

	if(getenv('TEST_HTTP_PORT') !== false)
	{
		$http_server_port=getenv('TEST_HTTP_PORT');
		echo ' -> Using TEST_HTTP_PORT="'.$http_server_port.' as HTTP server port'.PHP_EOL;
	}

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		echo ' -> Removing temporary files';
			rmdir_recursive(__DIR__.'/tmp/curl_file_updown');
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Creating server test directory';
			@mkdir(__DIR__.'/tmp');
			mkdir(__DIR__.'/tmp/curl_file_updown');
			mkdir(__DIR__.'/tmp/curl_file_updown/server');
			file_put_contents(__DIR__.'/tmp/curl_file_updown/server/file-to-be-downloaded.txt', 'download me');
			file_put_contents(
				__DIR__.'/tmp/curl_file_updown/server/upload.php',
				'<?php move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], __DIR__."/".$_FILES["fileToUpload"]["name"]); ?>'
			);
			file_put_contents(
				__DIR__.'/tmp/curl_file_updown/server/json-upload.php',
				'<?php header("Content-Type: application/json"); echo json_encode(["output"=>json_decode(file_get_contents("php://input"), true)["input"]]); ?>'
			);
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Starting PHP server...'.PHP_EOL.PHP_EOL;
			chdir(__DIR__.'/tmp/curl_file_updown/server');
			system('"'.PHP_BINARY.'" -S 127.0.0.1:'.$http_server_port);

		exit();
	}

	if(
		(isset($argv[1]) && ($argv[1] === 'noautoserve')) &&
		(!file_exists(__DIR__.'/tmp/curl_file_updown'))
	){
		echo 'Run tests/'.basename(__FILE__).' serve'.PHP_EOL;
		exit(1);
	}
	else
		try {
			echo ' -> Starting test server';
			$_serve_test_handle=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			echo 'Use tests/'.basename(__FILE__).' serve'.PHP_EOL;
			echo ' and run tests/'.basename(__FILE__).' noautoserve'.PHP_EOL;
			exit(1);
		}

	echo ' -> Creating client test directory';
		@mkdir(__DIR__.'/tmp/curl_file_updown/client');
		file_put_contents(__DIR__.'/tmp/curl_file_updown/client/file-to-be-uploaded.txt', 'upload me');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing http curl_file_upload';
		curl_file_upload(
			'http://127.0.0.1:'.$http_server_port.'/upload.php',
			__DIR__.'/tmp/curl_file_updown/client/file-to-be-uploaded.txt',
			['post_field_name'=>'fileToUpload']
		);
		if(
			file_exists(__DIR__.'/tmp/curl_file_updown/server/file-to-be-uploaded.txt') &&
			(file_get_contents(__DIR__.'/tmp/curl_file_updown/server/file-to-be-uploaded.txt') === 'upload me')
		){
			echo ' [ OK ]'.PHP_EOL;
			unlink(__DIR__.'/tmp/curl_file_updown/server/file-to-be-uploaded.txt');
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing http curl_file_download';
		curl_file_download(
			'http://127.0.0.1:'.$http_server_port.'/file-to-be-downloaded.txt',
			__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt'
		);
		if(
			file_exists(__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt') &&
			(file_get_contents(__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt') === 'download me')
		){
			echo ' [ OK ]'.PHP_EOL;
			unlink(__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt');
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing curl_json_upload';
		if(curl_json_upload(
			'http://127.0.0.1:'.$http_server_port.'/json-upload.php',
			json_encode(['input'=>'doing good']),
			'POST'
		)[1] === '{"output":"doing good"}')
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