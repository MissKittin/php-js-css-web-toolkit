<?php
	/*
	 * A simple point-to-point websocket server
	 *
	 * Warning:
	 *  only for *nix systems
	 *  check_var.php library is required
	 *  pcntl extension is required
	 *  sockets extension is required
	 *
	 * Required HTTP headers:
	 *  Connection: Upgrade
	 *  Upgrade: websocket
	 *  Sec-WebSocket-Key
	 *  Origin
	 *
	 * Note:
	 *  if the connection is broken, forked thread will be terminated
	 *  functions from the check_var.php library and load_library function
	 *   will also be available to the worker
	 *   use if(!function_exists()) instead
	 *  to run this program as a daemon, use the method
	 *   appropriate to your init, container, os or configuration
	 *
	 * Client class methods:
	 *  read() [returns string|null]
	 *   wait and read the message from the client
	 *   returns null when the socket is nonblocking and the client did not send the message
	 *  write(string_content)
	 *   send a message to the client
	 *  exit()
	 *   terminate connection and thread
	 *  get_http_header(string_header) [returns string|null]
	 *  get_all_http_headers() [returns array(header=>value)]
	 *  get_cookie(string_name, string_default_value=null) [returns string|default_value]
	 *  get_all_cookies() [returns array(name=>value)]
	 *  socket_set_block() [returns bool]
	 *   wait for the client (default)
	 *  socket_set_nonblock() [returns bool]
	 *   do not wait for the client
	 *
	 * Example functions.php:
		// Here you can include libraries

		function websockets_main($client)
		{
			// Controller

			websockets_log('Client UA: '.$client->get_http_header('User-Agent'));

			while(true)
				switch($client->read())
				{
					case 'get_time':
						websockets_log('-> get_time');

						$client->write(json_encode(
							['get_time', time()]
						));
					break;
					case 'exit':
						$client->exit();
				}
		}
		function websockets_log($message)
		{
			// This function doesn't have to be defined

			echo '['.date('Y-m-d H:i:s').'.'.gettimeofday()['usec'].'] '.$message.PHP_EOL;
		}
		function websockets_debug($message)
		{
			// This function doesn't have to be defined

			websockets_log('[D] '.$message);
		}
	 *
	 * Source:
	 *  https://medium.com/@cn007b/super-simple-php-websocket-example-ea2cd5893575
	 *  https://nomadphp.com/blog/92/build-a-chat-system-with-php-sockets-and-w3c-web-sockets-apis
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
			if(file_exists(__DIR__.'/lib/'.$library))
				include __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				include __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
	}

	final class __client
	{
		private $client;
		private $read_bytes;
		private $http_origin;
		private $debug;
		private $http_headers=[];
		private $http_cookies=[];

		public function __construct($client, $read_bytes, $http_origin, $debug)
		{
			if($this->debug && function_exists('websockets_debug'))
				websockets_debug('__construct(): connection opened');

			$this->client=$client;
			$this->read_bytes=$read_bytes;
			$this->http_origin=$http_origin;
			$this->debug=$debug;

			// init_client()

			$request=socket_read($this->client, $this->read_bytes);
			$request=explode("\n", $request);
			unset($request[0]);

			foreach($request as $header)
			{
				$strpos=strpos($header, ':');

				if($strpos !== false)
					$this->http_headers[trim(substr($header, 0, $strpos))]=trim(substr($header, $strpos+1));
			}

			foreach(['Connection', 'Upgrade', 'Sec-WebSocket-Key', 'Origin'] as $header)
				if(!isset($this->http_headers[$header]))
				{
					if(function_exists('websockets_log'))
						websockets_log('init_client(): '.$header.' header not sent');

					$this->exit();
				}

			foreach(['Connection'=>'Upgrade', 'Upgrade'=>'websocket'] as $header=>$value)
				if($this->http_headers[$header] !== $value)
				{
					if(function_exists('websockets_log'))
						websockets_log('init_client(): bad request ('.$header.' header)');

					$this->exit();
				}

			if((!empty($this->http_origin)) && (!in_array($this->http_headers['Origin'], $this->http_origin)))
			{
				if(function_exists('websockets_log'))
					websockets_log('init_client(): origin "'.$this->http_headers['Origin'].'" not allowed');

				$this->exit();
			}

			$key=base64_encode(pack('H*', sha1($this->http_headers['Sec-WebSocket-Key'].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			$headers=
				"HTTP/1.1 101 Switching Protocols\r\n"
				."Upgrade: websocket\r\n"
				."Connection: Upgrade\r\n"
				."Sec-WebSocket-Version: 13\r\n"
				."Sec-WebSocket-Accept: $key\r\n"
				."\r\n"
			;

			if(@socket_write($this->client, $headers, strlen($headers)) === false)
			{
				if(function_exists('websockets_log'))
					websockets_log('init_client(): connection lost');

				exit();
			}

			if($this->debug && function_exists('websockets_debug'))
				websockets_debug('init_client(): done');
		}

		private function unmask($text)
		{
			$length=ord($text[1])&127;

			if($length == 126)
			{
				$masks=substr($text, 4, 4);
				$data=substr($text, 8);
			}
			elseif($length == 127)
			{
				$masks=substr($text, 10, 4);
				$data=substr($text, 14);
			}
			else
			{
				$masks=substr($text, 2, 4);
				$data=substr($text, 6);
			}

			$text='';
			for($i=0; $i<strlen($data); ++$i)
				$text.=$data[$i]^$masks[$i%4];

			return $text;
		}

		public function read()
		{
			$content=socket_read($this->client, $this->read_bytes);

			if($content === false)
				return null;

			if($content === '')
			{
				if(function_exists('websockets_log'))
					websockets_log('read(): connection lost');

				exit();
			}

			return $this->unmask($content);
		}
		public function write(string $content)
		{
			if(@socket_write($this->client, chr(129).chr(strlen($content)).$content) === false)
			{
				if(function_exists('websockets_log'))
					websockets_log('write(): connection lost');

				exit();
			}
		}
		public function exit()
		{
			if($this->debug && function_exists('websockets_debug'))
				websockets_debug('exit() called');

			socket_close($this->client);
			sleep(1);
			exit();
		}
		public function get_http_header(string $header)
		{
			if(isset($this->http_headers[$header]))
				return $this->http_headers[$header];

			return null;
		}
		public function get_all_http_headers()
		{
			return $this->http_headers;
		}
		public function get_cookie(string $name, string $default_value=null)
		{
			if(!empty($this->http_cookies))
			{
				if(isset($this->http_cookies[$name]))
					return $this->http_cookies[$name];

				return $default_value;
			}

			get_all_cookies();

			if(isset($this->http_cookies[$name]))
				return $this->http_cookies[$name];

			return $default_value;
		}
		public function get_all_cookies()
		{
			if(empty($this->http_cookies) && isset($this->http_headers['Cookie']))
				foreach(explode(';', $this->http_headers['Cookie']) as $cookie)
				{
					$strpos=strpos($cookie, '=');
					$this->http_cookies[trim(substr($cookie, 0, $strpos))]=substr($cookie, $strpos+1);
				}

			return $this->http_cookies;
		}
		public function socket_set_block()
		{
			return socket_set_block($this->client);
		}
		public function socket_set_nonblock()
		{
			return socket_set_nonblock($this->client);
		}
	}

	if(!extension_loaded('pcntl'))
	{
		echo 'pcntl extension is not loaded'.PHP_EOL;
		exit(1);
	}
	if(!extension_loaded('sockets'))
	{
		echo 'sockets extension is not loaded'.PHP_EOL;
		exit(1);
	}

	try {
		load_library(['check_var.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$__ws_functions=check_argv_next_param('--functions');

	$__ws_ip=check_argv_next_param('--ip');
	if($__ws_ip === null)
		$__ws_ip='127.0.0.1';

	$__ws_port=check_argv_next_param('--port');
	if($__ws_port === null)
		$__ws_port='8081';

	$__ws_read_bytes=check_argv_next_param('--read');
	if($__ws_read_bytes === null)
		$__ws_read_bytes=5000;

	$__ws_http_origin=check_argv_next_param_many('--origin');
	if($__ws_http_origin === null)
		$__ws_http_origin=[];

	$__ws_children_limit=check_argv_param('--children-limit');
	if($__ws_children_limit === null)
		$__ws_children_limit=0;
	else
		$__ws_children_limit=(int)$__ws_children_limit;

	$__debug=false;
	if(check_argv('--debug'))
		$__debug=true;

	if(
		($__ws_functions === null) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Usage: --functions ./path/to/functions.php [--ip 127.0.0.1] [--port 8081] [--read 5000] [--origin http://example.com:8080] [--origin http://secondwebsite.com] [--children-limit=4] [--debug]'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --read -> bytes from client'.PHP_EOL;
		echo ' --origin -> add to the whitelist'.PHP_EOL;
		echo '  note: if the argument is absent, any address is allowed'.PHP_EOL;
		echo ' --children-limit=<positive-int> -> limit background processes (default: unlimited)'.PHP_EOL;
		echo ' --debug -> send more messages to the websockets_log()'.PHP_EOL;
		echo PHP_EOL;
		echo 'For more info see this file'.PHP_EOL;
		exit(1);
	}

	if($__ws_children_limit < 0)
	{
		echo 'Child process limit cannot be negative'.PHP_EOL;
		exit(1);
	}

	if(!file_exists($__ws_functions))
	{
		echo $__ws_functions.' not exist'.PHP_EOL;
		exit(1);
	}

	if((include $__ws_functions) === false)
	{
		echo $__ws_functions.' inclusion error'.PHP_EOL;
		exit(1);
	}

	if(!function_exists('websockets_main'))
	{
		echo 'websockets_main function not defined in '.$__ws_functions.PHP_EOL;
		exit(1);
	}

	$GLOBALS['__ws_children_pids']=[];
	declare(ticks=1);
	pcntl_signal(SIGCHLD, function($signal){
		if($signal === SIGCHLD)
			foreach($GLOBALS['__ws_children_pids'] as $pid)
				if(pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED) !== 0)
					unset($GLOBALS['__ws_children_pids'][$pid]);
	});

	$__ws_server=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if($__ws_server === false)
	{
		echo 'socket_create() error: '.socket_strerror(socket_last_error()).PHP_EOL;
		exit(1);
	}

	socket_set_option($__ws_server, SOL_SOCKET, SO_REUSEADDR, 1);
	if(@socket_bind($__ws_server, $__ws_ip, $__ws_port) === false)
	{
		echo 'socket_bind() error: '.socket_strerror(socket_last_error()).PHP_EOL;
		exit(1);
	}
	socket_listen($__ws_server);
	socket_set_block($__ws_server);

	if($__debug && function_exists('websockets_debug'))
		websockets_debug('websockets.php starting');

	while(true)
	{
		$__ws_client=socket_accept($__ws_server);

		if(is_resource($__ws_client))
		{
			if(
				($__ws_children_limit !== 0) &&
				(count($GLOBALS['__ws_children_pids']) === $__ws_children_limit)
			)
			{
				if($__debug && function_exists('websockets_debug'))
					websockets_debug('Child process limit ('.$__ws_children_limit.') reached - connection rejected');

				socket_close($__ws_client);
			}
			else
			{
				$__child_pid=pcntl_fork();

				if($__child_pid === -1)
				{
					socket_close($__ws_client);

					if(function_exists('websockets_log'))
						websockets_log('Fork error - connection rejected');
				}
				else if($__child_pid === 0)
				{
					websockets_main(new __client($__ws_client, $__ws_read_bytes, $__ws_http_origin, $__debug));

					if($__debug && function_exists('websockets_debug'))
						websockets_debug('websockets_main ended');

					socket_close($__ws_client);
					sleep(1);
					exit();
				}
				else
					$GLOBALS['__ws_children_pids'][$__child_pid]=$__child_pid;
			}
		}

		if(empty($GLOBALS['__ws_children_pids']))
		{
			socket_set_block($__ws_server);

			if($__debug && function_exists('websockets_debug'))
				websockets_debug('No clients connected - blocking mode enabled');
		}
		else
		{
			socket_set_nonblock($__ws_server);

			if($__debug && function_exists('websockets_debug'))
				websockets_debug('Background processes are running - blocking mode disabled');

			usleep(500000); // 0.5s
		}
	}
?>