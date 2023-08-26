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
	 * Functions:
	 	// with seeder
	 	pdo_connect(
			'./path_to/your_database_config_directory',
			function($error)
			{
				error_log('pdo_connect: '.$error->getMessage());
			}
		)

		// portable version
	 	pdo_connect_array(
			[
				'db_type'=>'your-db-type', // sqlite pgsql mysql
				'host'=>'server-ip-or-sqlite-db-path',
				'port'=>'server-port',
				//'socket'=>'/path/to/socket', // uncomment to use a unix socket
				'db_name'=>'database-name',
				'charset'=>'your-db-charset', // for pgsql and mysql only, optional
				'user'=>'username',
				'password'=>'password'
			],
			function($error)
			{
				error_log('pdo_connect_array: '.$error->getMessage());
			}
		)
	 */

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
		 * Configuration:
		 *  1) create a directory for database config files
		 *  2) create a config.php file:
				return [
					'db_type'=>'your-db-type', // sqlite pgsql mysql
					'host'=>'server-ip-or-sqlite-db-path',
					'port'=>'server-port',
					//'socket'=>'/path/to/socket', // uncomment to use a unix socket
					'db_name'=>'database-name',
					'charset'=>'your-db-charset', // for pgsql and mysql only, optional
					'user'=>'username',
					'password'=>'password',
					//'seeded_path'=>$db // uncomment this to move the database_seeded file to a different location
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
				function($error)
				{
					error_log('pdo_connect: '.$error->getMessage());
				}
			);
		 *   where $on_error is optional and is executed on PDOException
		 */

		if(!file_exists($db.'/config.php'))
			throw new Exception($db.'/config.php not exists');

		$db_config=require $db.'/config.php';

		if(!is_array($db_config))
			throw new Exception($db.'/config.php did not return an array');

		if(!isset($db_config['seeded_path']))
			$db_config['seeded_path']=$db;

		if(
			isset($db_config['db_type']) &&
			($db_config['db_type'] === 'sqlite') &&
			isset($db_config['host']) &&
			(!file_exists($db_config['host']))
		)
			@unlink($db_config['seeded_path'].'/database_seeded');

		$pdo_handler=pdo_connect_array($db_config, $on_error);

		if($pdo_handler === false)
			return false;

		if(
			file_exists($db.'/seed.php') &&
			(!file_exists($db_config['seeded_path'].'/database_seeded'))
		){
			if(file_put_contents($db_config['seeded_path'].'/database_seed_w_test', '') === false)
				throw new Exception('Could not create database_seed_w_test file in '.$db_config['seeded_path']);

			unlink($db_config['seeded_path'].'/database_seed_w_test');
			include $db.'/seed.php';
			file_put_contents($db_config['seeded_path'].'/database_seeded', '');
		}

		return $pdo_handler;
	}
	function pdo_connect_array(array $db_config, callable $on_error=null)
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
					'db_type'=>'your-db-type', // sqlite pgsql mysql
					'host'=>'server-ip-or-sqlite-db-path',
					'port'=>'server-port',
					//'socket'=>'/path/to/socket', // uncomment to use a unix socket
					'db_name'=>'database-name',
					'charset'=>'your-db-charset', // for pgsql and mysql only, optional
					'user'=>'username',
					'password'=>'password'
				],
				function($error)
				{
					error_log('pdo_connect_array: '.$error->getMessage());
				}
			);
		 *   where $on_error is optional and is executed on PDOException
		 */

		$_check_params=function($db_config, $params)
		{
			foreach($params as $db_config_param)
				if(!isset($db_config[$db_config_param]))
					throw new Exception('The '.$db_config_param.' parameter was not specified');
		};

		if(!extension_loaded('PDO'))
			throw new Exception('PDO extension is not loaded');

		if(!isset($db_config['db_type']))
			throw new Exception('The db_type parameter was not specified');

		try {
			switch($db_config['db_type'])
			{
				case 'pgsql':
					if(!extension_loaded('pdo_pgsql'))
						throw new Exception('pdo_pgsql extension is not loaded');

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
						throw new Exception('pdo_mysql extension is not loaded');

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
						throw new Exception('pdo_sqlite extension is not loaded');

					$_check_params($db_config, ['host']);
					$pdo_handler=new PDO('sqlite:'.$db_config['host']);
				break;
				default:
					throw new Exception($db_config['db_type'].' database type is not supported');
			}
		} catch(PDOException $error) {
			if($on_error !== null)
				$on_error($error);

			return false;
		}

		return $pdo_handler;
	}
?>