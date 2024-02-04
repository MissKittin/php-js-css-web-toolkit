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
				'persistent_id'=>null, // all instances created with the same persistent_id will share the same connection
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
	 */

	class memcached_connect_exception extends Exception {}
	function memcached_connect(string $db)
	{
		/*
		 * Memcached connection helper
		 *
		 * Returns the Memcached handler
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
				return [
					'options'=>[ // optional
						'persistent_id'=>null, // all instances created with the same persistent_id will share the same connection
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
		 *
		 * Initialization:
		 *  $db=memcached_connect('./path_to/your_database_config_directory');
		 */

		if(!file_exists($db.'/config.php'))
			throw new memcached_connect_exception($db.'/config.php not exists');

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new memcached_connect_exception($db.'/config.php did not return an array');

		return memcached_connect_array($db_config);
	}
	function memcached_connect_array(array $servers)
	{
		/*
		 * Memcached connection helper
		 * portable version
		 *
		 * Returns the Memcached handler
		 *
		 * Warning:
		 *  memcached extension is required
		 *
		 * Note:
		 *  throws an memcached_connect_exception on error
		 *
		 * Initialization:
			$db=memcached_connect_array([
				'options'=>[ // optional
					'persistent_id'=>null, // all instances created with the same persistent_id will share the same connection
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
			]);
		 */

		if(!extension_loaded('memcached'))
			throw new memcached_connect_exception('memcached extension is not loaded');

		if(!isset($servers['options']['persistent_id']))
			$servers['options']['persistent_id']=null;

		$memcached_handler=new Memcached($servers['options']['persistent_id']);

		foreach($servers as $server_index=>$server)
		{
			if($server_index === 'options')
				continue;

			if((!isset($server['host'])) && (!isset($server['socket'])))
				throw new memcached_connect_exception('Server #'.$server_index.': host or socket is not specified');

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
				(!$memcached_handler->addServer(
					$server['host'],
					$server['port'],
					$server['weight']
				)) &&
				($servers['options']['ignore_failed_servers'] !== true)
			)
				throw new memcached_connect_exception('Server '.$server['host'].':'.$server['port'].': connection failed');
		}

		return $memcached_handler;
	}
?>