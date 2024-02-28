<?php
	/*
	 * SQLite3 database dumpers
	 *
	 * Functions:
	 *  sqlite3_db_dump
	 *   original version
	 *   SQLite3 class is required
	 *  sqlite3_pdo_dump
	 *   PDO version
	 *   PDO extension is required
	 *   pdo_sqlite extension is required
	 */

	class sqlite3_db_dump_exception extends Exception {}
	function sqlite3_db_dump(string $file)
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
		 * Warning:
		 *  SQLite3 class is required
		 *
		 * Note:
		 *  throws an sqlite3_db_dump_exception on error
		 *
		 * Source:
		 *  https://github.com/ephestione/php-sqlite-dump/blob/master/sqlite_dump.php
		 */

		if(!class_exists('SQLite3'))
			throw new sqlite3_db_dump_exception('SQLite3 class not found');

		if(!file_exists($file))
			return false;

		$db=new SQLite3($file);
		$db->busyTimeout(5000);

		$sql='';
		$tables=$db->query('SELECT name FROM sqlite_master WHERE type ="table" AND name NOT LIKE "sqlite_%"');

		if($tables === false)
			return false;

		while($table=$tables->fetchArray(SQLITE3_NUM))
		{
			// CREATE TABLE
			$sql.=$db->querySingle('SELECT sql FROM sqlite_master WHERE name="'.$table[0].'"').';'."\n";

			// INSERT INTO
			$sql.='INSERT INTO '.$table[0].'(';
				$columns=$db->query('PRAGMA table_info('.$table[0].')');
				$fieldnames=[];
				while($column=$columns->fetchArray(SQLITE3_ASSOC))
					$fieldnames[]=$column['name'];
				$sql.=implode(',', $fieldnames).') VALUES';

				$rows=$db->query('SELECT * FROM '.$table[0]);
				while($row=$rows->fetchArray(SQLITE3_ASSOC))
				{
					foreach($row as $k=>$v)
						$row[$k]='"'.str_replace('"', '""', SQLite3::escapeString($v)).'"';

					$sql.="\n".'('.implode(',', $row).'),';
				}
			$sql=rtrim($sql, ',').';'."\n\n";
		}
		$sql=substr($sql, 0, -1);

		return $sql;
	}
	function sqlite3_pdo_dump(string $file)
	{
		/*
		 * SQLite3 database dumper
		 * Quickly read database content
		 * PDO version based on Ephestione's original
		 * For debugging purposes
		 *
		 * Warning:
		 *  PDO extension is required
		 *  pdo_sqlite extension is required
		 *
		 * Note:
		 *  throws an sqlite3_db_dump_exception on error
		 */

		foreach(['PDO', 'pdo_sqlite'] as $extension)
			if(!extension_loaded($extension))
				throw new sqlite3_db_dump_exception($extension.' extension is not loaded');

		if(!file_exists($file))
			return false;

		$db=new PDO('sqlite:'.$file);

		$sql='';
		$tables=$db->query('SELECT name FROM sqlite_master WHERE type ="table" AND name NOT LIKE "sqlite_%"');

		if($tables === false)
			return false;

		while($table=$tables->fetch(PDO::FETCH_NUM))
		{
			// CREATE TABLE
			$sql.=$db->query('SELECT sql FROM sqlite_master WHERE name="'.$table[0].'"')->fetch(PDO::FETCH_NUM)[0].';'."\n";

			// INSERT INTO
			$sql.='INSERT INTO '.$table[0].'(';
				$columns=$db->query('PRAGMA table_info('.$table[0].')');
				$fieldnames=[];
				while($column=$columns->fetch(PDO::FETCH_ASSOC))
					$fieldnames[]=$column['name'];
				$sql.=implode(',', $fieldnames).') VALUES';

				$rows=$db->query('SELECT * FROM '.$table[0]);
				while($row=$rows->fetch(PDO::FETCH_ASSOC))
				{
					foreach($row as $k=>$v)
						$row[$k]='"'.str_replace('"', '""', $v).'"';

					$sql.="\n".'('.implode(',', $row).'),';
				}
			$sql=rtrim($sql, ',').';'."\n\n";
		}
		$sql=substr($sql, 0, -1);

		return $sql;
	}
?>