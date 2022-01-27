<?php
	function pdo_connect(string $db, callable $on_error=function(){})
	{
		/*
		 * PDO connection helper
		 * with automatic seeder
		 *
		 * Supported databases:
		 *  SQLite3
		 *  PostgreSQL
		 *  MySQL
		 *
		 * Returns the PDO handler, or false if an error has occurred
		 *
		 * Configuration:
		 *  1) create a directory for database config files
		 *  2) create a config.php file:
				$db_type='your-db-type'; // sqlite pgsql mysql
				$db_host='server-ip-or-sqlite-db-path';
				//$db_socket='/path/to/socket'; // uncomment to use a unix socket
				$db_port='server-port';
				$db_name='database-name';
				$db_user='username';
				$db_password='password';
				//$db_seeded_path=$db; // uncomment this to move the database_seeded file to a different location
		 *  3) optionally you can create a seed.php file which will initialize the database, eg:
				$pdo_handler->exec('
					CREATE TABLE tablename(
						id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
						sampletext VARCHAR(255),
						sampleint INT
					)
				')
		 *
		 * Initialization:
			$db=pdo_connect(
				'./pathTo/yourDatabaseConfigDirectory',
				function($error){
					error_log('pdo_connect: '.$error->getMessage());
				}
			);
		 *   where callback is optional and is executed on PDOException
		 */

		if(!extension_loaded('PDO'))
			throw new Exception('PDO extension is not loaded');

		if(!file_exists($db.'/config.php'))
			throw new Exception('config.php not exists');

		include $db.'/config.php';

		if(!isset($db_seeded_path))
			$db_seeded_path=$db;

		try {
			switch($db_type)
			{
				case 'sqlite':
					if(!extension_loaded('pdo_sqlite'))
						throw new Exception('pdo_sqlite extension is not loaded');

					if(!file_exists($db_host))
						@unlink($db.'/database_seeded');
					$pdo_handler=new PDO('sqlite:'.$db_host);
				break;
				case 'pgsql':
					if(!extension_loaded('pdo_pgsql'))
						throw new Exception('pdo_pgsql extension is not loaded');

					if(isset($db_socket))
						$pdo_handler=new PDO(
							$db_type.':host='.$db_socket
							.';dbname='.$db_name
							.';user='.$db_user
							.';password='.$db_password
						);
					else
						$pdo_handler=new PDO(
							$db_type.':host='.$db_host
							.';port='.$db_port
							.';dbname='.$db_name
							.';user='.$db_user
							.';password='.$db_password
						);
				break;
				case 'mysql':
					if(!extension_loaded('pdo_mysql'))
						throw new Exception('pdo_mysql extension is not loaded');

					if(isset($db_socket))
						$pdo_handler=new PDO(
							$db_type.':unix_socket='.$db_socket
							.';dbname='.$db_name,
							$db_user,
							$db_password
						);
					else
						$pdo_handler=new PDO(
							$db_type.':host='.$db_host
							.';port='.$db_port
							.';dbname='.$db_name,
							$db_user,
							$db_password
						);
				break;
			}
		} catch(PDOException $error) {
			$on_error($error);
			return false;
		}

		if((file_exists($db.'/seed.php')) && (!file_exists($db_seeded_path.'/database_seeded')))
		{
			if(file_put_contents($db_seeded_path.'/database_seed_w_test', '') === false)
				throw new Exception('could not create database_seeded file in '.$db_seeded_path);
			unlink($db_seeded_path.'/database_seed_w_test');

			include $db.'/seed.php';
			file_put_contents($db_seeded_path.'/database_seeded', '');
		}

		return $pdo_handler;
	}
?>