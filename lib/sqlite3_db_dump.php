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
		 *  added support for views and triggers
		 *
		 * Note:
		 *  throws an sqlite3_db_dump_exception on error
		 *
		 * Usage:
			file_put_contents('database.sql', sqlite3_db_dump(
				new SQLite3('database.sqlite3')
			));
		 *
		 * Source: https://github.com/ephestione/php-sqlite-dump/blob/master/sqlite_dump.php
		 */

		if(!is_object($sqlite_handle))
			throw new sqlite3_db_dump_exception(
				'sqlite_handle must be an instance of SQLite3'
			);

		$sqlite_handle->busyTimeout(5000);

		$sql='';
		$tables=$sqlite_handle->query(''
		.	'SELECT name,type,sql '
		.	'FROM sqlite_master '
		.	'WHERE'
		.	'('
		.		'type="table" OR '
		.		'type="view" OR '
		.		'type="trigger"'
		.	')'
		.		'AND name NOT LIKE "sqlite_%"'
		);

		if($tables === false)
			return false;

		while(
			$table=$tables->fetchArray(SQLITE3_ASSOC)
		){
			// CREATE TABLE || CREATE VIEW || CREATE TRIGGER
			$sql.=$table['sql'].';'."\n";

			// CREATE VIEW || CREATE TRIGGER
			if(
				($table['type'] === 'view') ||
				($table['type'] === 'trigger')
			)
				continue;

			// INSERT INTO
			$sql.='INSERT INTO '.$table['name'].'(';
				$columns=$sqlite_handle->query(''
				.	'PRAGMA table_info'
				.	'('.$table['name'].')'
				);
				$fieldnames=[];

				while(
					$column=$columns->fetchArray(SQLITE3_ASSOC)
				)
					$fieldnames[]=$column['name'];

				$sql.=''
				.	implode(',', $fieldnames)
				.	') VALUES';

				$rows=$sqlite_handle->query(''
				.	'SELECT * '
				.	'FROM '.$table['name']
				);

				while(
					$row=$rows->fetchArray(SQLITE3_ASSOC)
				){
					foreach($row as $k=>$v)
						$row[$k]=''
						.	'"'
						.		str_replace(
									'"',
									'""',
									SQLite3::escapeString($v)
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

		return substr($sql, 0, -1);
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
		 * Usage:
			file_put_contents('database.sql', sqlite3_pdo_dump(
				new PDO('sqlite:database.sqlite3')
			));
		 */

		if(!is_object($pdo_handle))
			throw new sqlite3_db_dump_exception(
				'pdo_handle must be an instance of PDO'
			);

		$sql='';
		$tables=$pdo_handle->query(''
		.	'SELECT name,type,sql '
		.	'FROM sqlite_master '
		.	'WHERE'
		.	'('
		.		'type="table" OR '
		.		'type="view" OR '
		.		'type="trigger"'
		.	')'
		.		'AND name NOT LIKE "sqlite_%"'
		);

		if($tables === false)
			return false;

		while(
			$table=$tables->fetch(PDO::FETCH_ASSOC)
		){
			// CREATE TABLE || CREATE VIEW || CREATE TRIGGER
			$sql.=$table['sql'].';'."\n";

			// CREATE VIEW || CREATE TRIGGER
			if(
				($table['type'] === 'view') ||
				($table['type'] === 'trigger')
			)
				continue;

			// INSERT INTO
			$sql.='INSERT INTO '.$table['name'].'(';
				$columns=$pdo_handle->query(''
				.	'PRAGMA table_info'
				.	'('.$table['name'].')'
				);
				$fieldnames=[];

				while(
					$column=$columns->fetch(PDO::FETCH_ASSOC)
				)
					$fieldnames[]=$column['name'];

				$sql.=''
				.	implode(',', $fieldnames)
				.	') VALUES';

				$rows=$pdo_handle->query(''
				.	'SELECT * '
				.	'FROM '.$table['name']
				);

				while(
					$row=$rows->fetch(PDO::FETCH_ASSOC)
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
			."\n\n";
		}

		return substr($sql, 0, -1);
	}
?>