<?php
	/*
	 * SQLite3 database dumpers
	 *
	 * Functions:
	 *  sqlite3_db_dump
	 *   original version
	 *  sqlite3_pdo_dump
	 *   PDO version
	 */

	class sqlite3_db_dump_exception extends Exception {}

	function sqlite3_db_dump($sqlite_handler)
	{
		/*
		 * Ephestione's SQLite3 database dumper
		 * Quickly read database content
		 * Original version
		 * For debugging purposes
		 *
		 * Fixed bug with escaping " on INSERT INTO VALUES ()
		 *  see https://www.php.net/manual/en/sqlite3.escapestring.php
		 *
		 * Note:
		 *  throws an sqlite3_db_dump_exception on error
		 *
		 * Usage:
		 *  file_put_contents('database.sql', sqlite3_db_dump(new SQLite3('database.sqlite3')))
		 *
		 * Source:
		 *  https://github.com/ephestione/php-sqlite-dump/blob/master/sqlite_dump.php
		 */

		if(!is_object($sqlite_handler))
			throw new sqlite3_db_dump_exception('sqlite_handler must be an object');

		$sqlite_handler->busyTimeout(5000);

		$sql='';
		$tables=$sqlite_handler->query('SELECT name FROM sqlite_master WHERE type ="table" AND name NOT LIKE "sqlite_%"');

		if($tables === false)
			return false;

		while($table=$tables->fetchArray(SQLITE3_NUM))
		{
			// CREATE TABLE
			$sql.=$sqlite_handler->querySingle('SELECT sql FROM sqlite_master WHERE name="'.$table[0].'"').';'."\n";

			// INSERT INTO
			$sql.='INSERT INTO '.$table[0].'(';
				$columns=$sqlite_handler->query('PRAGMA table_info('.$table[0].')');
				$fieldnames=[];
				while($column=$columns->fetchArray(SQLITE3_ASSOC))
					$fieldnames[]=$column['name'];
				$sql.=implode(',', $fieldnames).') VALUES';

				$rows=$sqlite_handler->query('SELECT * FROM '.$table[0]);
				while($row=$rows->fetchArray(SQLITE3_ASSOC))
				{
					foreach($row as $k=>$v)
						$row[$k]='"'.str_replace('"', '""', SQLite3::escapeString($v)).'"';

					$sql.="\n".'('.implode(',', $row).'),';
				}
			$sql=rtrim($sql, ',').';'."\n\n";
		}

		return substr($sql, 0, -1);
	}
	function sqlite3_pdo_dump($pdo_handler)
	{
		/*
		 * SQLite3 database dumper
		 * Quickly read database content
		 * PDO version based on Ephestione's original
		 * For debugging purposes
		 *
		 * Note:
		 *  throws an sqlite3_db_dump_exception on error
		 *
		 * Usage:
		 *  file_put_contents('database.sql', sqlite3_pdo_dump(new PDO('sqlite:database.sqlite3')))
		 */

		if(!is_object($pdo_handler))
			throw new sqlite3_db_dump_exception('pdo_handler must be an object');

		$sql='';
		$tables=$pdo_handler->query('SELECT name FROM sqlite_master WHERE type ="table" AND name NOT LIKE "sqlite_%"');

		if($tables === false)
			return false;

		while($table=$tables->fetch(PDO::FETCH_NUM))
		{
			// CREATE TABLE
			$sql.=$pdo_handler->query('SELECT sql FROM sqlite_master WHERE name="'.$table[0].'"')->fetch(PDO::FETCH_NUM)[0].';'."\n";

			// INSERT INTO
			$sql.='INSERT INTO '.$table[0].'(';
				$columns=$pdo_handler->query('PRAGMA table_info('.$table[0].')');
				$fieldnames=[];
				while($column=$columns->fetch(PDO::FETCH_ASSOC))
					$fieldnames[]=$column['name'];
				$sql.=implode(',', $fieldnames).') VALUES';

				$rows=$pdo_handler->query('SELECT * FROM '.$table[0]);
				while($row=$rows->fetch(PDO::FETCH_ASSOC))
				{
					foreach($row as $k=>$v)
						$row[$k]='"'.str_replace('"', '""', $v).'"';

					$sql.="\n".'('.implode(',', $row).'),';
				}
			$sql=rtrim($sql, ',').';'."\n\n";
		}

		return substr($sql, 0, -1);
	}
?>