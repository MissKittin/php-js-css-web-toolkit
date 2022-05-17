<?php
	/*
	 * PDO Cheater - use the table as an object
	 *
	 * Warning:
	 *  all classes are interdependent
	 *  this is alpha version - use at your own risk
	 *  this library is not idiot-proof
	 *
	 * Usage/Examples:
	 *  Creating the pdo_cheat handler:
			$my_table=new pdo_cheat(array(
				'pdo_handler'=>new PDO('sqlite:./pdo_cheat.sqlite3'),
				'table_name'=>'my_table'
			))
	 *  Creating the pdo_cheat handler with the fixed table schema option (recommended, see note below):
			$my_table=new pdo_cheat(array(
				'pdo_handler'=>new PDO('sqlite:./pdo_cheat.sqlite3'),
				'table_name'=>'my_table',
				'table_schema'=>['id', 'name', 'surname', 'personal_id']
			))
	 *  Creating the pdo_cheat handler with automatic table creation (for testing purposes, see note below):
			$my_table=new pdo_cheat(array(
				'pdo_handler'=>new PDO('sqlite:./pdo_cheat.sqlite3'),
				'table_name'=>'my_table',
				'new_table_schema'=>array(
					'id'=>pdo_cheat::default_id_type,
					'name'=>'VARCHAR(30)',
					'surname'=>'VARCHAR(30)',
					'personal_id'=>'INTEGER'
				)
			))
	 *  Create a table (alternative method):
			$my_table->new_table()
				->id(pdo_cheat::default_id_type)
				->name('VARCHAR(30)')
				->surname('VARCHAR(30)')
				->personal_id('INTEGER')
				->save_table()
	 *  Create a new row:
			$my_table->new_row()
				->name('Test')
				->surname('tseT')
				->personal_id(20)
				->save_row()
	 *  Reading the row (first result):
			$test_person=$my_table->get_row()
				->select_id()
				// and
				->select_personal_id()
				->get_row_by_name('Test')
				->get_row()
	 *  Cell reading:
			$value=$test_person->personal_id()
	 *  Dump a row from memory:
			$test_person->dump_row()
	 *  Reading the row (second result) and editing the row:
			$test_mod=$my_table->get_row();
			$test_mod
				->get_row_by_name('Test')
				// and
				->get_row_by_surname('tseT')
				->get_row();
			$test_mod=$test_mod->get_next_row();
			if($test_mod !== false)
			{
				$test_mod
					->personal_id(40)
					->save_row();
			}
	 *  Delete a row:
			$my_table->delete_row()
				->name('Test')
				// and
				->surname('tseT')
				->delete_row()
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

	abstract class pdo_cheat__exec
	{
		protected $pdo_handler;
		protected $table_name;

		public function pdo_cheat_exec__construct($pdo_handler, $table_name)
		{
			$this->pdo_handler=$pdo_handler;
			$this->table_name=$table_name;
		}

		protected function exec($statement)
		{
			return $this->pdo_handler->exec($statement);
		}
		protected function exec_prepared($statement, $parameters)
		{
			$result=$this->pdo_handler->prepare($statement);
			if($result === false)
				return false;

			$result=$result->execute($parameters);
			if($result === false)
				return false;

			return true;
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
	}

	class pdo_cheat extends pdo_cheat__exec
	{
		const default_id_type='INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';

		protected $table_schema=array();

		public function __construct(array $params)
		{
			foreach(['pdo_handler', 'table_name'] as $param)
				if(!isset($params[$param]))
					throw new Exception('the '.$param.' parameter was not specified for the constructor');

			foreach(['pdo_handler', 'table_name'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			if(isset($params['table_schema']))
				foreach($params['table_schema'] as $column_name)
					$this->table_schema[$column_name]=$column_name;
			else
			{
				$table_schema=$this->query('SELECT * FROM '.$this->table_name.' LIMIT 1');
				if(($table_schema === false) || (!isset($table_schema[0])))
				{
					if(isset($params['new_table_schema']))
					{
						$table_schema='';
						foreach($params['new_table_schema'] as $column_name=>$column_type)
							$table_schema.=$column_name.' '.$column_type.', ';
						$table_schema=substr($table_schema, 0, -2);

						if($this->exec('CREATE TABLE IF NOT EXISTS '.$this->table_name.'('.$table_schema.')') === false)
							throw new Exception('unable to create table');

						$this->_pdo_cheat__save_table_schema($params['table_schema']);
					}
				}
				else
					if(isset($table_schema[0]))
						$this->_pdo_cheat__save_table_schema($table_schema[0]);
			}
		}

		public function dump_table(int $limit=null, int $limit_offset=null)
		{
			if($limit === null)
				return $this->query('SELECT * FROM '.$this->table_name);

			if($limit_offset === null)
				return $this->query('SELECT * FROM '.$this->table_name.' LIMIT '.$limit);

			return $this->query('SELECT * FROM '.$this->table_name.' LIMIT '.$limit.' OFFSET '.$limit_offset);
		}
		public function dump_schema()
		{
			return $this->table_schema;
		}
		public function pdo_error_info()
		{
			return $this->pdo_handler->errorInfo();
		}

		public function new_table()
		{
			return new pdo_cheat__new_table(
				$this,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function clear_table()
		{
			return new pdo_cheat__clear_table(
				$this,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function new_row()
		{
			return new pdo_cheat__new_row(
				$this,
				$this->pdo_handler,
				$this->table_name,
				$this->table_schema
			);
		}
		public function get_row()
		{
			return new pdo_cheat__get_row(
				$this,
				$this->table_schema,
				$this->pdo_handler,
				$this->table_name
			);
		}
		public function delete_row()
		{
			return new pdo_cheat__delete_row(
				$this,
				$this->table_schema,
				$this->pdo_handler,
				$this->table_name
			);
		}

		public function _pdo_cheat__clear_table_schema()
		{
			$this->table_schema=array();
		}
		public function _pdo_cheat__save_table_schema($table_schema)
		{
			foreach($table_schema as $column_name=>$a)
				$this->table_schema[$column_name]=$column_name;
		}
	}

	class pdo_cheat__new_table extends pdo_cheat__exec
	{
		protected $pdo_cheat;
		protected $table_schema=array();

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
				throw new Exception('no column type defined for '.$column_name);

			$this->table_schema[$column_name]=$column_type[0];
			return $this;
		}

		public function save_table()
		{
			$statement='';
			foreach($this->table_schema as $column_name=>$column_type)
				$statement.=$column_name.' '.$column_type.', ';
			$statement=substr($statement, 0, -2);

			$result=$this->exec('CREATE TABLE IF NOT EXISTS '.$this->table_name.'('.$statement.')');
			if($result === false)
				return false;

			$this->pdo_cheat->_pdo_cheat__save_table_schema($this->table_schema);
			return true;
		}
	}
	class pdo_cheat__clear_table extends pdo_cheat__exec
	{
		protected $pdo_cheat;

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
			$result=$this->exec('DROP TABLE '.$this->table_name);
			if($result === false)
				return false;
			
			$this->pdo_cheat->_pdo_cheat__clear_table_schema();
			return true;
		}
	}
	class pdo_cheat__get_row extends pdo_cheat__exec
	{
		protected $selected_columns=array();
		protected $query_conditions=array();
		protected $pdo_query=null;

		protected $pdo_cheat;
		protected $table_schema;

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
					throw new Exception('no column was provided');

				$column_name=substr($column_name, 11);
				if($column_name === '')
					throw new Exception('get_row_by_() ???');

				$this->query_conditions[$column_name]=$value[0];
				return $this;
			}
			else if(substr($column_name, 0, 7) === 'select_')
			{
				$column_name=substr($column_name, 7);
				if($column_name === '')
					throw new Exception('select_() ???');

				$this->selected_columns[$column_name]=$column_name;
				return $this;
			}
		}

		public function get_row()
		{
			if($this->pdo_query !== null)
				throw new Exception('get_row() has been executed');
			if(empty($this->query_conditions))
				throw new Exception('get_row_by conditions not defined');

			$statement='';
			$parameters=array();
			foreach($this->query_conditions as $column_name=>$value)
			{
				$statement.=$column_name.'=? AND ';
				$parameters[]=$value;
			}
			$statement=substr($statement, 0, -5);

			if(empty($this->selected_columns))
				$selected_columns='*';
			else
			{
				$selected_columns='';
				foreach($this->selected_columns as $column_name)
					$selected_columns.=$column_name.',';
				$selected_columns=substr($selected_columns, 0, -1);
			}

			$this->pdo_query=$this->query_prepared(
				'SELECT '.$selected_columns.' FROM '.$this->table_name.' WHERE '.$statement,
				$parameters
			);
			if($this->pdo_query === false)
				return false;

			return $this->get_next_row();
		}
		public function get_next_row()
		{
			if($this->pdo_query === null)
				throw new Exception('get_row() did not executed');

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
	class pdo_cheat__delete_row extends pdo_cheat__exec
	{
		protected $pdo_cheat;
		protected $table_schema;
		protected $query_conditions=array();

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
				throw new Exception('no value was provided for column '.$column_name);
			if(!isset($this->table_schema[$column_name]))
				throw new Exception('The '.$column_name.' column does not exist');

			$this->query_conditions[$column_name]=$value[0];
			return $this;
		}

		public function delete_row()
		{
			if(empty($this->query_conditions))
				throw new Exception('conditions not specified');

			$statement='';
			$parameters=array();
			foreach($this->query_conditions as $column_name=>$value)
			{
				$statement.=$column_name.'=? AND ';
				$parameters[]=$value;
			}
			$statement=substr($statement, 0, -5);

			$this->query_conditions=array();

			return $this->exec_prepared(
				'DELETE FROM '.$this->table_name.' WHERE '.$statement,
				$parameters
			);
		}
	}
	class pdo_cheat__new_row extends pdo_cheat__exec
	{
		protected $pdo_cheat;
		protected $table_row;
		protected $table_schema;

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
				throw new Exception($column_name.' column is not defined in the table schema');

			if(isset($value[0]))
			{
				$this->table_row[$column_name]=$value[0];
				return $this;
			}
			else
			{
				if(isset($this->table_row[$column_name]))
					return $this->table_row[$column_name];
				return null;
			}
		}

		public function dump_row()
		{
			return $this->table_row;
		}
		public function save_row()
		{
			$columns='';
			$values='';
			$parameters=array();
			foreach($this->table_row as $column_name=>$value)
			{
				$columns.=$column_name.', ';
				$values.='?, ';
				$parameters[]=$value;
			}
			$columns=substr($columns, 0, -2);
			$values=substr($values, 0, -2);

			$this->table_row=array();

			return $this->exec_prepared(
				'REPLACE INTO '.$this->table_name.'('.$columns.') VALUES('.$values.')',
				$parameters
			);
		}
	}
	class pdo_cheat__existing_row extends pdo_cheat__new_row
	{
		public function __construct(
			pdo_cheat $pdo_cheat,
			$pdo_handler,
			$table_name,
			$table_schema,
			$current_row
		){
			parent::__construct(
				$pdo_cheat,
				$pdo_handler,
				$table_name,
				$table_schema
			);

			$this->table_row=$current_row;
		}
	}
?>