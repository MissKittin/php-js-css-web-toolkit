<?php
	/*
	 * PDO connection helper
	 *
	 * Supported databases:
	 *  PostgreSQL
	 *  MySQL
	 *  SQLite3
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_pgsql is required for the PostgreSQL driver
	 *  pdo_mysql is required for the MySQL driver
	 *  pdo_sqlite is required for the SQLite3 driver
	 *
	 * Note:
	 *  throws an pdo_connect_exception on error
	 *
	 * Functions:
	 	// with seeder
	 	pdo_connect(
			'./path_to/your_database_config_directory',
			function($error) // optional
			{
				// executed on PDOException
				my_log_function('pdo_connect: '.$error->getMessage());
			},
			true, // optional, enable seeder (default), set to false to disable
			false // optional, reseed the database
		)

		// portable version
	 	pdo_connect_array(
			[
				'db_type'=>'string-your-db-type', // or use socket, sqlite pgsql mysql
				'host'=>'string-server-ip-or-sqlite-db-path',
				'port'=>'string-server-port',
				//'socket'=>'string/path/to/socket', // uncomment to use a unix socket
				'db_name'=>'string-database-name',
				'charset'=>'string-your-db-charset', // for pgsql and mysql only, optional
				'user'=>'string-username',
				'password'=>'string-password',
				'pdo_options'=>[ // set PDO options via constructor, optional
					PDO::ATTR_PERSISTENT=>true
				]
			],
			function($error) // optional
			{
				// executed on PDOException
				my_log_function(''
				.	'pdo_connect_array: '
				.	$error->getMessage()
				);
			}
		)
	 *
	 * Note:
	 *  the socket path varies depending on the database, eg.
	 *  for pgsql (note: this is the directory path): /var/run/postgresql
	 *  for mysql: /var/run/mysqld/mysqld.sock
	 *
	 * Most zwadzacy:
	 *  a bridge for replacing a PDO class with another
	 *  recommended to be used with extreme caution
	 *  more info below
	 */

	class pdo_connect_exception extends Exception {}

	function pdo_connect(
		string $db,
		?callable $on_error=null,
		bool $enable_seeder=true,
		bool $reseed=false
	){
		/*
		 * PDO connection helper
		 * with automatic seeder
		 *
		 * Returns the PDO handle
		 *  or false if an error has occurred
		 * For more info, see pdo_connect_array function
		 *
		 * Warning:
		 *  pdo_connect_array function is required
		 *  _pdo_connect_sqlite_helper function is required
		 *
		 * Note:
		 *  throws an pdo_connect_exception on error
		 *  seeder always run when sqlite host is :memory:
		 *
		 * Configuration:
		 *  1) create a directory for database config files
		 *  2) create a config.php file:
				<?php
					return [
						'db_type'=>'string-your-db-type', // sqlite pgsql mysql
						'host'=>'string-server-ip-or-sqlite-db-path', // or use socket, can be :memory: for sqlite
						'port'=>'string-server-port',
						//'socket'=>'string/path/to/socket', // uncomment to use a unix socket
						'db_name'=>'string-database-name',
						'charset'=>'string-your-db-charset', // for pgsql and mysql only, optional
						'user'=>'string-username',
						'password'=>'string-password',
						//'seeded_path'=>$db, // string, uncomment this to move the database_seeded file to a different location
						'pdo_options'=>[ // set PDO options via constructor, optional
							PDO::ATTR_PERSISTENT=>true
						]
					];
				?>
		 *  3) optionally you can create a seed.php file which will initialize the database, eg:
				<?php
					$pdo_handle->exec(''
					.	'CREATE TABLE tablename('
					//.		'id SERIAL PRIMARY KEY,' // pgsql
					//.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),' // mysql
					.		'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,' // sqlite
					.		'sampletext VARCHAR(255),'
					.		'sampleint INTEGER'
					.	')'
					);
				?>
		 *
		 * Initialization:
			$db=pdo_connect(
				'./path_to/your_database_config_directory',
				function($error) // optional
				{
					// executed on PDOException
					my_log_function(''
					.	'pdo_connect: '
					.	$error->getMessage()
					);
				},
				true, // optional, enable seeder (default), set to false to disable
				false // optional, reseed the database
			);
		 *
		 * Note:
		 *  the socket path varies depending on the database, eg.
		 *  for pgsql (note: this is the directory path): /var/run/postgresql
		 *  for mysql: /var/run/mysqld/mysqld.sock
		 */

		if(!file_exists($db.'/config.php'))
			throw new pdo_connect_exception(
				$db.'/config.php not exists'
			);

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new pdo_connect_exception(
				$db.'/config.php did not return an array'
			);

		foreach([
			'db_type',
			'host',
			'port',
			'socket',
			'db_name',
			'charset',
			'user',
			'password',
			'seeded_path'
		] as $param)
			if(
				isset($db_config[$param]) &&
				(!is_string($db_config[$param]))
			)
				throw new pdo_connect_exception(
					'The '.$param.' parameter is not a string'
				);

		if(!isset($db_config['db_type']))
			throw new pdo_connect_exception(
				'The db_type parameter was not specified'
			);

		if(
			$enable_seeder &&
			(!is_file($db.'/seed.php'))
		)
			$enable_seeder=false;

		if($enable_seeder)
		{
			// true when (seeded_path === null) or (host === ':memory:')
			$do_seed_sqlite=_pdo_connect_sqlite_helper($db_config);

			if(!isset($db_config['seeded_path']))
				$db_config['seeded_path']=$db;
		}

		$pdo_handle=pdo_connect_array($db_config, $on_error, false);

		if($pdo_handle === false)
			return false;

		if($enable_seeder)
		{
			if(
				$reseed &&
				file_exists(''
				.	$db_config['seeded_path']
				.	'/database_seeded'
				)
			)
				unlink(''
				.	$db_config['seeded_path']
				.	'/database_seeded'
				);

			if($do_seed_sqlite)
			{
				include $db.'/seed.php';
				return $pdo_handle;
			}

			if(
				isset($db_config['seeded_path']) &&
				(!file_exists(''
				.	$db_config['seeded_path']
				.	'/database_seeded'
				))
			){
				if(file_put_contents(''
				.	$db_config['seeded_path']
				.	'/database_seed_w_test'
				, '') === false)
					throw new pdo_connect_exception(
						'Could not create database_seed_w_test file in '.$db_config['seeded_path']
					);

				unlink(''
				.	$db_config['seeded_path']
				.	'/database_seed_w_test'
				);

				require $db.'/seed.php';

				file_put_contents(''
				.	$db_config['seeded_path']
				.	'/database_seeded'
				, '');
			}
		}

		return $pdo_handle;
	}
	function pdo_connect_array(
		array $db_config,
		?callable $on_error=null,
		bool $type_hint=true
	){
		/*
		 * PDO connection helper
		 * portable version
		 *
		 * Returns the PDO handle
		 *  or false if an error has occurred
		 *
		 * Warning:
		 *  pdo_connect_bridge class is required
		 *  PDO extension is required
		 *  pdo_pgsql is required for the PostgreSQL driver
		 *  pdo_mysql is required for the MySQL driver
		 *  pdo_sqlite is required for the SQLite3 driver
		 *
		 * Note:
		 *  throws an pdo_connect_exception on error
		 *
		 * Required parameters:
		 *  pgsql:
		 *   socket db_name user password
		 *   host port db_name user password
		 *  mysql:
		 *   socket db_name user password
		 *   host port db_name user password
		 *  sqlite:
		 *   host
		 *
		 * Initialization:
			$db=pdo_connect_array(
				[
					'db_type'=>'string-your-db-type', // sqlite pgsql mysql
					'host'=>'string-server-ip-or-sqlite-db-path', // or use socket, can be :memory: for sqlite
					'port'=>'string-server-port',
					//'socket'=>'string/path/to/socket', // uncomment to use a unix socket
					'db_name'=>'string-database-name',
					'charset'=>'string-your-db-charset', // for pgsql and mysql only, optional
					'user'=>'string-username',
					'password'=>'string-password',
					'pdo_options'=>[ // set PDO options via constructor, optional
						PDO::ATTR_PERSISTENT=>true
					]
				],
				function($error) // optional
				{
					// executed on PDOException
					my_log_function(''
					.	'pdo_connect_array: '
					.	$error->getMessage()
					);
				}
			);
		 *
		 * Note:
		 *  the socket path varies depending on the database, eg.
		 *  for pgsql (note: this is the directory path): /var/run/postgresql
		 *  for mysql: /var/run/mysqld/mysqld.sock
		 */

		$_check_params=function($db_config, $params)
		{
			foreach($params as $db_config_param)
				if(!isset($db_config[$db_config_param]))
					throw new pdo_connect_exception(
						'The '.$db_config_param.' parameter was not specified'
					);
		};

		if(!class_exists(
			pdo_connect_bridge::class_exists()
		))
			throw new pdo_connect_exception(
				'PDO extension is not loaded'
			);

		if($type_hint)
		{
			if(!isset($db_config['db_type']))
				throw new pdo_connect_exception(
					'The db_type parameter was not specified'
				);

			foreach([
				'db_type',
				'host',
				'port',
				'socket',
				'db_name',
				'charset',
				'user',
				'password'
			] as $param)
				if(
					isset($db_config[$param]) &&
					(!is_string($db_config[$param]))
				)
					throw new pdo_connect_exception(
						'The '.$param.' parameter is not a string'
					);
		}

		if(!isset($db_config['pdo_options']))
			$db_config['pdo_options']=null;

		try {
			switch($db_config['db_type'])
			{
				case 'pgsql':
					if(!in_array(
						'pgsql',
						pdo_connect_bridge::getAvailableDrivers()
					))
						throw new pdo_connect_exception(
							'pdo_pgsql extension is not loaded'
						);

					$db_config['charset']='';

					if(
						isset($db_config['charset']) &&
						(!empty($db_config['charset']))
					)
						$db_config['charset']=';'
						.	'options='."'"
						.		'--client_encoding='.$db_config['charset']
						.	"'";

					if(isset($db_config['socket']))
					{
						$_check_params($db_config, [
							'socket',
							'db_name',
							'user',
							'password'
						]);

						return pdo_connect_bridge::PDO($db_config['db_type'].':'
						.	'host='.$db_config['socket'].';'
						.	'dbname='.$db_config['db_name'].';'
						.	'user='.$db_config['user'].';'
						.	'password='.$db_config['password']
						.	$db_config['charset']
						,	null
						,	null
						,	$db_config['pdo_options']
						);
					}

					$_check_params($db_config, [
						'host',
						'port',
						'db_name',
						'user',
						'password'
					]);

					return pdo_connect_bridge::PDO($db_config['db_type'].':'
					.	'host='.$db_config['host'].';'
					.	'port='.$db_config['port'].';'
					.	'dbname='.$db_config['db_name'].';'
					.	'user='.$db_config['user'].';'
					.	'password='.$db_config['password']
					.	$db_config['charset']
					,	null
					,	null
					,	$db_config['pdo_options']
					);
				break;
				case 'mysql':
					if(!in_array(
						'mysql',
						pdo_connect_bridge::getAvailableDrivers()
					))
						throw new pdo_connect_exception(
							'pdo_mysql extension is not loaded'
						);

					$db_config['charset']='';

					if(
						isset($db_config['charset']) &&
						(!empty($db_config['charset']))
					)
						$db_config['charset']=';'
						.	'charset='.$db_config['charset'];

					if(isset($db_config['socket']))
					{
						$_check_params($db_config, [
							'socket',
							'db_name',
							'user',
							'password'
						]);

						return pdo_connect_bridge::PDO($db_config['db_type'].':'
						.	'unix_socket='.$db_config['socket'].';'
						.	'dbname='.$db_config['db_name']
						.	$db_config['charset']
						,	$db_config['user']
						,	$db_config['password']
						,	$db_config['pdo_options']
						);
					}

					$_check_params($db_config, [
						'host',
						'port',
						'db_name',
						'user',
						'password'
					]);

					return pdo_connect_bridge::PDO($db_config['db_type'].':'
					.	'host='.$db_config['host'].';'
					.	'port='.$db_config['port'].';'
					.	'dbname='.$db_config['db_name']
					.	$db_config['charset']
					,	$db_config['user']
					,	$db_config['password']
					,	$db_config['pdo_options']
					);
				break;
				case 'sqlite':
					if(!in_array(
						'sqlite',
						pdo_connect_bridge::getAvailableDrivers()
					))
						throw new pdo_connect_exception(
							'pdo_sqlite extension is not loaded'
						);

					$_check_params(
						$db_config,
						['host']
					);

					return pdo_connect_bridge::PDO($db_config['db_type'].':'
					.	$db_config['host']
					);
				break;
				default:
					throw new pdo_connect_exception(
						$db_config['db_type'].' database type is not supported'
					);
			}
		} catch(PDOException $error) {
			if($on_error !== null)
				$on_error($error);

			return false;
		}
	}
	function _pdo_connect_sqlite_helper(&$db_config)
	{
		// Seeder helper

		if(
			($db_config['db_type'] !== 'sqlite') ||
			(!isset($db_config['host']))
		)
			return false;

		if($db_config['host'] === ':memory:')
			return true;

		if(!file_exists($db_config['host']))
		{
			// null is only for sqlite
			if(!isset($db_config['seeded_path']))
				$db_config['seeded_path']=null;

			if($db_config['seeded_path'] === null)
				return true;

			if(
				(!file_exists($db_config['host'])) &&
				file_exists(''
				.	$db_config['seeded_path']
				.	'/database_seeded'
				)
			)
				unlink(''
				.	$db_config['seeded_path']
				.	'/database_seeded'
				);
		}

		return false;
	}

	final class pdo_connect_bridge
	{
		/*
		 * Most zwodzacy
		 *
		 * A bridge for replacing a PDO class with another
		 * It can be used for debugging and mocking methods
		 * or for defining your own PDO drivers in pure PHP
		 *
		 * Note:
		 *  throws an pdo_connect_exception on error
		 *
		 * Usage:
		 *  before calling any function from this library define a new class
		 *  and set it as a replacement
			class PDO_mock extends PDO // you don't need to extend PDO if you have a PDO-compliant replacement
			{
				public static function getAvailableDrivers()
				{
					// add customdbdriver to the registry

					return array_merge(
						parent::{__FUNCTION__}(),
						['customdbdriver']
					);
				}

				public function __construct(...$arguments)
				{
					// debug when database connection occurs

					echo ': '.__METHOD__.'() :';

					parent::{__FUNCTION__}(
						...$arguments
					);

					// add connection to debug bar collector (maximebf_debugbar.php library)
					//maximebf_debugbar
					//::	get_collector('pdo')
					//->	addConnection($this);
				}
				public function __destruct()
				{
					// debug when disconnected from database
					echo ': '.__METHOD__.'() :';
				}

				// other methods
			}

			// set the PDO_mock class as a substitute for the PDO class
			pdo_connect_bridge::set_class('PDO_mock', function(...$arguments){
				return new PDO_mock(
					...$arguments
				);
			});
		 *  then use the functions from this library as if nothing had happened
		 */

		private static $pdo_class_name='PDO';
		private static $pdo_class=null;

		public static function set_class(
			string $class_name,
			callable $callback
		){
			self::$pdo_class_name=$class_name;
			self::$pdo_class[0]=$callback;
		}

		public static function class_exists()
		{
			return self::$pdo_class_name;
		}
		public static function getAvailableDrivers()
		{
			return self
			::	$pdo_class_name
			::	getAvailableDrivers();
		}
		public static function PDO(...$arguments)
		{
			if(self::$pdo_class !== null)
				return self::$pdo_class[0](
					...$arguments
				);

			return new PDO(
				...$arguments
			);
		}

		public function __construct()
		{
			throw new pdo_connect_exception(
				'You cannot initialize '.self::class
			);
		}
	}
?>