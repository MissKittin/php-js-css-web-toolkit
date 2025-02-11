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

	function sqlite3_db_dump($sqlite_handle)
	{
		/*
		 * Ephestione's SQLite3 database dumper
		 * Quickly read database content
		 * Original version
		 * For debugging purposes
		 *
		 * Modifications:
		 *  fixed bug with escaping " on INSERT INTO VALUES ()
		 *   see https://www.php.net/manual/en/sqlite3.escapestring.php
		 *  added support for indexes, views and triggers
		 *
		 * Note:
		 *  throws an sqlite3_db_dump_exception on error
		 *
		 * Warning:
		 *  sqlite3_db_dump_main function is required
		 *
		 * Usage:
			file_put_contents('database.sql', sqlite3_db_dump(
				new SQLite3('database.sqlite3')
			));
		 *
		 * Source: https://github.com/ephestione/php-sqlite-dump/blob/master/sqlite_dump.php
		 */

		return sqlite3_db_dump_main(
			$sqlite_handle,
			'sqlite_handle', 'SQLite3',
			function($sqlite_query)
			{
				return $sqlite_query->fetchArray(SQLITE3_ASSOC);
			}
		);
	}
	function sqlite3_pdo_dump($pdo_handle)
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
		 * Warning:
		 *  sqlite3_db_dump_main function is required
		 *
		 * Usage:
			file_put_contents('database.sql', sqlite3_pdo_dump(
				new PDO('sqlite:database.sqlite3')
			));
		 */

		return sqlite3_db_dump_main(
			$pdo_handle,
			'pdo_handle', 'PDO',
			function($pdo_query)
			{
				return $pdo_query->fetch(PDO::FETCH_ASSOC);
			}
		);
	}
	function sqlite3_db_dump_main(
		$db_handle,
		$param_name, $object_name,
		$fetch_callback
	){
		/*
		 * This is a core function
		 * Do not use it directly
		 * Use sqlite3_db_dump or sqlite3_pdo_dump instead
		 */

		if(!is_object($db_handle))
			throw new sqlite3_db_dump_exception(
				$param_name.' must be an instance of '.$object_name
			);

		$original_busy_timeout=$fetch_callback(
			$db_handle->query('PRAGMA busy_timeout')
		)['timeout'];

		$db_handle->exec('PRAGMA busy_timeout=5000');

		$sql='';
		$tables=$db_handle->query(''
		.	'SELECT name,type,sql '
		.	'FROM sqlite_master '
		.	'WHERE'
		.	'('
		.		'type="table" OR '
		.		'type="index" OR '
		.		'type="view" OR '
		.		'type="trigger"'
		.	')'
		.		'AND name NOT LIKE "sqlite_%"'
		);

		if($tables === false)
		{
			$db_handle->exec('PRAGMA busy_timeout='.$original_busy_timeout);
			return false;
		}

		while(
			//$table=$tables->fetchArray(SQLITE3_ASSOC) // sqlite3
			//$table=$tables->fetch(PDO::FETCH_ASSOC) // pdo
			$table=$fetch_callback($tables)
		){
			// CREATE TABLE || CREATE INDEX || CREATE VIEW || CREATE TRIGGER
			$sql.=$table['sql'].';'."\n";

			// CREATE INDEX || CREATE VIEW || CREATE TRIGGER
			if(
				($table['type'] === 'index') ||
				($table['type'] === 'view') ||
				($table['type'] === 'trigger')
			)
				continue;

			// INSERT INTO
			$sql.='INSERT INTO '.$table['name'].'(';
				$columns=$db_handle->query(''
				.	'PRAGMA table_info'
				.	'('.$table['name'].')'
				);
				$fieldnames=[];

				while(
					//$column=$columns->fetchArray(SQLITE3_ASSOC) // sqlite3
					//$column=$columns->fetch(PDO::FETCH_ASSOC) // pdo
					$column=$fetch_callback($columns)
				)
					$fieldnames[]=$column['name'];

				$sql.=''
				.	implode(',', $fieldnames)
				.	') VALUES';

				$rows=$db_handle->query(''
				.	'SELECT * '
				.	'FROM '.$table['name']
				);

				while(
					//$row=$rows->fetchArray(SQLITE3_ASSOC) // sqlite3
					//$row=$rows->fetch(PDO::FETCH_ASSOC) // pdo
					$row=$fetch_callback($rows)
				){
					foreach($row as $k=>$v)
						$row[$k]=''
						.	'"'
						.		str_replace(
									'"',
									'""',
									$v
								)
						.	'"';

					$sql.=''
					.	"\n"
					.	'('.implode(',', $row).'),';
				}

			$sql=''
			.	rtrim($sql, ',').';'
			.	"\n\n";
		}

		$db_handle->exec('PRAGMA busy_timeout='.$original_busy_timeout);

		return substr($sql, 0, -1);
	}
?>