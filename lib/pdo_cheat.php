<?php
	/*
	 * PDO Cheater - use the table as an object
	 *
	 * Warning:
	 *  all classes are interdependent
	 *
	 * Note:
	 *  you cannot inherit these classes
	 *   use composition instead
	 *  throws an pdo_cheat_exception on error
	 *
	 * Supported databases:
	 *  PostgreSQL
	 *  MySQL
	 *  SQLite3
	 *
	 * Usage/Examples:
	 *  Creating the pdo_cheat handler:
			$my_table=new pdo_cheat([
				'pdo_handler'=>new PDO('sqlite:./pdo_cheat.sqlite3'),
				'table_name'=>'my_table'
			])
	 *  Creating the pdo_cheat handler with the fixed table schema option (recommended, see note below):
			$my_table=new pdo_cheat([
				'pdo_handler'=>new PDO('sqlite:./pdo_cheat.sqlite3'),
				'table_name'=>'my_table',
				'table_schema'=>['id', 'name', 'surname', 'personal_id']
			])
	 *  Creating the pdo_cheat handler with automatic table creation (for testing purposes, see note below):
			$my_table=new pdo_cheat([
				'pdo_handler'=>new PDO('sqlite:./pdo_cheat.sqlite3'),
				'table_name'=>'my_table',
				'new_table_schema'=>[
					'id'=>pdo_cheat::default_id_type,
					'name'=>'VARCHAR(30)',
					'surname'=>'VARCHAR(30)',
					'personal_id'=>'INTEGER'
				]
			])
	 *  Create a table (alternative method):
			$my_table->new_table()
			->	id(pdo_cheat::default_id_type)
			->	name('VARCHAR(30)')
			->	surname('VARCHAR(30)')
			->	personal_id('INTEGER')
			->	save_table()
	 *  Alter the table (if_exists() is optional):
	 *   note: after using this, the cheater instance is invalidated
	 *    you need to initialize the cheater again
			$my_table->alter_table()->if_exists()
			->	add_example_name('INTEGER')
			$my_table->alter_table()->if_exists()
			->	drop_example_name() // with SQLite3, the operation may take longer
			$my_table->alter_table()->if_exists()
			->	rename_from_example_name()
			->	rename_to_new_name() // with SQLite3, the operation may take longer
			$my_table->alter_table()->if_exists()
			->	modify_example_name('VARCHAR(30)') // with SQLite3, the operation may take longer
			$my_table->alter_table()->if_exists()
			->	rename_table('newname')
	 *  Create a new row:
			$my_table->new_row()
			->	name('Test')
			->	surname('tseT')
			->	personal_id(20)
			->	save_row()
	 *  Reading the row (first result):
			$test_person=$my_table->get_row()
			->	select_id()
			//	// and
			->	select_personal_id()
			->	get_row_by_name('Test')
			->	get_row()
	 *  Cell reading:
			$value=$test_person->personal_id()
	 *  Dump a row from memory:
			$test_person->dump_row()
	 *  Reading the row (second result) and editing the row:
			$test_mod=$my_table->get_row();
			$test_mod
			->	get_row_by_name('Test')
			//	// and
			->	get_row_by_surname('tseT')
			->	get_row();
			$test_mod=$test_mod->get_next_row();
			if($test_mod !== false)
				$test_mod
				->	personal_id(40)
				->	save_row();
	 *  Delete a row:
			$my_table->delete_row()
			->	name('Test')
			//	// and
			->	surname('tseT')
			->	delete_row()
	 *  Clearing the table (does not reset the id counter):
			$my_table->clear_table()->flush_table()
	 *  Dropping a table:
			$my_table->clear_table()->drop_table()
	 *  Dump all table content (returns an array or false):
			$my_table->dump_table([int_LIMIT, int_OFFSET]) // [] here means optional
	 *  Dump table schema (returns an array):
			$my_table->dump_schema()
	 *  Reading the last PDO error:
			$my_table->pdo_error_info();
	 *
	 * Note:
	 *  if cell is empty, unset or unselected, the row getter returns null
	 *  get_row() and get_next_row() return false on failure
	 *  in pdo_cheat::__construct() table_schema has priority over new_table_schema
	 *   and if table_schema is passed, CREATE TABLE will not be executed
	 *   use new_table_schema or pdo_cheat::new_table() if you don't know if the table exists
	 *
	 * Classes:
	 *  pdo_cheat -> main controller
	 *  pdo_cheat__exec -> handling PDO queries
	 *  pdo_cheat__new_table -> create a table
	 *  pdo_cheat__alter_table -> alter the table
	 *  pdo_cheat__clear_table -> dropping table
	 *  pdo_cheat__get_row -> row selection
	 *  pdo_cheat__delete_row -> delete row
	 *  pdo_cheat__new_row -> new row's getters/setters
	 *  pdo_cheat__existing_row -> existing row's getters/setters
	 *
	 * Roadmap:
	 *  new_table() -> pdo_cheat__new_table
	 *  	__call() // column_name(column_type)
	 *  	save_table() [returns bool]
	 *  alter_table() -> pdo_cheat__alter_table
	 *  	if_exists() [returns pdo_cheat__alter_table]
	 *  	__call()
	 *  		// add_ () [returns bool]
	 *  		// drop_ [returns bool]
	 *  		// rename_from_ [returns pdo_cheat__alter_table]
	 *  		// rename_to [returns bool]
	 *  		// modify_ () [returns bool]
	 *  	rename_table() [returns bool]
	 *  clear_table() -> pdo_cheat__clear_table
	 *  	flush_table() [returns bool]
	 *  	drop_table() [returns bool]
	 *  new_row() -> pdo_cheat__new_row
	 *  	__call() // column_name(value) [returns pdo_cheat__existing_row] or column_name() [returns string|null]
	 *  	save_row() [returns bool]
	 *  get_row() -> pdo_cheat__get_row
	 *  	__call() // get_row_by_something() or select_columnname()
	 *  	get_row() -> pdo_cheat__existing_row or false
	 *  		__call() // column_name(value) [returns pdo_cheat__existing_row] or column_name() [returns string|null]
	 *  		save_row() [returns bool]
	 *  	get_next_row() -> pdo_cheat__existing_row or false
	 *  		__call() // column_name(value) [returns pdo_cheat__existing_row] or column_name() [returns string|null]
	 *  		save_row() [returns bool]
	 *  delete_row() -> pdo_cheat__delete_row
	 *  	__call() // column_name(value)
	 *  	delete_row() [returns bool]
	 */

	class pdo_cheat_exception extends Exception {}

	abstract class pdo_cheat__exec
	{
		protected $pdo_handler;
		protected $table_name;

		protected function exec($statement)
		{
			return $this->pdo_handler->exec($statement);
		}
		protected function exec_prepared($statement, $parameters)
		{
			$result=$this->pdo_handler->prepare($statement);

			if($result === false)
				return false;

			return $result->execute($parameters);
		}
		protected function query($statement)
		{
			$result=$this->pdo_handler->query($statement);

			if($result === false)
				return false;

			return $result->fetchAll(PDO::FETCH_NAMED);
		}
		protected function query_prepared($statement, $parameters)
		{
			$result=$this->pdo_handler->prepare($statement);

			if($result === false)
				return false;

			$result->execute($parameters);

			return $result;
		}
		protected function pdo_cheat_exec__construct($pdo_handler, $table_name)
		{
			$this->pdo_handler=$pdo_handler;
			$this->table_name=$table_name;
		}
	}

	final class pdo_cheat extends pdo_cheat__exec
	{
		const default_id_type='_PDO_CHEAT__DEFAULT_ID_TYPE';

		private $table_schema=[];
		private $table_altered=false;

		public function __construct(array $params)
		{
			foreach([
				'pdo_handler'=>'object',
				'table_name'=>'string'
			] as $param=>$param_type){
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new pdo_cheat_exception('The input array parameter '.$param.' is not a '.$param_type);

					$this->$param=$params[$param];

					continue;
				}

				throw new pdo_cheat_exception('The '.$param.' parameter was not specified for the constructor');
			}

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new pdo_cheat_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if(isset($params['table_schema']))
			{
				if(!is_array($params['table_schema']))
					throw new pdo_cheat_exception('The input array parameter table_schema is not an array');

				foreach($params['table_schema'] as $column_name)
				{
					if(!is_string($column_name))
						throw new pdo_cheat_exception('One of the elements of the table_schema array is not a string');

					$this->table_schema[$column_name]=$column_name;
				}

				return;
			}

			$table_schema=$this->query(''
			.	'SELECT * '
			.	'FROM '.$this->table_name.' '
			.	'LIMIT 1'
			);

			if(($table_schema === false) || (!isset($table_schema[0])))
			{
				if(isset($params['new_table_schema']))
				{
					if(!is_array($params['new_table_schema']))
						throw new pdo_cheat_exception('The input array parameter new_table_schema is not an array');

					$table_schema='';

					foreach($params['new_table_schema'] as $column_name=>$column_type)
					{
						if(!is_string($column_name))
							throw new pdo_cheat_exception('One of the column name in the new_table_schema array is not a string');

						if(!is_string($column_type))
							throw new pdo_cheat_exception($column_name.' column type is not a string in the new_table_schema array');

						if($column_type === pdo_cheat::default_id_type)
							switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
							{
								case 'pgsql':
									$column_type='SERIAL PRIMARY KEY';
								break;
								case 'mysql':
									$column_type='INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY('.$column_name.')';
								break;
								case 'sqlite':
									$column_type='INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';
							}

						$table_schema.=$column_name.' '.$column_type.', ';
					}

					$table_schema=substr($table_schema, 0, -2);

					if($this->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
					.	'('
					.		$table_schema
					.	')'
					) === false)
						throw new pdo_cheat_exception('Unable to create table');

					$this->_pdo_cheat__save_table_schema($params['table_schema']);
				}

				return;
			}

			if(isset($table_schema[0]))
				$this->_pdo_cheat__save_table_schema($table_schema[0]);
		}

		public function dump_table(?int $limit=null, ?int $limit_offset=null)
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			if($limit === null)
				return $this->query(''
				.	'SELECT * '
				.	'FROM '.$this->table_name
				);

			if($limit_offset === null)
				return $this->query(''
				.	'SELECT * '
				.	'FROM '.$this->table_name.' '
				.	'LIMIT '.$limit
				);

			return $this->query(''
			.	'SELECT * '
			.	'FROM '.$this->table_name.' '
			.	'LIMIT '.$limit.' '
			.	'OFFSET '.$limit_offset
			);
		}
		public function dump_schema()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			return $this->table_schema;
		}
		public function pdo_error_info()
		{
			return $this->pdo_handler->errorInfo();
		}

		public function new_table()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			return new pdo_cheat__new_table(
				$this,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function alter_table()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			$this->table_altered=true;

			return new pdo_cheat__alter_table(
				$this,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function clear_table()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			return new pdo_cheat__clear_table(
				$this,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function new_row()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			return new pdo_cheat__new_row(
				$this,
				$this->pdo_handler,
				$this->table_name,
				$this->table_schema
			);
		}
		public function get_row()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			return new pdo_cheat__get_row(
				$this,
				$this->table_schema,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function delete_row()
		{
			if($this->table_altered)
				throw new pdo_cheat_exception('The table has been altered - this cheater instance is not valid anymore');

			return new pdo_cheat__delete_row(
				$this,
				$this->table_schema,
				$this->pdo_handler,
				$this->table_name
			);
		}

		public function _pdo_cheat__clear_table_schema()
		{
			$this->table_schema=[];
		}
		public function _pdo_cheat__save_table_schema($table_schema)
		{
			foreach($table_schema as $column_name=>$a)
				$this->table_schema[$column_name]=$column_name;
		}
	}

	final class pdo_cheat__new_table extends pdo_cheat__exec
	{
		private $pdo_cheat;
		private $table_schema=[];

		public function __construct(
			pdo_cheat $pdo_cheat,
			$pdo_handler,
			$table_name
		){
			$this->pdo_cheat_exec__construct($pdo_handler, $table_name);
			$this->pdo_cheat=$pdo_cheat;
		}
		public function __call($column_name, $column_type)
		{
			if(!isset($column_type[0]))
				throw new pdo_cheat_exception('No column type defined for '.$column_name);

			$this->table_schema[$column_name]=$column_type[0];

			return $this;
		}

		public function save_table()
		{
			$statement='';

			foreach($this->table_schema as $column_name=>$column_type)
			{
				if($column_type === pdo_cheat::default_id_type)
					switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
					{
						case 'pgsql':
							$column_type='SERIAL PRIMARY KEY';
						break;
						case 'mysql':
							$column_type='INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY('.$column_name.')';
						break;
						case 'sqlite':
							$column_type='INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';
					}

				$statement.=$column_name.' '.$column_type.', ';
			}

			$statement=substr($statement, 0, -2);

			if($this->exec(''
			.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
			.	'('
			.		$statement
			.	')'
			) === false)
				return false;

			$this->pdo_cheat->_pdo_cheat__save_table_schema($this->table_schema);

			return true;
		}
	}
	final class pdo_cheat__alter_table extends pdo_cheat__exec
	{
		private $if_exists=false;
		private $rename_from=null;
		private $pdo_query='ALTER TABLE ';

		private $pdo_cheat;

		public function __construct(
			pdo_cheat $pdo_cheat,
			$pdo_handler,
			$table_name
		){
			$this->pdo_cheat_exec__construct($pdo_handler, $table_name);
			$this->pdo_cheat=$pdo_cheat;
		}
		public function __call($column_name, $value)
		{
			if(substr($column_name, 0, 4) === 'add_')
			{
				if(!isset($value[0]))
					throw new pdo_cheat_exception('Column datatype not specified');

				$column_name=substr($column_name, 4);

				if($column_name === '')
					throw new pdo_cheat_exception('add_() ???');

				return $this->_add_column($column_name, $value[0]);
			}

			if(substr($column_name, 0, 5) === 'drop_')
			{
				$column_name=substr($column_name, 5);

				if($column_name === '')
					throw new pdo_cheat_exception('drop_() ???');

				return $this->_drop_column($column_name);
			}

			if(substr($column_name, 0, 12) === 'rename_from_')
			{
				$column_name=substr($column_name, 12);

				if($column_name === '')
					throw new pdo_cheat_exception('rename_from_() ???');

				if($this->rename_from !== null)
					throw new pdo_cheat_exception('rename_from_() has been executed');

				$this->rename_from=$column_name;

				return $this;
			}

			if(substr($column_name, 0, 10) === 'rename_to_')
			{
				$column_name=substr($column_name, 10);

				if($column_name === '')
					throw new pdo_cheat_exception('rename_from_() ???');

				if($this->rename_from === null)
					throw new pdo_cheat_exception('rename_from_() has not been executed');

				$return_value=$this->_rename_column($this->rename_from, $column_name);
				$this->rename_from=null;

				return $return_value;
			}

			if(substr($column_name, 0, 7) === 'modify_')
			{
				if(!isset($value[0]))
					throw new pdo_cheat_exception('Column datatype not specified');

				$column_name=substr($column_name, 7);

				if($column_name === '')
					throw new pdo_cheat_exception('modify_() ???');

				return $this->_modify_column($column_name, $value[0]);
			}
		}

		private function _sqlite3_helper($column_name, $data_type_or_new_name, $action)
		{
			/*
			 * A helper that allows you to bypass sqlite restrictions:
			 * 1) get information about the columns of the old table
			 * 2) rename the old table
			 * 3) create a new modified one
			 * 4) transfer data from the old to the new
			 */

			$table_columns=$this->query('PRAGMA table_info('.$this->table_name.')');

			if($table_columns === false)
				return false;

			$create_table_args='';
			$insert_into_args='';

			foreach($table_columns as $table_column)
			{
				if($table_column['name'] === $column_name)
					switch($action)
					{
						case 'drop_column':
							continue 2;
						case 'rename_column':
							$table_column['name']=$data_type_or_new_name;
						break;
						case 'modify_column':
							$table_column['type']=$data_type_or_new_name;
					}

				$create_table_args.=''
				.	$table_column['name'].' '
				.	$table_column['type'];

				if($table_column['notnull'] === '1')
					$create_table_args.=' NOT NULL';

				if($table_column['dflt_value'] !== null)
					$create_table_args.=' DEFAULT "'.str_replace('"', '""', $table_column['dflt_value']).'"';

				if($table_column['pk'] === '1')
					$create_table_args.=' PRIMARY KEY';

				$create_table_args.=',';
				$insert_into_args.=$table_column['name'].',';
			}

			$create_table_args=substr($create_table_args, 0, -1);
			$insert_into_args=substr($insert_into_args, 0, -1);

			$select_args='*';

			if($action === 'drop_column')
				$select_args=&$insert_into_args;

			$rename_table_arg='__'.$this->table_name.'__'.md5(rand());

			return $this->exec(''
			.	$this->pdo_query.$this->table_name.' RENAME TO '.$rename_table_arg.';'
			.	'CREATE TABLE '.$this->table_name.'('.$create_table_args.');'
			.	'INSERT INTO '.$this->table_name.'('.$insert_into_args.') SELECT '.$select_args.' FROM '.$rename_table_arg.';'
			.	'DROP TABLE '.$rename_table_arg.';'
			);
		}
		private function _add_column($column_name, $data_type)
		{
			if($this->exec($this->pdo_query.$this->table_name.' ADD '.$column_name.' '.$data_type) === false)
				return false;

			return true;
		}
		private function _drop_column(string $column_name)
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
				case 'mysql':
					if($this->exec($this->pdo_query.$this->table_name.' DROP COLUMN '.$column_name) === false)
						return false;
				break;
				case 'sqlite':
					if($this->_sqlite3_helper($column_name, null, 'drop_column') === false)
						return false;
			}

			return true;
		}
		private function _rename_column($old_name, $new_name)
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($this->exec($this->pdo_query.$this->table_name.' RENAME COLUMN '.$old_name.' TO '.$new_name) === false)
						return false;
				break;
				case 'mysql':
					if($this->exec($this->pdo_query.$this->table_name.' RENAME COLUMN '.$old_name.' TO '.$new_name) === false)
					{
						// for old mysqls

						$column_type=$this->query('DESCRIBE '.$this->table_name.' '.$old_name);

						if(!isset($column_type[0]['Type']))
							return false;

						if($this->exec($this->pdo_query.$this->table_name.' CHANGE '.$old_name.' '.$new_name.' '.$column_type[0]['Type']) === false)
							return false;
					}
				break;
				case 'sqlite':
					if($this->_sqlite3_helper($old_name, $new_name, 'rename_column') === false)
						return false;
			}

			return true;
		}
		private function _modify_column($column_name, $data_type)
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($this->exec($this->pdo_query.$this->table_name.' ALTER COLUMN '.$column_name.' TYPE '.$data_type) === false)
						return false;
				break;
				case 'mysql':
					if($this->exec($this->pdo_query.$this->table_name.' MODIFY COLUMN '.$column_name.' '.$data_type) === false)
						return false;
				break;
				case 'sqlite':
					if($this->_sqlite3_helper($column_name, $data_type, 'modify_column') === false)
						return false;
			}

			return true;
		}

		public function if_exists()
		{
			if(!$this->if_exists)
			{
				$this->pdo_query.='IF EXISTS ';
				$this->if_exists=true;
			}

			return $this;
		}
		public function rename_table(string $new_name)
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
				case 'sqlite':
					if($this->exec($this->pdo_query.$this->table_name.' RENAME TO '.$new_name) === false)
						return false;
				break;
				case 'mysql':
					if($this->exec($this->pdo_query.$this->table_name.' RENAME '.$new_name) === false)
						return false;
			}

			return true;
		}
	}
	final class pdo_cheat__clear_table extends pdo_cheat__exec
	{
		private $pdo_cheat;

		public function __construct(
			pdo_cheat $pdo_cheat,
			$pdo_handler,
			$table_name
		){
			$this->pdo_cheat_exec__construct($pdo_handler, $table_name);
			$this->pdo_cheat=$pdo_cheat;
		}

		public function flush_table()
		{
			if($this->exec('DELETE FROM '.$this->table_name) === false)
				return false;

			return true;
		}
		public function drop_table()
		{
			if($this->exec('DROP TABLE '.$this->table_name) === false)
				return false;

			$this->pdo_cheat->_pdo_cheat__clear_table_schema();

			return true;
		}
	}
	final class pdo_cheat__get_row extends pdo_cheat__exec
	{
		private $selected_columns=[];
		private $query_conditions=[];
		private $pdo_query=null;

		private $pdo_cheat;
		private $table_schema;

		public function __construct(
			pdo_cheat $pdo_cheat,
			$table_schema,
			$pdo_handler,
			$table_name
		){
			$this->pdo_cheat_exec__construct($pdo_handler, $table_name);

			$this->pdo_cheat=$pdo_cheat;
			$this->table_schema=$table_schema;
		}
		public function __call($column_name, $value)
		{
			if(substr($column_name, 0, 11) === 'get_row_by_')
			{
				if(!isset($value[0]))
					throw new pdo_cheat_exception('No column was provided');

				$column_name=substr($column_name, 11);

				if($column_name === '')
					throw new pdo_cheat_exception('get_row_by_() ???');

				$this->query_conditions[$column_name]=$value[0];

				return $this;
			}

			if(substr($column_name, 0, 7) === 'select_')
			{
				$column_name=substr($column_name, 7);

				if($column_name === '')
					throw new pdo_cheat_exception('select_() ???');

				$this->selected_columns[$column_name]=$column_name;

				return $this;
			}
		}

		public function get_row()
		{
			if($this->pdo_query !== null)
				throw new pdo_cheat_exception('get_row() has been executed');

			if(empty($this->query_conditions))
				throw new pdo_cheat_exception('get_row_by conditions not defined');

			$statement='';
			$parameters=[];

			foreach($this->query_conditions as $column_name=>$value)
			{
				$statement.=$column_name.'=? AND ';
				$parameters[]=$value;
			}

			$statement=substr($statement, 0, -5);
			$selected_columns='*';

			if(!empty($this->selected_columns))
			{
				$selected_columns='';

				foreach($this->selected_columns as $column_name)
					$selected_columns.=$column_name.',';

				$selected_columns=substr($selected_columns, 0, -1);
			}

			$this->pdo_query=$this->query_prepared(''
			.	' SELECT '.$selected_columns
			.	' FROM '.$this->table_name
			.	' WHERE '.$statement
			,	$parameters
			);

			if($this->pdo_query === false)
				return false;

			return $this->get_next_row();
		}
		public function get_next_row()
		{
			if($this->pdo_query === null)
				throw new pdo_cheat_exception('get_row() did not executed');

			$result=$this->pdo_query->fetch(PDO::FETCH_NAMED);

			if($result === false)
				return false;

			return new pdo_cheat__existing_row(
				$this->pdo_cheat,
				$this->pdo_handler,
				$this->table_name,
				$this->table_schema,
				$result
			);
		}
	}
	final class pdo_cheat__delete_row extends pdo_cheat__exec
	{
		private $pdo_cheat;
		private $table_schema;
		private $query_conditions=[];

		public function __construct(
			pdo_cheat $pdo_cheat,
			$table_schema,
			$pdo_handler,
			$table_name
		){
			$this->pdo_cheat_exec__construct($pdo_handler, $table_name);

			$this->pdo_cheat=$pdo_cheat;
			$this->table_schema=$table_schema;
		}
		public function __call($column_name, $value)
		{
			if(!isset($value[0]))
				throw new pdo_cheat_exception('No value was provided for column '.$column_name);

			if(!isset($this->table_schema[$column_name]))
				throw new pdo_cheat_exception('The '.$column_name.' column does not exist');

			$this->query_conditions[$column_name]=$value[0];

			return $this;
		}

		public function delete_row()
		{
			if(empty($this->query_conditions))
				throw new pdo_cheat_exception('Conditions not specified');

			$statement='';
			$parameters=[];

			foreach($this->query_conditions as $column_name=>$value)
			{
				$statement.=$column_name.'=? AND ';
				$parameters[]=$value;
			}

			$statement=substr($statement, 0, -5);
			$this->query_conditions=[];

			return $this->exec_prepared(''
			.	' DELETE FROM '.$this->table_name
			.	' WHERE '.$statement
			,	$parameters
			);
		}
	}
	class pdo_cheat__new_row extends pdo_cheat__exec
	{
		protected $new_row=true;

		private $pdo_cheat;
		protected $table_row;
		private $table_schema;

		public function __construct(
			pdo_cheat $pdo_cheat,
			$pdo_handler,
			$table_name,
			$table_schema
		){
			$this->pdo_cheat_exec__construct($pdo_handler, $table_name);

			$this->pdo_cheat=$pdo_cheat;
			$this->table_schema=$table_schema;
		}
		public function __call($column_name, $value)
		{
			if(!isset($this->table_schema[$column_name]))
				throw new pdo_cheat_exception($column_name.' column is not defined in the table schema');

			if(isset($value[0]))
			{
				$this->table_row[$column_name]=$value[0];
				return $this;
			}

			if(isset($this->table_row[$column_name]))
				return $this->table_row[$column_name];
		}

		public function dump_row()
		{
			return $this->table_row;
		}
		public function save_row()
		{
			$columns='';
			$values='';
			$parameters=[];

			if(
				(!$this->new_row) &&
				($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql')
			){
				$pgsql_first_column=null;
				$pgsql_update_columns='';
				$pgsql_update_values=[];

				foreach($this->table_row as $column_name=>$value)
				{
					if($pgsql_first_column === null)
						$pgsql_first_column=$column_name;
					else
					{
						$pgsql_update_columns.=$column_name.'=?, ';
						$pgsql_update_values[]=$value;
					}

					$columns.=$column_name.', ';
					$values.='?, ';
					$parameters[]=$value;
				}

				$pgsql_update_columns=substr($pgsql_update_columns, 0, -2);
			}
			else
				foreach($this->table_row as $column_name=>$value)
				{
					$columns.=$column_name.', ';
					$values.='?, ';
					$parameters[]=$value;
				}

			$columns=substr($columns, 0, -2);
			$values=substr($values, 0, -2);

			$this->table_row=[];

			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($this->new_row)
						return $this->exec_prepared(''
						.	' INSERT INTO '.$this->table_name
						.	' ('
						.		$columns
						.	' ) VALUES ('
						.		$values
						.	' )'
						,	$parameters
						);

					return $this->exec_prepared(''
						.' INSERT INTO '.$this->table_name
						.' ('
						.	$columns
						.' ) VALUES ('
						.	$values
						.' )'
						.' ON CONFLICT('.$pgsql_first_column.') DO UPDATE SET '
						.	$pgsql_update_columns,
						array_merge($parameters, $pgsql_update_values)
					);
				break;
				case 'mysql':
				case 'sqlite':
					return $this->exec_prepared(''
						.' REPLACE INTO '.$this->table_name
						.' ('
						.	$columns
						.' ) VALUES ('
						.	$values
						.' )',
						$parameters
					);
			}
		}
	}
	final class pdo_cheat__existing_row extends pdo_cheat__new_row
	{
		protected $new_row=false;

		public function __construct(
			pdo_cheat $pdo_cheat,
			$pdo_handler,
			$table_name,
			$table_schema,
			$current_row
		){
			parent::{__FUNCTION__}(
				$pdo_cheat,
				$pdo_handler,
				$table_name,
				$table_schema
			);

			$this->table_row=$current_row;
		}
	}
?>