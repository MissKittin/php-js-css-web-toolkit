<?php
	function pdo_connect($db, $on_error=null)
	{
		/* PDO connection class with automatic seeder
		 * Supported databases: SQLite3, PostgreSQL, MySQL
		 *
		 * Configuration:
		 *  create directory for database config files
		 *  create config.php file with:
		 *   $db_type='your-db-type'; // sqlite pgsql mysql
		 *   $db_host='server-ip-or-sqlite-db-path';
		 *   //$db_socket='/path/to/socket'; // uncomment this to use unix socket
		 *   $db_port='server-port';
		 *   $db_name='database-name';
		 *   $db_user='login';
		 *   $db_password='password';
		 *  optionally create seed.php file that will initialize database, eg:
		 *   $pdo_handler->exec('
		 *		CREATE TABLE tablename(
		 *			id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		 *			sampletext VARCHAR(255),
		 *			sampleint INT
		 *		)
		 *   ');
		 *   hint: also you can write INSERT INTO statements with default values
		 *  include this library in your code
		 *
		 * Initialization:
		 *  write in your code: $db=pdo_connect('pathTo/yourDatabaseConfigDirectory', function($error) { error_log('pdo_connect: '.$error); });
		 *   where callback function is executed on PDOException and it's optional
		 *
		 * Usage:
		 *  if connection is successfull, PDO handler will be in defined name
		 *
		 * Closing database connection:
		 *  write in your code: unset($db);
		 */

		include $db . '/config.php';

		// open
		try
		{
			switch($db_type)
			{
				case 'sqlite':
					if(!file_exists($db_host)) @unlink($db . '/database_seeded');
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
			if($on_error !== null) $on_error('pdo_connect::__construct() error: ' . $error);
			return false;
		}

		// seed
		if((file_exists($db . '/seed.php')) && (!file_exists($db . '/database_seeded')))
		{
			// test if is writable
			if(!file_put_contents($db . '/database_seed_w_test', '') === false)
				die('could not create database_seeded');
			unlink($db . '/database_seed_w_test');

			include $db . '/seed.php';
			file_put_contents($db . '/database_seeded', '');
		}

		return $pdo_handler;
	}
?>