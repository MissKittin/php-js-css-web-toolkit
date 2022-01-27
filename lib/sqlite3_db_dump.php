<?php
	function sqlite3_db_dump(string $file)
	{
		/*
		 * Ephestione's SQLite3 database dumper
		 * Quickly read database content
		 * For debugging purposes
		 *
		 * Fixed bug with escaping " on INSERT INTO VALUES ()
		 *  see https://www.php.net/manual/en/sqlite3.escapestring.php
		 *
		 * Source:
		 *  https://github.com/ephestione/php-sqlite-dump/blob/master/sqlite_dump.php
		 */

		if(!class_exists('SQLite3'))
			throw new Exception('SQLite3 class not found');

		if(!file_exists($file))
			return false;

		$db=new SQLite3($file);
		$db->busyTimeout(5000);

		$sql='';
		$tables=$db->query('SELECT name FROM sqlite_master WHERE type ="table" AND name NOT LIKE "sqlite_%"');
		while($table=$tables->fetchArray(SQLITE3_NUM))
		{
			// CREATE TABLE
			$sql.=$db->querySingle('SELECT sql FROM sqlite_master WHERE name="'.$table[0].'"').';'.PHP_EOL;

			// INSERT INTO
			$sql.='INSERT INTO '.$table[0].' (';
				$columns=$db->query('PRAGMA table_info('.$table[0].')');
				$fieldnames=array();
				while($column=$columns->fetchArray(SQLITE3_ASSOC))
					$fieldnames[]=$column['name'];
				$sql.=implode(',', $fieldnames).') VALUES';

				$rows=$db->query('SELECT * FROM '.$table[0]);
				while($row=$rows->fetchArray(SQLITE3_ASSOC))
				{
					foreach($row as $k=>$v)
						$row[$k]='"'.str_replace('"', '""', SQLite3::escapeString($v)).'"';
					$sql.=PHP_EOL."\t".'('.implode(',', $row).'),';
				}
			$sql=rtrim($sql, ',').';'.PHP_EOL.PHP_EOL;
		}
		$sql=substr($sql, 0, -1);

		return $sql;
	}
?>