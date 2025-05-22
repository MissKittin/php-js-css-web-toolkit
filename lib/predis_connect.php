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
	 *
	 * Most zwadzacy:
	 *  a bridge for replacing a Predis\Client class with another
	 *  recommended to be used with extreme caution
	 *  more info below
	 */

	class predis_connect_exception extends Exception {}

	function predis_connect(string $db)
	{
		/*
		 * Predis connection helper
		 *
		 * Warning:
		 *  predis_connect_bridge class is required
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
			$redis=predis_connect('./path_to/your_database_config_directory');
		 */

		if(!class_exists(
			predis_connect_bridge::class_exists()
		))
			throw new predis_connect_exception(
				'predis/predis package is not installed'
			);

		if(!file_exists($db.'/config.php'))
			throw new predis_connect_exception(
				$db.'/config.php not exists'
			);

		if(file_exists($db.'/options.php'))
			return predis_connect_bridge::Predis_Client(
				require $db.'/config.php',
				require $db.'/options.php'
			);

		return predis_connect_bridge::Predis_Client(
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

		protected $predis_handle;

		public function __construct($predis_handle)
		{
			$this->predis_handle=$predis_handle;
		}
		public function __call($method, $args)
		{
			switch($method)
			{
				case 'set':
					if(
						isset($args[2]) &&
						is_array($args[2])
					){
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

			$output=$this
			->	predis_handle
			->	$method(...$args);

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
	final class predis_connect_bridge
	{
		/*
		 * Most zwodzacy
		 *
		 * A bridge for replacing a Predis\Client class with another
		 * It can be used for debugging and mocking methods
		 *
		 * Note:
		 *  throws an predis_connect_exception on error
		 *
		 * Usage:
		 *  before calling any function from this library define a new class
		 *  and set it as a replacement
			class Predis_Client_mock extends Predis\Client
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

			// set the Predis_Client_mock class as a substitute for the Predis\Client class
			predis_connect_bridge::set_class('Predis_Client_mock', function(...$arguments){
				return new Predis_Client_mock(
					...$arguments
				);
			});
		 *  then use the functions from this library as if nothing had happened
		 */

		private static $predis_class_name='\Predis\Client';
		private static $predis_class=null;

		public static function set_class(
			string $class_name,
			callable $callback
		){
			self::$predis_class_name=$class_name;
			self::$predis_class[0]=$callback;
		}

		public static function class_exists()
		{
			return self::$predis_class_name;
		}
		public static function Predis_Client(...$arguments)
		{
			if(self::$predis_class !== null)
				return self::$predis_class[0](
					...$arguments
				);

			return new Predis\Client(
				...$arguments
			);
		}

		public function __construct()
		{
			throw new predis_connect_exception(
				'You cannot initialize '.self::class
			);
		}
	}
?>