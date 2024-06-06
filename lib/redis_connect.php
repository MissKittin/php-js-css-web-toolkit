<?php
	/*
	 * Redis connection helper
	 *
	 * Warning:
	 *  redis extension is required
	 *
	 * Note:
	 *  throws an redis_connect_exception on error
	 *
	 * Functions:
	 	// pre-configured version
	 	redis_connect(
			'./path_to/your_redis_config_directory',
			function($error) // optional
			{
				// executed on RedisException
				error_log('redis_connect: '.$error->getMessage());
			}
		)

		// portable version
	 	redis_connect_array(
			[
				'host'=>'string-server-ip', // required or use socket
				'port'=>int-server-port, // optional, default: 6379
				'socket'=>'string-unix-socket-path', // has priority over the host, eg. /var/run/redis/redis.sock
				'dbindex'=>int-db-index, // optional, default: 0
				'auth'=>[ // optional
					'user'=>'string-phpredis',
					'pass'=>'string-phpredis'
				],
				'timeout'=>float-timeout, // optional, default: 0
				'retry_interval'=>int-retry-interval, // optional, default: 0
				'read_timeout'=>float-read-timeout, // optional, default: 0
				'options'=>[  // optional
					Redis::OPT_BACKOFF_ALGORITHM=>Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
					Redis::OPT_BACKOFF_BASE=>500,
					Redis::OPT_BACKOFF_CAP, 750
				]
			],
			function($error) // optional
			{
				// executed on RedisException
				error_log('redis_connect_array: '.$error->getMessage());
			}
		)
	 */

	class redis_connect_exception extends Exception {}

	function redis_connect(string $db, callable $on_error=null)
	{
		/*
		 * Redis connection helper
		 *
		 * Returns the Redis handler
		 *  or false if an error has occurred
		 * For more info, see redis_connect_array function
		 *
		 * Warning:
		 *  redis_connect_array function is required
		 *
		 * Note:
		 *  throws an redis_connect_exception on error
		 *
		 * Configuration:
		 *  1) create a directory for redis config files
		 *  2) create a config.php file:
				<?php
					return [
						'host'=>'string-server-ip', // required or use socket
						'port'=>int-server-port, // optional, default: 6379
						'socket'=>'string-unix-socket-path', // has priority over the host, eg. /var/run/redis/redis.sock
						'dbindex'=>int-db-index, // optional, default: 0
						'auth'=>[ // optional
							'user'=>'string-phpredis',
							'pass'=>'string-phpredis'
						],
						'timeout'=>float-timeout, // optional, default: 0
						'retry_interval'=>int-retry-interval, // optional, default: 0
						'read_timeout'=>float-read-timeout, // optional, default: 0
						'options'=>[  // optional
							Redis::OPT_BACKOFF_ALGORITHM=>Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
							Redis::OPT_BACKOFF_BASE=>500,
							Redis::OPT_BACKOFF_CAP, 750
						]
					];
				?>
		 *
		 * Initialization:
			$db=redis_connect(
				'./path_to/your_database_config_directory',
				function($error) // optional
				{
					// executed on RedisException
					error_log('redis_connect: '.$error->getMessage());
				}
			);
		 */

		if(!file_exists($db.'/config.php'))
			throw new redis_connect_exception($db.'/config.php not exists');

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new redis_connect_exception($db.'/config.php did not return an array');

		foreach([
			'host'=>'string',
			'port'=>'integer',
			'socket'=>'string',
			'dbindex'=>'integer',
			'auth'=>'array',
			'timeout'=>'double',
			'retry_interval'=>'integer',
			'read_timeout'=>'double',
			'options'=>'array'
		] as $param=>$param_type)
			if(isset($db_config[$param]) && (gettype($db_config[$param]) !== $param_type))
				throw new redis_connect_exception('The '.$param.' parameter is not a '.$param_type);

		return redis_connect_array($db_config, $on_error, false);
	}
	function redis_connect_array(array $db_config, callable $on_error=null, bool $type_hint=true)
	{
		/*
		 * Redis connection helper
		 * portable version
		 *
		 * Returns the Redis handler
		 *  or false if an error has occurred
		 *
		 * Warning:
		 *  redis extension is required
		 *
		 * Note:
		 *  throws an redis_connect_exception on error
		 *
		 * Initialization:
			$db=redis_connect_array(
				[
					'host'=>'string-server-ip', // required or use socket
					'port'=>int-server-port, // optional, default: 6379
					'socket'=>'string-unix-socket-path', // has priority over the host, eg. /var/run/redis/redis.sock
					'dbindex'=>int-db-index, // optional, default: 0
					'auth'=>[ // optional
						'user'=>'string-phpredis',
						'pass'=>'string0phpredis'
					],
					'timeout'=>float-timeout, // optional, default: 0
					'retry_interval'=>int-retry-interval, // optional, default: 0
					'read_timeout'=>float-read-timeout, // optional, default: 0
					'options'=>[  // optional
						Redis::OPT_BACKOFF_ALGORITHM=>Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
						Redis::OPT_BACKOFF_BASE=>500,
						Redis::OPT_BACKOFF_CAP, 750
					]
				],
				function($error) // optional
				{
					// executed on RedisException
					error_log('redis_connect_array: '.$error->getMessage());
				}
			);
		 */

		if(!class_exists('Redis'))
			throw new redis_connect_exception('redis extension is not loaded');

		if($type_hint)
			foreach([
				'host'=>'string',
				'port'=>'integer',
				'socket'=>'string',
				'dbindex'=>'integer',
				'auth'=>'array',
				'timeout'=>'double',
				'retry_interval'=>'integer',
				'read_timeout'=>'double',
				'options'=>'array'
			] as $param=>$param_type)
				if(isset($db_config[$param]) && (gettype($db_config[$param]) !== $param_type))
					throw new redis_connect_exception('The '.$param.' parameter is not a '.$param_type);

		if(isset($db_config['socket']))
		{
			$db_config['host']='unix://'.$db_config['socket'];
			$db_config['port']=0;
		}
		else if(!isset($db_config['host']))
			throw new redis_connect_exception('The host parameter was not specified');

		foreach([
			'port'=>6379,
			'dbindex'=>0,
			'timeout'=>0,
			'retry_interval'=>0,
			'read_timeout'=>0,
			'auth'=>null,
			'options'=>null
		] as $default_config=>$default_value)
			if(!isset($db_config[$default_config]))
				$db_config[$default_config]=$default_value;

		try {
			$redis_handler=new Redis();

			if($db_config['options'] !== null)
				foreach($db_config['options'] as $option_name=>$option_value)
					if(!$redis_handler->setOption($option_name, $option_value))
						throw new redis_connect_exception('setOption returned false');

			if(!$redis_handler->connect(
				$db_config['host'],
				$db_config['port'],
				$db_config['timeout'],
				null, // persistent_id
				$db_config['retry_interval'],
				$db_config['read_timeout']
			))
				return false;

			if(
				($db_config['auth'] !== null) &&
				(!$redis_handler->auth($db_config['auth']))
			)
				return false;

			if(!$redis_handler->select($db_config['dbindex']))
				return false;
		} catch(RedisException $error) {
			if($on_error !== null)
				$on_error($error);

			return false;
		}

		return $redis_handler;
	}
?>