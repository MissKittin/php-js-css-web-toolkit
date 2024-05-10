<?php
	/*
	 * websockets.php tool test
	 *
	 * Note:
	 *  run with "dotest" argument to serve test
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  serve.php tool is required
	 *  rmdir_recursive.php library is required
	 *  sockets extension is required
	 *  proc_* functions are required
	 */

	if(!extension_loaded('sockets'))
	{
		echo 'Error: sockets extension is not loaded'.PHP_EOL;
		exit(1);
	}

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
	function hybi10_encode($payload, $type='text', $masked=true)
	{
		// source: https://github.com/varspool/php-websocket/blob/master/client/lib/class.websocket_client.php

		$frameHead=[];
		$frame='';
		$payloadLength=strlen($payload);

		switch($type)
		{
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0]=129;
			break;

			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0]=136;
			break;

			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0]=137;
			break;

			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0]=138;
			break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if($payloadLength > 65535)
		{
			$payloadLengthBin=str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1]=($masked === true) ? 255 : 127;
			for($i=0; $i < 8; $i++)
				$frameHead[$i+2]=bindec($payloadLengthBin[$i]);
			// most significant bit MUST be 0 (close connection if frame too big)
			if($frameHead[2] > 127)
				return false;
		}
		else if($payloadLength > 125)
		{
			$payloadLengthBin=str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1]=($masked === true) ? 254 : 126;
			$frameHead[2]=bindec($payloadLengthBin[0]);
			$frameHead[3]=bindec($payloadLengthBin[1]);
		}
		else
			$frameHead[1]=($masked === true) ? $payloadLength + 128 : $payloadLength;

		// convert frame-head to string:
		foreach(array_keys($frameHead) as $i)
			$frameHead[$i]=chr($frameHead[$i]);
		if($masked === true)
		{
			// generate a random mask:
			$mask=[];
			for($i=0; $i<4; $i++)
				$mask[$i]=chr(rand(0, 255));

			$frameHead=array_merge($frameHead, $mask);
		}
		$frame=implode('', $frameHead);

		// append payload to frame:
		$framePayload=[];
		for($i=0; $i<$payloadLength; $i++)
			$frame.=($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];

		return $frame;
	}

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		system(
			'"'.PHP_BINARY.'" '.__DIR__.'/../serve.php'.' '
			.'--docroot '.__DIR__.'/tmp/websockets '
		);
		exit();
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
			.					'else '
			.						'document.getElementById("output").innerHTML="WebSocket test failed - bad response";'
			.				'});'
			.			'}, false);'
			.		'</script>'
			.	'</head>'
			.	'<body><h1 id="output">WebSocket test failed</h1></body>'
			.'</html>'
		);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	if(isset($argv[1]) && ($argv[1] === 'dotest'))
	{
		echo ' -> Starting serve tool (127.0.0.1:8080)';
			try {
				$_serve_test_handler=_serve_test('"'.PHP_BINARY.'" '.$argv[0].' serve');
				echo ' [ OK ]'.PHP_EOL;
			} catch(Exception $error) {
				echo ' [FAIL]'.PHP_EOL;
				echo 'Error: '.$error->getMessage().PHP_EOL;
				exit(1);
			}

		echo ' -> Starting tool (127.0.0.1:8081)'.PHP_EOL.PHP_EOL;
			system(
				'"'.PHP_BINARY.'" '.__DIR__.'/../'.basename(__FILE__).' '
				.'--functions '.__DIR__.'/tmp/websockets/functions.php'
			);
		echo PHP_EOL;
	}
	else
	{
		echo ' -> Starting tool (127.0.0.1:8081)';
			try {
				$_serve_test_handler=_serve_test(
					'"'.PHP_BINARY.'" '.__DIR__.'/../'.basename(__FILE__).' '
					.'--functions '.__DIR__.'/tmp/websockets/functions.php'
				);
				echo ' [ OK ]'.PHP_EOL;
			} catch(Exception $error) {
				echo ' [FAIL]'.PHP_EOL;
				echo 'Error: '.$error->getMessage().PHP_EOL;
				exit(1);
			}

		function do_exit($_serve_test_handler, $failed=false)
		{
			if(is_resource($_serve_test_handler))
			{
				echo ' -> Stopping tool'.PHP_EOL;

				$_serve_test_handler_status=@proc_get_status($_serve_test_handler);
				if(isset($_serve_test_handler_status['pid']))
				{
					@exec('taskkill.exe /F /T /PID '.$_serve_test_handler_status['pid'].' 2>&1');

					$ch_pid=$_serve_test_handler_status['pid'];
					$ch_pid_ex=$ch_pid;
					$ch_pid_tokill=[];
					while(($ch_pid_ex !== null) && ($ch_pid_ex !== ''))
					{
						$ch_pid=$ch_pid_ex;
						$ch_pid_tokill[]=$ch_pid_ex;
						$ch_pid_ex=@shell_exec('pgrep -P '.$ch_pid);
					}
					if($ch_pid === $_serve_test_handler_status['pid'])
						proc_terminate($_serve_test_handler);
					else
						foreach(array_reverse($ch_pid_tokill) as $ch_pid)
							exec('kill '.rtrim($ch_pid).' 2>&1');
				}

				proc_close($_serve_test_handler);
			}

			switch($failed)
			{
				case false:
					exit();
				case true:
					exit(1);
			}

			echo PHP_EOL.PHP_EOL
			.	$failed.PHP_EOL;

			exit(1);
		}

		echo ' -> Testing tool';
			$sock=socket_create(AF_INET, SOCK_STREAM, 0);
			if($sock === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				do_exit($_serve_test_handler, 'socket_create() failed');
			}

			if(!socket_connect($sock, '127.0.0.1', 8081))
			{
				echo ' [FAIL]'.PHP_EOL;
				socket_close($sock);
				do_exit($_serve_test_handler, 'socket_connect() failed');
			}

			if(socket_write($sock, ''
			.	'GET / HTTP/1.1'."\n"
			.	'Upgrade: WebSocket'."\n"
			.	'Connection: Upgrade'."\n"
			.	'Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ=='."\n"
			.	'Origin: http://localhost/'."\n"
			.	'Host: 127.0.0.1'."\n"
			.	"\n"
			) === false){
				echo ' [FAIL]'.PHP_EOL;
				socket_close($sock);
				do_exit($_serve_test_handler, 'socket_write() headers failed');
			}

			$headers=socket_read($sock, 2000);
			if($headers === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				socket_close($sock);
				do_exit($_serve_test_handler, 'socket_read() headers failed');
			}

			if(socket_write($sock, hybi10_encode('get_time')) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				socket_close($sock);
				do_exit($_serve_test_handler, 'socket_write() get_time failed');
			}

			$current_time=time();
			$ws_data=socket_read($sock, 2000);
			if($ws_data === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				socket_close($sock);
				do_exit($_serve_test_handler, 'socket_read() ws_data failed');
			}
			socket_close($sock);

			if(md5($headers) === '777f015a23caf2f805acdb741269f46e')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(trim(substr($ws_data, 2)) == '["get_time",'.$current_time.']')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
	}

	do_exit($_serve_test_handler, $failed);
?>