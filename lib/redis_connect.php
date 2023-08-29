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
			function($error)
			{
				error_log('redis_connect: '.$error->getMessage());
			}
		)

		// portable version
	 	redis_connect_array(
			[
				'host'=>'ip-or-unix-socket-path', // required
				'port'=>'server-port',
				'auth'=>[
					'user'=>'phpredis',
					'pass'=>'phpredis'
				],
				'timeout'=>timeout,
				'retry_interval'=>retry-interval,
				'read_timeout'=>read-timeout,
				'options'=>[
					Redis::OPT_BACKOFF_ALGORITHM=>Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
					Redis::OPT_BACKOFF_BASE=>500,
					Redis::OPT_BACKOFF_CAP, 750
				]
			],
			function($error)
			{
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
				return [
					'host'=>'ip-or-unix-socket-path', // required
					'port'=>'server-port',
					'dbindex'=>0,
					'auth'=>[
						'user'=>'phpredis',
						'pass'=>'phpredis'
					],
					'timeout'=>timeout,
					'retry_interval'=>retry-interval,
					'read_timeout'=>read-timeout,
					'options'=>[
						Redis::OPT_BACKOFF_ALGORITHM=>Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
						Redis::OPT_BACKOFF_BASE=>500,
						Redis::OPT_BACKOFF_CAP, 750
					]
				];
		 *
		 * Initialization:
			$db=redis_connect(
				'./path_to/your_database_config_directory',
				function($error)
				{
					error_log('redis_connect: '.$error->getMessage());
				}
			);
		 *   where $on_error is optional and is executed on RedisException
		 */

		if(!file_exists($db.'/config.php'))
			throw new redis_connect_exception($db.'/config.php not exists');

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new redis_connect_exception($db.'/config.php did not return an array');

		return redis_connect_array($db_config, $on_error);
	}
	function redis_connect_array(array $db_config, callable $on_error=null)
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
					'host'=>'ip-or-unix-socket-path', // required
					'port'=>'server-port',
					'dbindex'=>0,
					'auth'=>[
						'user'=>'phpredis',
						'pass'=>'phpredis'
					],
					'timeout'=>timeout,
					'retry_interval'=>retry-interval,
					'read_timeout'=>read-timeout,
					'options'=>[
						Redis::OPT_BACKOFF_ALGORITHM=>Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
						Redis::OPT_BACKOFF_BASE=>500,
						Redis::OPT_BACKOFF_CAP, 750
					]
				],
				function($error)
				{
					error_log('redis_connect_array: '.$error->getMessage());
				}
			);
		 *   where $on_error is optional and is executed on RedisException
		 */

		if(!extension_loaded('redis'))
			throw new redis_connect_exception('redis extension is not loaded');

		if(!isset($db_config['host']))
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