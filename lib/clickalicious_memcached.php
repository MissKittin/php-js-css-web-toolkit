<?php
	class clickalicious_memcached_exception extends Exception {}
	class clickalicious_memcached
	{
		/*
		 * Proxy for clickalicious/memcached.php package
		 * and Memcached polyfill
		 *
		 * Warning:
		 *  CRUD operations are supported, but support for more advanced options is not guaranteed
		 *  weight option is not supported (compatibility with memcached extension)
		 *  tested only with toolkit libraries and components
		 *  clickalicious/memcached.php package is required
		 *
		 * Note:
		 *  methods get, gets and version query only the first server
		 *  throws an clickalicious_memcached_exception on error
		 *
		 * Usage:
			$memcached_handle=new clickalicious_memcached('persistentid'); // 'persistentid' is optional

			$memcached_handle->addServer(
				'127.0.0.1', // string_host
				11211, // int_port
				0 // int_weight (optional, default: 0)
			);
			// or
			$memcached_handle->addServer(
				'/var/run/memcached/memcached.sock', // string_socket_path (warning: must start with a slash)
				0, // int_port (required but ignored)
				0 // int_weight (optional, default: 0)
			);
			// or
			$memcached_handle->addServers([
				['127.0.0.1', 11211, 0], // string_host, int_port, int_weight (required)
				['127.0.0.2', 11212, 1],
				['/var/run/memcached/memcached.sock', 0, 1] // warning: string_socket_path must start with a slash
			]);

			$memcached_handle->set('key', 'value'); // bool
			$value=$memcached_handle->get('key'); // 'value'
			$memcached_handle->delete('key'); // bool
		 */

		protected $persistent_id;
		protected $primary_server=null;
		protected $servers=[];

		public function __construct(?string $persistent_id=null)
		{
			$this->persistent_id=$persistent_id;
		}
		public function __call($method, $arguments)
		{
			$return_value=null;

			if($this->primary_server === null)
				throw new clickalicious_memcached_exception(
					'No servers defined'
				);

			switch($method)
			{
				case 'setHost':
				case 'host':
				case 'getHost':
				case 'setPort':
				case 'port':
				case 'getPort':
				case 'setTimeout':
				case 'timeout':
				case 'getTimeout':
				case 'send':
				case 'stats':
					throw new clickalicious_memcached_exception(''
					.	'You cannot use '.$method.'() - '
					.	'use get_client_instance("host", port)->'.$name.'()'
					);
				case 'get':
				case 'gets':
				case 'version':
					$return_value=$this
					->	primary_server
					->	$method(...$arguments);
				break;
				default:
					foreach($this->servers as $server)
						$return_value=$server->$method(
							...$arguments
						);
			}

			return $return_value;
		}

		public function addServer(
			string $host,
			int $port,
			int $weight=0
		){
			if(!class_exists('\Clickalicious\Memcached\Client'))
				throw new clickalicious_memcached_exception(
					'clickalicious/memcached.php package is not installed'
				);

			if(substr($host, 0, 1) === '/')
			{
				$host='unix://'.$host;
				$port=-1;
			}

			if(isset($servers[$host.$port]))
				throw new clickalicious_memcached_exception(
					'Server "'.$host.':'.$port.'" is already defined'
				);

			$this->servers[$host.$port]=new Clickalicious\Memcached\Client(
				$host,
				$port,
				$this->persistent_id
			);

			$this->servers[$host.$port]->setTimeout(null); // for fsockopen()

			if($this->primary_server === null)
				$this->primary_server=$this->servers[$host.$port];

			return true;
		}
		public function addServers(array $servers)
		{
			foreach($servers as $server)
			{
				if(!isset($server[1]))
					throw new clickalicious_memcached_exception(
						'One of the arrays does not have enough parameters ["host", port, weight]'
					);

				$this->addServer(
					$server[0],
					$server[1]
				);
			}

			return true;
		}
		public function get_client_instance(string $host, int $port)
		{
			if(substr($host, 0, 1) === '/')
			{
				$host='unix://'.$host;
				$port=-1;
			}

			if(!isset($this->servers[$host.$port]))
				throw new clickalicious_memcached_exception(
					'Server "'.$host.':'.$port.'" is not defined'
				);

			return $this->servers[$host.$port];
		}
	}

	if(
		(!class_exists('Memcached')) &&
		class_exists('\Clickalicious\Memcached\Client')
	){
		class Memcached extends clickalicious_memcached {}
	}
?>