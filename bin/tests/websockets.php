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
	function hybi10_encode($payload, $type='text', $masked=true)
	{
		// source: https://github.com/varspool/php-websocket/blob/master/client/lib/class.websocket_client.php

		$frame_head=[];
		$frame='';
		$payload_length=strlen($payload);

		switch($type)
		{
			case 'text':
				$frame_head[0]=129;
			break;
			case 'close':
				$frame_head[0]=136;
			break;
			case 'ping':
				$frame_head[0]=137;
			break;
			case 'pong':
				$frame_head[0]=138;
		}

		if($payload_length > 65535)
		{
			$payload_length_bin=str_split(sprintf('%064b', $payload_length), 8);
			$frame_head[1]=127;

			if($masked === true)
				$frame_head[1]=255;

			for($i=0; $i<8; ++$i)
				$frame_head[$i+2]=bindec($payload_length_bin[$i]);

			if($frame_head[2] > 127)
				return false;
		}
		else if($payload_length > 125)
		{
			$payload_length_bin=str_split(sprintf('%016b', $payload_length), 8);
			$frame_head[1]=126;
			$frame_head[2]=bindec($payload_length_bin[0]);
			$frame_head[3]=bindec($payload_length_bin[1]);

			if($masked === true)
				$frame_head[1]=254;
		}
		else
		{
			$frame_head[1]=$payload_length;

			if($masked === true)
				$frame_head[1]+=128;
		}

		foreach(array_keys($frame_head) as $i)
			$frame_head[$i]=chr($frame_head[$i]);

		if($masked === true)
		{
			$mask=[];

			for($i=0; $i<4; ++$i)
				$mask[$i]=chr(rand(0, 255));

			$frame_head=array_merge($frame_head, $mask);
		}

		$frame=implode('', $frame_head);

		for($i=0; $i<$payload_length; ++$i)
			if($masked === true)
				$frame.=$payload[$i]^$mask[$i%4];
			else
				$frame.=$payload[$i];

		return $frame;
	}

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		system('"'.PHP_BINARY.'" '
		.	'"'.__DIR__.'/../serve.php"'.' '
		.	'--docroot "'.__DIR__.'/tmp/websockets" '
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
		file_put_contents(__DIR__.'/tmp/websockets/functions.php', '<?php '
		.	'function websockets_main($client)'
		.	'{'
		.		'while(true)'
		.			'switch($client->read())'
		.			'{'
		.				'case "get_time":'
		.					'$client->write(json_encode('
		.						'["get_time", time()]'
		.					'));'
		.				'break;'
		.				'case "exit":'
		.					'$client->exit();'
		.			'}'
		.	'}'
		);
		file_put_contents(__DIR__.'/tmp/websockets/index.php', ''
		.	'<html>'
		.		'<head>'
		.			'<script>'
		.				'document.addEventListener("DOMContentLoaded", function(){'
		.					'var socket=new WebSocket("ws://127.0.0.1:8081");'
		.				'socket.addEventListener("open", function(){'
		.						'socket.send("get_time");'
		.					'});'
		.					'socket.addEventListener("message", function(event){'
		.						'var data=JSON.parse(event.data);'
		.						'if(data[0] === "get_time")'
		.							'document.getElementById("output").innerHTML="Current timestamp: "+data[1];'
		.						'else '
		.							'document.getElementById("output").innerHTML="WebSocket test failed - bad response";'
		.					'});'
		.				'}, false);'
		.			'</script>'
		.		'</head>'
		.		'<body><h1 id="output">WebSocket test failed</h1></body>'
		.	'</html>'
		);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	if(isset($argv[1]) && ($argv[1] === 'dotest'))
	{
		echo ' -> Starting serve tool (127.0.0.1:8080)';
			try {
				$_serve_test_handle=_serve_test('"'.PHP_BINARY.'" "'.$argv[0].'" serve');
				echo ' [ OK ]'.PHP_EOL;
			} catch(Exception $error) {
				echo ' [FAIL]'.PHP_EOL;
				echo 'Error: '.$error->getMessage().PHP_EOL;
				exit(1);
			}

		echo ' -> Starting tool (127.0.0.1:8081)'.PHP_EOL.PHP_EOL;
			system('"'.PHP_BINARY.'" '
			.	'"'.__DIR__.'/../'.basename(__FILE__).'" '
			.	'--functions "'.__DIR__.'/tmp/websockets/functions.php"'
			);

		echo PHP_EOL;
		exit();
	}

	echo ' -> Starting tool (127.0.0.1:8081)';
		try {
			$_serve_test_handle=_serve_test('"'.PHP_BINARY.'" '
			.	'"'.__DIR__.'/../'.basename(__FILE__).'" '
			.	'--functions "'.__DIR__.'/tmp/websockets/functions.php"'
			);
			echo ' [ OK ]'.PHP_EOL;
		} catch(Exception $error) {
			echo ' [FAIL]'.PHP_EOL;
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

	function do_exit($_serve_test_handle, $failed=false)
	{
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
					$_ch_pid_tokill=[];

					while(($_ch_pid_ex !== null) && ($_ch_pid_ex !== ''))
					{
						$_ch_pid=$_ch_pid_ex;
						$_ch_pid_tokill[]=$_ch_pid_ex;
						$_ch_pid_ex=@shell_exec('pgrep -P '.$_ch_pid);
					}

					if($_ch_pid === $_serve_test_handle_status['pid'])
						proc_terminate($_serve_test_handle);
					else
						foreach(array_reverse($_ch_pid_tokill) as $_ch_pid)
							exec('kill '.rtrim($_ch_pid).' 2>&1');
				}
			}

			proc_close($_serve_test_handle);
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
			do_exit($_serve_test_handle, 'socket_create() failed');
		}

		if(!socket_connect($sock, '127.0.0.1', 8081))
		{
			echo ' [FAIL]'.PHP_EOL;
			socket_close($sock);
			do_exit($_serve_test_handle, 'socket_connect() failed');
		}

		if(socket_write($sock, ''
		.	'GET / HTTP/1.1'."\r\n"
		.	'Upgrade: WebSocket'."\r\n"
		.	'Connection: Upgrade'."\r\n"
		.	'Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ=='."\r\n"
		.	'Origin: http://localhost/'."\r\n"
		.	'Host: 127.0.0.1'."\r\n"
		.	"\r\n"
		) === false){
			echo ' [FAIL]'.PHP_EOL;
			socket_close($sock);
			do_exit($_serve_test_handle, 'socket_write() headers failed');
		}

		$headers=socket_read($sock, 2000);

		if($headers === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			socket_close($sock);
			do_exit($_serve_test_handle, 'socket_read() headers failed');
		}

		if(socket_write($sock, hybi10_encode('get_time')) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			socket_close($sock);
			do_exit($_serve_test_handle, 'socket_write() get_time failed');
		}

		$current_time=time();
		$ws_data=socket_read($sock, 2000);

		if($ws_data === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			socket_close($sock);
			do_exit($_serve_test_handle, 'socket_read() ws_data failed');
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

	do_exit($_serve_test_handle, $failed);
?>