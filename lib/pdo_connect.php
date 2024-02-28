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
				error_log('pdo_connect: '.$error->getMessage());
			}
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
				'password'=>'string-password'
			],
			function($error) // optional
			{
				// executed on PDOException
				error_log('pdo_connect_array: '.$error->getMessage());
			}
		)
	 *
	 * Note:
	 *  the socket path varies depending on the database, eg.
	 *  for pgsql (note: directory path): /var/run/postgresql
	 *  for mysql: /var/run/mysqld/mysqld.sock
	 */

	class pdo_connect_exception extends Exception {}
	function pdo_connect(string $db, callable $on_error=null)
	{
		/*
		 * PDO connection helper
		 * with automatic seeder
		 *
		 * Returns the PDO handler
		 *  or false if an error has occurred
		 * For more info, see pdo_connect_array function
		 *
		 * Warning:
		 *  pdo_connect_array function is required
		 *
		 * Note:
		 *  throws an pdo_connect_exception on error
		 *
		 * Configuration:
		 *  1) create a directory for database config files
		 *  2) create a config.php file:
				return [
					'db_type'=>'string-your-db-type', // sqlite pgsql mysql
					'host'=>'string-server-ip-or-sqlite-db-path', // or use socket, can be :memory: for sqlite
					'port'=>'string-server-port',
					//'socket'=>'string/path/to/socket', // uncomment to use a unix socket
					'db_name'=>'string-database-name',
					'charset'=>'string-your-db-charset', // for pgsql and mysql only, optional
					'user'=>'string-username',
					'password'=>'string-password',
					//'seeded_path'=>$db // string, uncomment this to move the database_seeded file to a different location
				];
		 *  3) optionally you can create a seed.php file which will initialize the database, eg:
				$pdo_handler->exec(''
				.	'CREATE TABLE tablename('
				//.		'id SERIAL PRIMARY KEY,' // pgsql
				//.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),' // mysql
				.		'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,' // sqlite
				.		'sampletext VARCHAR(255),'
				.		'sampleint INTEGER'
				.	')'
				)
		 *
		 * Initialization:
			$db=pdo_connect(
				'./path_to/your_database_config_directory',
				function($error) // optional
				{
					// executed on PDOException
					error_log('pdo_connect: '.$error->getMessage());
				}
			);
		 *
		 * Note:
		 *  the socket path varies depending on the database, eg.
		 *  for pgsql (note: directory path): /var/run/postgresql
		 *  for mysql: /var/run/mysqld/mysqld.sock
		 */

		if(!file_exists($db.'/config.php'))
			throw new pdo_connect_exception($db.'/config.php not exists');

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new pdo_connect_exception($db.'/config.php did not return an array');

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
			if(isset($db_config[$param]) && (!is_string($db_config[$param])))
				throw new pdo_connect_exception('The '.$param.' parameter is not a string');

		if(!isset($db_config['db_type']))
			throw new pdo_connect_exception('The db_type parameter was not specified');

		$do_seed=false;
		if(is_file($db.'/seed.php'))
			$do_seed=true;

		if($do_seed)
		{
			if(!isset($db_config['seeded_path']))
				$db_config['seeded_path']=$db;

			if(
				($db_config['db_type'] === 'sqlite') &&
				isset($db_config['host']) &&
				($db_config['host'] !== ':memory:') &&
				(!file_exists($db_config['host'])) &&
				file_exists($db_config['seeded_path'].'/database_seeded')
			)
				unlink($db_config['seeded_path'].'/database_seeded');
		}

		$pdo_handler=pdo_connect_array($db_config, $on_error, false);

		if($pdo_handler === false)
			return false;

		if($do_seed)
		{
			if(
				isset($db_config['host']) &&
				($db_config['db_type'].$db_config['host'] === 'sqlite:memory:')
			)
				include $db.'/seed.php';
			else if(!file_exists($db_config['seeded_path'].'/database_seeded'))
			{
				if(file_put_contents($db_config['seeded_path'].'/database_seed_w_test', '') === false)
					throw new pdo_connect_exception('Could not create database_seed_w_test file in '.$db_config['seeded_path']);

				unlink($db_config['seeded_path'].'/database_seed_w_test');
				require $db.'/seed.php';
				file_put_contents($db_config['seeded_path'].'/database_seeded', '');
			}
		}

		return $pdo_handler;
	}
	function pdo_connect_array(array $db_config, callable $on_error=null, bool $type_hint=true)
	{
		/*
		 * PDO connection helper
		 * portable version
		 *
		 * Returns the PDO handler
		 *  or false if an error has occurred
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
					'password'=>'string-password'
				],
				function($error) // optional
				{
					// executed on PDOException
					error_log('pdo_connect_array: '.$error->getMessage());
				}
			);
		 *
		 * Note:
		 *  the socket path varies depending on the database, eg.
		 *  for pgsql (note: directory path): /var/run/postgresql
		 *  for mysql: /var/run/mysqld/mysqld.sock
		 */

		$_check_params=function($db_config, $params)
		{
			foreach($params as $db_config_param)
				if(!isset($db_config[$db_config_param]))
					throw new pdo_connect_exception('The '.$db_config_param.' parameter was not specified');
		};

		if(!extension_loaded('PDO'))
			throw new pdo_connect_exception('PDO extension is not loaded');

		if($type_hint)
		{
			if(!isset($db_config['db_type']))
				throw new pdo_connect_exception('The db_type parameter was not specified');

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
				if(isset($db_config[$param]) && (!is_string($db_config[$param])))
					throw new pdo_connect_exception('The '.$param.' parameter is not a string');
		}

		try {
			switch($db_config['db_type'])
			{
				case 'pgsql':
					if(!extension_loaded('pdo_pgsql'))
						throw new pdo_connect_exception('pdo_pgsql extension is not loaded');

					if(isset($db_config['charset']) && (!empty($db_config['charset'])))
						$db_config['charset']=';options=\'--client_encoding='.$db_config['charset'].'\'';
					else
						$db_config['charset']='';

					if(isset($db_config['socket']))
					{
						$_check_params($db_config, ['socket', 'db_name', 'user', 'password']);

						$pdo_handler=new PDO($db_config['db_type']
						.	':host='.$db_config['socket']
						.	';dbname='.$db_config['db_name']
						.	';user='.$db_config['user']
						.	';password='.$db_config['password']
						.	$db_config['charset']
						);
					}
					else
					{
						$_check_params($db_config, ['host', 'port', 'db_name', 'user', 'password']);

						$pdo_handler=new PDO($db_config['db_type']
						.	':host='.$db_config['host']
						.	';port='.$db_config['port']
						.	';dbname='.$db_config['db_name']
						.	';user='.$db_config['user']
						.	';password='.$db_config['password']
						.	$db_config['charset']
						);
					}
				break;
				case 'mysql':
					if(!extension_loaded('pdo_mysql'))
						throw new pdo_connect_exception('pdo_mysql extension is not loaded');

					if(isset($db_config['charset']) && (!empty($db_config['charset'])))
						$db_config['charset']=';charset='.$db_config['charset'];
					else
						$db_config['charset']='';

					if(isset($db_config['socket']))
					{
						$_check_params($db_config, ['socket', 'db_name', 'user', 'password']);

						$pdo_handler=new PDO($db_config['db_type']
						.	':unix_socket='.$db_config['socket']
						.	';dbname='.$db_config['db_name']
						.	$db_config['charset'],
							$db_config['user'],
							$db_config['password']
						);
					}
					else
					{
						$_check_params($db_config, ['host', 'port', 'db_name', 'user', 'password']);

						$pdo_handler=new PDO($db_config['db_type']
						.	':host='.$db_config['host']
						.	';port='.$db_config['port']
						.	';dbname='.$db_config['db_name']
						.	$db_config['charset'],
							$db_config['user'],
							$db_config['password']
						);
					}
				break;
				case 'sqlite':
					if(!extension_loaded('pdo_sqlite'))
						throw new pdo_connect_exception('pdo_sqlite extension is not loaded');

					$_check_params($db_config, ['host']);
					$pdo_handler=new PDO('sqlite:'.$db_config['host']);
				break;
				default:
					throw new pdo_connect_exception($db_config['db_type'].' database type is not supported');
			}
		} catch(PDOException $error) {
			if($on_error !== null)
				$on_error($error);

			return false;
		}

		return $pdo_handler;
	}
?>