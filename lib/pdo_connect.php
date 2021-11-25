<?php
	function pdo_connect($db, $on_error=null)
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
		 *  $db=pdo_connect('./pathTo/yourDatabaseConfigDirectory', function($error) { error_log('pdo_connect: '.$error); });
		 *   where callback is optional and is executed on PDOException
		 */

		include $db . '/config.php';

		try
		{
			switch($db_type)
			{
				case 'sqlite':
					if(!file_exists($db_host))
						@unlink($db . '/database_seeded');
					$pdo_handler=new PDO('sqlite:' . $db_host);
				break;
				case 'pgsql':
					if(isset($db_socket))
						$pdo_handler=new PDO($db_type.':host='.$db_socket . ';dbname='.$db_name . ';user='.$db_user . ';password='.$db_password);
					else
						$pdo_handler=new PDO($db_type.':host='.$db_host . ';port='.$db_port . ';dbname='.$db_name . ';user='.$db_user . ';password='.$db_password);
				break;
				case 'mysql':
					if(isset($db_socket))
						$pdo_handler=new PDO($db_type.':unix_socket=='.$db_socket . ';dbname='.$db_name, $db_user, $db_password);
					else
						$pdo_handler=new PDO($db_type.':host='.$db_host . ';port='.$db_port . ';dbname='.$db_name, $db_user, $db_password);
				break;
			}
		} catch(PDOException $error) {
			if($on_error !== null)
				$on_error($error->getMessage());
			return false;
		}

		if((file_exists($db . '/seed.php')) && (!file_exists($db . '/database_seeded')))
		{
			if(file_put_contents($db . '/database_seed_w_test', '') === false)
				throw new Exception('could not create database_seeded file');
			unlink($db . '/database_seed_w_test');

			include $db . '/seed.php';
			file_put_contents($db . '/database_seeded', '');
		}

		return $pdo_handler;
	}
?>