<?php
	class pdo_migrate_exception extends Exception {}
	function pdo_migrate(array $params)
	{
		/*
		 * Database migration helper
		 *
		 * Warning:
		 *  the order of scripts in the directory corresponds to the id number in the table
		 *  if you disturb the order of already migrated scripts, you may have a big problem
		 *  use appropriate script naming with date and time, e.g. yyyy-mm-dd_hh-mm-ss_create-table-sth.php
		 *  migrations that have already been applied will be skipped
		 *
		 * Note:
		 *  throws an pdo_migrate_exception on error
		 *  may throw PDOException depending on PDO::ATTR_ERRMODE
		 *  if an error occurs in the apply mode
		 *   the failed migration will automatically go into rollback mode
		 *   and then the migration will be aborted
		 *
		 * Hint:
		 *  you can integrate this function with the pdo_connect.php library seeder option
		 *  for more information see pdo_connect.php library
		 *
		 * Supported databases:
		 *  PostgreSQL
		 *  MySQL
		 *  SQLite3
	 	 *
		 * Table layout:
		 *  PostgreSQL:
		 *   `id` SERIAL PRIMARY KEY
		 *   `migration` VARCHAR(255)
		 *   `failed` INTEGER
		 *  MySQL:
		 *   `id` INTEGER NOT NULL [PRIMARY KEY]
		 *   `migration` VARCHAR(255)
		 *   `failed` INTEGER
		 *  SQLite3:
		 *   `id` INTEGER PRIMARY KEY
		 *   `migration` VARCHAR(255)
		 *   `failed` INTEGER
		 *
		 * How to use:
		 *  create a directory for migration, put scripts in it and run the function
		 *  the function takes a callable defined in the migration script and runs it with the parameters $pdo_handle and $mode
		 *  $mode only accepts the values 'apply' and 'rollback'
		 *  callable returns boolean - true if successful, false if an error occurred
		 *  in case of an error during migration, flag 1 is set in the database in the failed column and the migration process is interrupted
		 *  after correcting the migration script you can try again
		 *
		 * Example migration script: 2024-10-25_16-20-30_create-table-sampletable.php
			<?php
				return function($pdo_handle, $mode)
				{
					try {
						switch($mode)
						{
							case 'apply':
								$result=$pdo_handle->exec(''
								.	'CREATE TABLE sampletable'
								.	'('
								.		'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,'
								.		'samplecolumn TEXT'
								.	')'
								);
							break;
							case 'rollback':
								$result=$pdo_handle->exec(
									'DROP TABLE IF EXISTS sampletable'
								);
						}

						if($result === false)
							return false;
					} catch(PDOException $error) {
						// do not crash now - set migration failed flag and then crash
						return false;
					}

					return true;
				};
			?>
		 *
		 * Usage:
			// apply all new migrations
			pdo_migrate([
				'pdo_handle'=>new PDO('sqlite:./my-database.sqlite3'),
				'table_name'=>'migrations', // optional, default: pdo_migrate
				'directory'=>'path/to/directory/with/migrations-scripts',
				'mode'=>'apply'
			]);

			// apply 3 first migrations
			pdo_migrate([
				'pdo_handle'=>new PDO('sqlite:./my-database.sqlite3'),
				'table_name'=>'migrations', // optional, default: pdo_migrate
				'directory'=>'path/to/directory/with/migrations-scripts',
				'mode'=>'apply',
				'count'=>3
			]);

			// rollback all migrations
			pdo_migrate([
				'pdo_handle'=>new PDO('sqlite:./my-database.sqlite3'),
				'table_name'=>'migrations', // optional, default: pdo_migrate
				'directory'=>'path/to/directory/with/migrations-scripts',
				'mode'=>'rollback'
			]);

			// rollback last 3 migrations
			pdo_migrate([
				'pdo_handle'=>new PDO('sqlite:./my-database.sqlite3'),
				'table_name'=>'migrations', // optional, default: pdo_migrate
				'directory'=>'path/to/directory/with/migrations-scripts',
				'mode'=>'rollback',
				'count'=>3
			]);
		 *
		 * Callbacks:
		 *  define them in an array (must be callable) e.g.:
			pdo_migrate([
				'pdo_handle'=>new PDO('sqlite:./my-database.sqlite3'),
				'table_name'=>'migrations', // optional, default: pdo_migrate
				'directory'=>'path/to/directory/with/migrations-scripts',
				'mode'=>'apply',
				'on_begin'=>function($migration)
				{
					// show migration name
					echo ' -> '.$migration;
				},
				'on_skip'=>function($migration)
				{
					// the migration has already been performed
					echo ' [SKIP]'.PHP_EOL;
				},
				'on_error'=>function($migration)
				{
					// migration failed
					echo ' [FAIL]'.PHP_EOL;
				},
				'on_error_rollback'=>function($migration)
				{
					// show rollback migration name on error
					echo ' <- '.$migration;
				},
				'on_end'=>function($migration, $skipped)
				{
					// if not used $params['count'] option
					if(!$skipped)
						// migration completed successfully
						echo ' [ OK ]'.PHP_EOL;
				}
			]);
		 */

		foreach([
			'pdo_handle',
			'directory',
			'mode'
		] as $param)
			if(!isset($params[$param]))
				throw new pdo_migrate_exception(
					'The '.$param.' parameter was not specified'
				);

		foreach([
			'pdo_handle'=>'object',
			'table_name'=>'string',
			'directory'=>'string',
			'mode'=>'string',
			'count'=>'integer'
		] as $param=>$param_type)
			if(
				isset($params[$param]) &&
				(gettype($params[$param]) !== $param_type)
			)
				throw new pdo_migrate_exception(
					'The input array parameter '.$param.' is not a '.$param_type
				);

		foreach([
			'on_begin',
			'on_skip',
			'on_error',
			'on_error_rollback',
			'on_end'
		] as $param){
			if(!isset($params[$param]))
			{
				$params[$param]=function(){};
				continue;
			}

			if(!is_callable($params[$param]))
				throw new pdo_migrate_exception(
					'The input array parameter '.$param.' is not callable'
				);
		}

		if(
			($params['mode'] !== 'apply') &&
			($params['mode'] !== 'rollback')
		)
			throw new pdo_migrate_exception(
				'mode parameter must be apply or rollback'
			);

		if(!is_dir($params['directory']))
			throw new pdo_migrate_exception(
				$params['directory'].' is not a directory'
			);

		if(!isset($params['table_name']))
			$params['table_name']='pdo_migrate';

		if(!in_array(
			$params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME),
			['pgsql', 'mysql', 'sqlite']
		))
			throw new pdo_migrate_exception(
				$params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported'
			);

		$migrations=array_diff(
			scandir($params['directory']),
			['.', '..']
		);
		$migration_id=0;
		$i=1; // foreach

		if(isset($params['count']))
		{
			$migrations_len=count($migrations);

			if($params['count'] < 1)
				throw new app_db_migrate_exception(
					'count parameter must be greater than 0'
				);

			if($params['count'] > $migrations_len)
				throw new app_db_migrate_exception(
					'Too many migrations were given to be rolled back - there are '.$migrations_len.' of them'
				);
		}

		if($params['mode'] === 'rollback')
		{
			$migrations=array_reverse($migrations);
			$migration_id=count($migrations);
		}

		switch($params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$create_table_result=$params['pdo_handle']->exec(''
				.	'CREATE TABLE IF NOT EXISTS '.$params['table_name']
				.	'('
				.		'id SERIAL PRIMARY KEY,'
				.		'migration VARCHAR(255),'
				.		'failed INTEGER'
				.	')'
				);
			break;
			case 'mysql':
				$create_table_result=$params['pdo_handle']->exec(''
				.	'CREATE TABLE IF NOT EXISTS '.$params['table_name']
				.	'('
				.		'id INTEGER NOT NULL, PRIMARY KEY(id),'
				.		'migration VARCHAR(255),'
				.		'failed INTEGER'
				.	')'
				);
			break;
			case 'sqlite':
				$create_table_result=$params['pdo_handle']->exec(''
				.	'CREATE TABLE IF NOT EXISTS '.$params['table_name']
				.	'('
				.		'id INTEGER PRIMARY KEY,'
				.		'migration VARCHAR(255),'
				.		'failed INTEGER'
				.	')'
				);
		}

		if($create_table_result === false)
			throw new pdo_migrate_exception(
				'PDO exec error (CREATE TABLE)'
			);

		foreach($migrations as $migration_script)
		{
			$migration=pathinfo($migration_script, PATHINFO_FILENAME);

			$params['on_begin']($migration);

			if(
				isset($params['count']) &&
				($i++ > $params['count'])
			){
				$params['on_skip']($migration);
				$params['on_end']($migration, true);

				break;
			}

			switch($params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$migration_status=$params['pdo_handle']->query(''
					.	'SELECT failed '
					.	'FROM '.$params['table_name'].' '
					.	'WHERE migration='."'".$migration."'"
					);
				break;
				case 'mysql':
				case 'sqlite':
					$migration_status=$params['pdo_handle']->query(''
					.	'SELECT failed '
					.	'FROM '.$params['table_name'].' '
					.	'WHERE migration="'.$migration.'"'
					);
			}

			$migration_status=$migration_status->fetchAll(PDO::FETCH_ASSOC);

			if($params['mode'] === 'apply')
			{
				if($migration_status === false)
					throw new pdo_migrate_exception(''
					.	'PDO query error '
					.	'(SELECT FROM '.$params['table_name'].' WHERE migration='.$migration.')'
					);
			}
			else if(empty($migration_status))
			{
				$params['on_skip']($migration);
				--$migration_id;

				continue;
			}

			if(
				($params['mode'] === 'apply') &&
				isset($migration_status[0]) &&
				($migration_status[0]['failed'] == 0)
			){
				$params['on_skip']($migration);
				++$migration_id;

				continue;
			}

			$callback_function=require $params['directory'].'/'.$migration_script;

			if(!is_callable($callback_function))
				throw new pdo_migrate_exception(
					'Script did not return callable ('.$migration.')'
				);

			if($callback_function(
				$params['pdo_handle'],
				$params['mode']
			) !== true){
				$params['on_error']($migration);

				if($params['mode'] === 'apply')
				{
					$params['on_error_rollback']($migration);
					$callback_function(
						$params['pdo_handle'],
						'rollback'
					);
				}

				switch($params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						$params['pdo_handle']->exec(''
						.	'INSERT INTO '.$params['table_name']
						.	'('
						.		'id,'
						.		'migration,'
						.		'failed'
						.	') VALUES ('
						.		(($params['mode'] === 'rollback') ? --$migration_id : ++$migration_id).','
						.		"'".$migration."',"
						.		'1'
						.	')'
						.	'ON CONFLICT(id) DO UPDATE SET '
						.		'failed=1'
						);
					break;
					case 'mysql':
					case 'sqlite':
						$params['pdo_handle']->exec(''
						.	'REPLACE INTO '.$params['table_name']
						.	'('
						.		'id,'
						.		'migration,'
						.		'failed'
						.	') VALUES ('
						.		(($params['mode'] === 'rollback') ? --$migration_id : ++$migration_id).','
						.		'"'.$migration.'",'
						.		'1'
						.	')'
						);
				}

				$params['on_end']($migration, false);

				throw new pdo_migrate_exception(
					'Migration failed ('.$migration.')'
				);
			}

			switch($params['mode'])
			{
				case 'apply';
					switch($params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME))
					{
						case 'pgsql':
							$meta_save_result=$params['pdo_handle']->exec(''
							.	'INSERT INTO '.$params['table_name']
							.	'('
							.		'id,'
							.		'migration,'
							.		'failed'
							.	') VALUES ('
							.		++$migration_id.','
							.		"'".$migration."',"
							.		'0'
							.	')'
							.	'ON CONFLICT(id) DO UPDATE SET '
							.		'failed=0'
							);
						break;
						case 'mysql':
						case 'sqlite':
							$meta_save_result=$params['pdo_handle']->exec(''
							.	'REPLACE INTO '.$params['table_name']
							.	'('
							.		'id,'
							.		'migration,'
							.		'failed'
							.	') VALUES ('
							.		++$migration_id.','
							.		'"'.$migration.'",'
							.		'0'
							.	')'
							);
					}
				break;
				case 'rollback':
					switch($params['pdo_handle']->getAttribute(PDO::ATTR_DRIVER_NAME))
					{
						case 'pgsql':
							$meta_save_result=$params['pdo_handle']->exec(''
							.	'DELETE FROM '.$params['table_name'].' '
							.	'WHERE migration='."'".$migration."'"
							);
						break;
						case 'mysql':
						case 'sqlite':
							$meta_save_result=$params['pdo_handle']->exec(''
							.	'DELETE FROM '.$params['table_name'].' '
							.	'WHERE migration="'.$migration.'"'
							);
					}
			}

			if($meta_save_result === false)
				throw new pdo_migrate_exception(''
				.	'PDO exec error '
				.	'(INSERT INTO '.$params['table_name'].')'
				);

			$params['on_end']($migration, false);
		}
	}
?>