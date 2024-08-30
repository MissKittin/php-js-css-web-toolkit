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
				require __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				require __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
	}

	final class _client
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

			foreach(['Connection'=>'upgrade', 'Upgrade'=>'websocket'] as $header=>$value)
				if(strtolower($this->http_headers[$header]) !== $value)
				{
					if(function_exists('websockets_log'))
						websockets_log('init_client(): bad request ('.$header.' header)');

					$this->exit();
				}

			if(
				(!empty($this->http_origin)) &&
				(!in_array(
					$this->http_headers['Origin'],
					$this->http_origin
				))
			){
				if(function_exists('websockets_log'))
					websockets_log('init_client(): origin "'.$this->http_headers['Origin'].'" not allowed');

				$this->exit();
			}

			$key=base64_encode(
				pack('H*', sha1(''
				.	$this->http_headers['Sec-WebSocket-Key']
				.	'258EAFA5-E914-47DA-95CA-C5AB0DC85B11'
				))
			);
			$headers=''
			.	"HTTP/1.1 101 Switching Protocols\r\n"
			.	"Upgrade: websocket\r\n"
			.	"Connection: Upgrade\r\n"
			.	"Sec-WebSocket-Version: 13\r\n"
			.	"Sec-WebSocket-Accept: $key\r\n"
			.	"\r\n";

			if(
				socket_write(
					$this->client,
					$headers,
					strlen($headers)
				)
				===
				false
			){
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

			switch($length)
			{
				case 126:
					$masks=substr($text, 4, 4);
					$data=substr($text, 8);
				break;
				case 127:
					$masks=substr($text, 10, 4);
					$data=substr($text, 14);
				break;
				default:
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
			if(socket_write(
				$this->client,
				chr(129).chr(strlen($content)).$content
			) === false){
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
		public function get_cookie(string $name, ?string $default_value=null)
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

	$_ws_functions=check_argv_next_param('--functions');

	$_ws_ip=check_argv_next_param('--ip');
	if($_ws_ip === null)
		$_ws_ip='127.0.0.1';

	$_ws_port=check_argv_next_param('--port');
	if($_ws_port === null)
		$_ws_port='8081';

	$_ws_uds=check_argv_next_param('--uds');

	$_ws_read_bytes=check_argv_next_param('--read');
	if($_ws_read_bytes === null)
		$_ws_read_bytes=5000;

	$_ws_http_origin=check_argv_next_param_many('--origin');
	if($_ws_http_origin === null)
		$_ws_http_origin=[];

	$_ws_children_limit=check_argv_param('--children-limit');
	if($_ws_children_limit === null)
		$_ws_children_limit=0;
	else
		$_ws_children_limit=(int)$_ws_children_limit;

	$_debug=false;
	if(check_argv('--debug'))
		$_debug=true;

	if(
		($_ws_functions === null) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Usage: --functions ./path/to/functions.php [--ip 127.0.0.1] [--port 8081] [--uds /path/to/websockets.sock] [--read 5000] [--origin http://example.com:8080] [--origin http://secondwebsite.com] [--children-limit=4] [--debug]'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --uds -> use Unix Domain Socket instead of TCP/IP'.PHP_EOL;
		echo '  note: has priority over the ip/port'.PHP_EOL;
		echo ' --read -> bytes from client'.PHP_EOL;
		echo ' --origin -> add to the whitelist'.PHP_EOL;
		echo '  note: if the argument is absent, any address is allowed'.PHP_EOL;
		echo ' --children-limit=<positive-int> -> limit background processes (default: unlimited)'.PHP_EOL;
		echo ' --debug -> send more messages to the websockets_log()'.PHP_EOL;
		echo PHP_EOL;
		echo 'For more info see this file'.PHP_EOL;
		exit(1);
	}

	if($_ws_children_limit < 0)
	{
		echo 'Child process limit cannot be negative'.PHP_EOL;
		exit(1);
	}

	if(!file_exists($_ws_functions))
	{
		echo $_ws_functions.' not exist'.PHP_EOL;
		exit(1);
	}

	require $_ws_functions;

	if(!function_exists('websockets_main'))
	{
		echo 'websockets_main function not defined in '.$_ws_functions.PHP_EOL;
		exit(1);
	}

	$GLOBALS['_ws_children_pids']=[];
	declare(ticks=1);
	pcntl_signal(SIGCHLD, function($signal){
		if($signal === SIGCHLD)
			foreach($GLOBALS['_ws_children_pids'] as $pid)
				if(pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED) !== 0)
					unset($GLOBALS['_ws_children_pids'][$pid]);
	});

	if($_ws_uds === null)
		$_ws_server=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	else
		$_ws_server=socket_create(AF_UNIX, SOCK_STREAM, 0);

	if($_ws_server === false)
	{
		echo 'socket_create() error: '.socket_strerror(socket_last_error()).PHP_EOL;
		exit(1);
	}

	socket_set_option($_ws_server, SOL_SOCKET, SO_REUSEADDR, 1);

	if($_ws_uds === null)
		$_ws_sb_result=socket_bind($_ws_server, $_ws_ip, $_ws_port);
	else
		$_ws_sb_result=socket_bind($_ws_server, $_ws_uds);

	if($_ws_sb_result === false)
	{
		echo 'socket_bind() error: '.socket_strerror(socket_last_error()).PHP_EOL;
		exit(1);
	}

	socket_listen($_ws_server);
	socket_set_block($_ws_server);

	if($_debug && function_exists('websockets_debug'))
		websockets_debug('websockets.php starting');

	while(true)
	{
		$_ws_client=socket_accept($_ws_server);

		if(is_resource($_ws_client))
		{
			if(
				($_ws_children_limit !== 0) &&
				(count($GLOBALS['_ws_children_pids']) === $_ws_children_limit)
			){
				if($_debug && function_exists('websockets_debug'))
					websockets_debug('Child process limit ('.$_ws_children_limit.') reached - connection rejected');

				socket_close($_ws_client);
			}
			else
			{
				$_child_pid=pcntl_fork();

				switch($_child_pid)
				{
					case -1:
						socket_close($_ws_client);

						if(function_exists('websockets_log'))
							websockets_log('Fork error - connection rejected');
					break;
					case 0:
						websockets_main(new _client($_ws_client, $_ws_read_bytes, $_ws_http_origin, $_debug));

						if($_debug && function_exists('websockets_debug'))
							websockets_debug('websockets_main ended');

						socket_close($_ws_client);
						sleep(1);
						exit();
					break;
					default:
						$GLOBALS['_ws_children_pids'][$_child_pid]=$_child_pid;
				}
			}
		}

		if(empty($GLOBALS['_ws_children_pids']))
		{
			socket_set_block($_ws_server);

			if($_debug && function_exists('websockets_debug'))
				websockets_debug('No clients connected - blocking mode enabled');
		}
		else
		{
			socket_set_nonblock($_ws_server);

			if($_debug && function_exists('websockets_debug'))
				websockets_debug('Background processes are running - blocking mode disabled');

			usleep(500000); // 0.5s
		}
	}
?>