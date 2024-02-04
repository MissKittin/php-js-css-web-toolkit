<?php
	/*
	 * websockets.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  serve.php tool is required
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
			PHP_BINARY.' '.__DIR__.'/../serve.php'.' '
			.'--docroot '.__DIR__.'/tmp/websockets '
		);
		exit();
	}

	if(!isset($argv[1]))
	{
		echo 'Run '.$argv[0].' dotest'.PHP_EOL;
		exit(1);
	}
	if($argv[1] !== 'dotest')
	{
		echo 'Run '.$argv[0].' dotest'.PHP_EOL;
		exit(1);
	}

	if(!is_file(__DIR__.'/../serve.php'))
	{
		echo 'Error: serve.php tool does not exist'.PHP_EOL;
		exit(1);
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
		@rmdir_recursive(__DIR__.'/tmp/websockets');
		mkdir(__DIR__.'/tmp/websockets');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		file_put_contents(
			__DIR__.'/tmp/websockets/functions.php', '<?php '
			.'function websockets_main($client)'
			.'{'
			.	'while(true)'
			.		'switch($client->read())'
			.		'{'
			.			'case "get_time":'
			.				'$client->write(json_encode('
			.					'["get_time", time()]'
			.				'));'
			.			'break;'
			.			'case "exit":'
			.				'$client->exit();'
			.		'}'
			.'}'
		);
		file_put_contents(
			__DIR__.'/tmp/websockets/index.php', ''
			.'<html>'
			.	'<head>'
			.		'<script>'
			.			'document.addEventListener("DOMContentLoaded", function(){'
			.				'var socket=new WebSocket("ws://127.0.0.1:8081");'
			.				'socket.addEventListener("open", function(){'
			.					'socket.send("get_time");'
			.				'});'
			.				'socket.addEventListener("message", function(event){'
			.					'var data=JSON.parse(event.data);'
			.					'if(data[0] === "get_time")'
			.						'document.getElementById("output").innerHTML="Current timestamp: "+data[1];'
			.					'else'
			.						'document.getElementById("output").innerHTML="WebSocket test failed - bad response";'
			.				'});'
			.			'}, false);'
			.		'</script>'
			.	'</head>'
			.	'<body><h1 id="output">WebSocket test failed</h1></body>'
			.'</html>'
		);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Starting serve tool (127.0.0.1:8080)';
		try {
			$_serve_test_handler=_serve_test(PHP_BINARY.' '.$argv[0].' serve');
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

	echo ' -> Starting tool (127.0.0.1:8081)'.PHP_EOL.PHP_EOL;
		system(
			PHP_BINARY.' '.__DIR__.'/../'.basename(__FILE__).' '
			.'--functions '.__DIR__.'/tmp/websockets/functions.php'
		);
	echo PHP_EOL;

	if(is_resource($_serve_test_handler))
	{
		echo ' -> Stopping tool'.PHP_EOL;

		$_serve_test_handler_status=@proc_get_status($_serve_test_handler);
		if(isset($_serve_test_handler_status['pid']))
			@exec('taskkill.exe /F /T /PID '.$_serve_test_handler_status['pid'].' 2>&1');

		proc_terminate($_serve_test_handler);
		proc_close($_serve_test_handler);
	}

	exit();
?>