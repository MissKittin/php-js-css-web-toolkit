<?php
	/*
	 * Memcached connection helper
	 *
	 * Warning:
	 *  memcached extension is required
	 *
	 * Note:
	 *  throws an memcached_connect_exception on error
	 *
	 * Functions:
		// pre-configured version
		memcached_connect('./path_to/your_memcached_config_directory')

		// portable version
		memcached_connect_array([
			'options'=>[ // optional
				'persistent_id'=>null, // string or null, all instances created with the same persistent_id will share the same connection
				'ignore_failed_servers'=>true // do not throw an exception on addServer fail
			],
			[ // server #1
				'host'=>'127.0.0.1', // or socket required
				'port'=>11211, // optional, default: 11211, ignored for socket
				'weight'=>30 // optional, default: 0
			],
			[ // server #2
				'socket'=>'/var/run/memcached/memcached.sock', // has priority over the host
				'weight'=>60 // optional, default: 0
			]
		])
	 *
	 * Most zwadzacy:
	 *  a bridge for replacing a Memcached class with another
	 *  recommended to be used with extreme caution
	 *  more info below
	 */

	class memcached_connect_exception extends Exception {}

	function memcached_connect(string $db)
	{
		/*
		 * Memcached connection helper
		 *
		 * Returns the Memcached handle
		 * For more info, see memcached_connect_array function
		 *
		 * Warning:
		 *  memcached_connect_array function is required
		 *
		 * Note:
		 *  throws an memcached_connect_exception on error
		 *
		 * Configuration:
		 *  1) create a directory for memcached config files
		 *  2) create a config.php file:
				<?php
					return [
						'options'=>[ // optional
							'persistent_id'=>null, // string or null, all instances created with the same persistent_id will share the same connection
							'ignore_failed_servers'=>true // do not throw an exception on addServer fail
						],
						[ // server #1
							'host'=>'127.0.0.1', // or socket required
							'port'=>11211, // optional, default: 11211, ignored for socket
							'weight'=>30 // optional, default: 0
						],
						[ // server #2
							'socket'=>'/var/run/memcached/memcached.sock', // has priority over the host
							'weight'=>60 // optional, default: 0
						]
					];
				?>
		 *
		 * Initialization:
			$db=memcached_connect('./path_to/your_database_config_directory');
		 */

		if(!file_exists($db.'/config.php'))
			throw new memcached_connect_exception(
				$db.'/config.php not exists'
			);

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new memcached_connect_exception(
				$db.'/config.php did not return an array'
			);

		foreach($db_config as $key=>$value)
		{
			if($key === 'options')
			{
				if(
					isset($value['persistent_id']) &&
					(
						(!is_string($value['persistent_id'])) &&
						(!is_null($value['persistent_id']))
					)
				)
					throw new memcached_connect_exception(
						'The persistent_id parameter is not a string nor null'
					);

				if(
					isset($value['ignore_failed_servers']) &&
					(!is_bool($value['ignore_failed_servers']))
				)
					throw new memcached_connect_exception(
						'The ignore_failed_servers parameter is not a boolean'
					);

				continue;
			}

			foreach([
				'host'=>'string',
				'port'=>'integer',
				'socket'=>'string',
				'weight'=>'integer'
			] as $param=>$param_type)
				if(
					isset($db_config[$key][$param]) &&
					(gettype($db_config[$key][$param]) !== $param_type)
				)
					throw new memcached_connect_exception(
						'The '.$param.' parameter is not a '.$param_type
					);
		}

		return memcached_connect_array($db_config, false);
	}
	function memcached_connect_array(
		array $servers,
		bool $type_hint=true
	){
		/*
		 * Memcached connection helper
		 * portable version
		 *
		 * Returns the Memcached handle
		 *
		 * Warning:
		 *  memcached_connect_bridge class is required
		 *  memcached extension is required
		 *
		 * Note:
		 *  throws an memcached_connect_exception on error
		 *
		 * Initialization:
			$db=memcached_connect_array([
				'options'=>[ // optional
					'persistent_id'=>null, // string or null, all instances created with the same persistent_id will share the same connection
					'ignore_failed_servers'=>true // do not throw an exception on addServer fail (default: false)
				],
				[ // server #1
					'host'=>'127.0.0.1', // or socket required
					'port'=>11211, // optional, default: 11211, ignored for socket
					'weight'=>30 // optional, default: 0
				],
				[ // server #2
					'socket'=>'/var/run/memcached/memcached.sock', // has priority over the host
					'weight'=>60 // optional, default: 0
				]
			]);
		 */

		if(!class_exists(
			memcached_connect_bridge::class_exists()
		))
			throw new memcached_connect_exception(
				'memcached extension is not loaded'
			);

		if($type_hint)
			foreach($servers as $key=>$value)
			{
				if($key === 'options')
				{
					if(
						isset($value['persistent_id']) &&
						(
							(!is_string($value['persistent_id'])) &&
							(!is_null($value['persistent_id']))
						)
					)
						throw new memcached_connect_exception(
							'The persistent_id parameter is not a string nor null'
						);

					if(
						isset($value['ignore_failed_servers']) &&
						(!is_bool($value['ignore_failed_servers']))
					)
						throw new memcached_connect_exception(
							'The ignore_failed_servers parameter is not a boolean'
						);

					continue;
				}

				foreach([
					'host'=>'string',
					'port'=>'integer',
					'socket'=>'string',
					'weight'=>'integer'
				] as $param=>$param_type)
					if(
						isset($db_config[$key][$param]) &&
						(gettype($db_config[$key][$param]) !== $param_type)
					)
						throw new memcached_connect_exception(
							'The '.$param.' parameter is not a '.$param_type
						);
			}

		if(!isset(
			$servers['options']['persistent_id']
		))
			$servers['options']['persistent_id']=null;

		if(!isset(
			$servers['options']['ignore_failed_servers']
		))
			$servers['options']['ignore_failed_servers']=false;

		$memcached_handle=memcached_connect_bridge::Memcached(
			$servers['options']['persistent_id']
		);

		foreach($servers as $server_index=>$server)
		{
			if($server_index === 'options')
				continue;

			if(
				(!isset($server['host'])) &&
				(!isset($server['socket']))
			)
				throw new memcached_connect_exception(
					'Server #'.$server_index.': host or socket is not specified'
				);

			if(!isset($server['port']))
				$server['port']=11211;

			if(!isset($server['weight']))
				$server['weight']=0;

			if(isset($server['socket']))
			{
				$server['host']=$server['socket'];
				$server['port']=0;
			}

			if(
				(!$memcached_handle->addServer(
					$server['host'],
					$server['port'],
					$server['weight']
				)) &&
				($servers['options']['ignore_failed_servers'] !== true)
			)
				throw new memcached_connect_exception(
					'Server '.$server['host'].':'.$server['port'].': connection failed'
				);
		}

		return $memcached_handle;
	}

	final class memcached_connect_bridge
	{
		/*
		 * Most zwodzacy
		 *
		 * A bridge for replacing a Memcached class with another
		 * It can be used for debugging and mocking methods
		 *
		 * Note:
		 *  throws an memcached_connect_exception on error
		 *
		 * Usage:
		 *  before calling any function from this library define a new class
		 *  and set it as a replacement
			class Memcached_mock extends Memcached
			{
				public function __construct(...$arguments)
				{
					// debug when database connection occurs

					echo ': '.__METHOD__.'() :';

					parent::{__FUNCTION__}(
						...$arguments
					);
				}
				public function __destruct()
				{
					// debug when disconnected from database
					echo ': '.__METHOD__.'() :';
				}

				// other methods
			}

			// set the Memcached_mock class as a substitute for the Memcached class
			memcached_connect_bridge::set_class('Memcached_mock', function(...$arguments){
				return new Memcached_mock(
					...$arguments
				);
			});
		 *  then use the functions from this library as if nothing had happened
		 */

		private static $memcached_class_name='Memcached';
		private static $memcached_class=null;

		public static function set_class(
			string $class_name,
			callable $callback
		){
			self::$memcached_class_name=$class_name;
			self::$memcached_class[0]=$callback;
		}

		public static function class_exists()
		{
			return self::$memcached_class_name;
		}
		public static function Memcached(...$arguments)
		{
			if(self::$memcached_class !== null)
				return self::$memcached_class[0](
					...$arguments
				);

			return new Memcached(
				...$arguments
			);
		}

		public function __construct()
		{
			throw new memcached_connect_exception(
				'You cannot initialize '.self::class
			);
		}
	}
?>