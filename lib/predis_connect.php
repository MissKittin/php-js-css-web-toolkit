<?php
	/*
	 * Predis support and PHPRedis proxy
	 *
	 * Warning:
	 *  predis/predis package is required
	 *
	 * Note:
	 *  throws an predis_connect_exception on error
	 *
	 * Functions:
	 *  predis_connect('./path_to/your_database_config_directory')
	 *   use config file
	 *  predis_connect_proxy('./path_to/your_database_config_directory')
	 *   wrap predis_connect with proxy
	 *
	 * Classes:
	 *  predis_phpredis_proxy(predis_connect('./path_to/your_database_config_directory'))
	 *   same as predis_connect_proxy
	 */

	class predis_connect_exception extends Exception {}

	function predis_connect(string $db)
	{
		/*
		 * Predis connection helper
		 *
		 * Warning:
		 *  predis/predis package is required
		 *
		 * Note:
		 *  throws an predis_connect_exception on error
		 *
		 * Configuration:
		 *  1) create a directory for database config files
		 *  2) create a config.php file:
				<?php
					return [
						'scheme'=>'tcp',
						'host'=>'127.0.0.1',
						'port'=>6379,
						'database'=>0
					];
				?>
		 *  3) you can also create an options.php file:
				<?php
					return ['prefix'=>'sample:'];
				?>
		 *
		 * Initialization:
		 *  $redis=predis_connect('./path_to/your_database_config_directory');
		 */

		if(!class_exists('\Predis\Client'))
			throw new predis_connect_exception('predis/predis package is not installed');

		if(!file_exists($db.'/config.php'))
			throw new predis_connect_exception($db.'/config.php not exists');

		if(file_exists($db.'/options.php'))
			return new Predis\Client(
				require $db.'/config.php',
				require $db.'/options.php'
			);

		return new Predis\Client(
			require $db.'/config.php'
		);
	}
	function predis_connect_proxy(string $db)
	{
		return new predis_phpredis_proxy(
			predis_connect($db)
		);
	}

	class predis_phpredis_proxy
	{
		/*
		 * Provides basic PHPRedis API compatibility for Predis
		 * so that you can use Redis-enabled libraries
		 * without installing the PECL extension
		 *
		 * Warning:
		 *  tested only with toolkit libraries and components
		 *
		 * Usage:
			$redis=new predis_phpredis_proxy(new Predis\Client(
				'scheme'=>'tcp',
				'host'=>'127.0.0.1',
				'port'=>6379,
				'database'=>0
			));
		 */

		protected $predis_handler;

		public function __construct($predis_handler)
		{
			$this->predis_handler=$predis_handler;
		}
		public function __call($method, $args)
		{
			switch($method)
			{
				case 'set':
					if(isset($args[2]) && is_array($args[2]))
					{
						// set($key, $value, ['ex'=>$timeout]) --> set($key, $value, 'ex', $timeout)

						$old_args=$args[2];
						unset($args[2]);

						foreach($old_args as $key=>$value)
						{
							$args[]=$key;
							$args[]=$value;
						}
					}
				break;
				case 'scan':
					// scan(&$iterator, $pattern) --> scan(&$iterator, ['MATCH'=>$pattern])
					$args[1]=['MATCH'=>$args[1]];
			}

			$output=$this->predis_handler->$method(...$args);

			switch($method)
			{
				case 'set':
					// set(): Predis\Response\Status --> set(): bool
					if(
						($output->__toString() === 'OK') ||
						($output->__toString() === 'QUEUED')
					)
						return true;

					return false;
				case 'scan':
					// scan(): [[iterator], [matches]] --> scan(): [matches]|false
					if(empty($output[1]))
						return false;

					return $output[1];
				default:
					// get(): null --> get(): false
					if($output === null)
						return false;
			}

			return $output;
		}
	}
?>