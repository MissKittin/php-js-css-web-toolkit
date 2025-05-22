<?php
	class pdo_crud_builder_exception extends Exception {}
	class pdo_crud_builder
	{
		/*
		 * PDO CRUD builder
		 *
		 * Note:
		 *  most of the queries are parameterized to avoid sql injections
		 *  all args in [] are optional
		 *  throws an pdo_crud_builder_exception on error
		 *
		 * Initializing:
		 *  $query_builder_object=new pdo_crud_builder(params_array)
		 *   where params_array has:
				'pdo_handle'=>$pdo_object // required
				'fetch_mode'=>PDO::FETCH_NAMED // optional
				'auto_flush'=>true // flush query after exec(), optional
				'on_error'=>function($message){ error_log($message); } // error logging, optional (see examples)
		 *  note: this class does not creates connection to the database
		 *   you have to manually open connection and then pass pdo handle to the builder
		 *
		 * PDO fetch method
		 *  current fetch method is used in query() exec method
		 *  can be changed via set_fetch_method(PDO::FETCH_METHOD)
		 *  if query() is not used, this value can be passed to PDO method via get_fetch_method(), eg.
			$pdo_crud_builder=new pdo_crud_builder(new PDO(
				'sqlite:./database.sqlite3'
			));

			$result=$pdo_crud_builder
			->	select('*')
			->	from('log')
			->	where_not_like('date', '%-%-% 17:30:00')
			->	exec(true); // true tells the exec() method this is a query

			while($row=$pdo_crud_builder->fetch_row($result))
				echo ''
				.	'0: '.$row[0].', '
				.	'1: '.$row[1].', '
				.	'2: '.$row[2].', '
				.	'3: '.$row[3].', '
				.	'4: '.$row[4]
				.	PHP_EOL;
		 *
		 * Usage:
			$query_builder_object
			->	first_statement()
			->	second_statement()
			->	n_statement()
			->	execution_method()
		 *
		 *  Operations on tables:
		 *   Creating table ([] means array):
				create_table('table_name', [
					'id'=>'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL', // varies depending on database type
					'first_column_name'=>'first column type',
					'second_column_name'=>'second column type',
					'n_column_name'=>'n column type'
				])
				create_table('table_name', [
					'id'=>'INTEGER NOT NULL AUTO_INCREMENT',
					'first_column_name'=>'first column type',
					'second_column_name'=>'second column type',
					'n_column_name'=>'n column type'
				], 'id') // for MySQL - the last argument is equivalent to 'PRIMARY KEY'=>'(id)'
		 *   Altering table:
				alter_table('table_name')
				alter_table_if_exists('table_name')
				add_column('column_name', 'data_type')
				drop_column('column_name')
				rename_column('old_column_name', 'new_column_name')
				alter_column_type('column_name', 'data_type')
				alter_column('column_name', 'data_type')
				modify_column('column_name', 'data_type')
				modify('column_name', 'data_type')
				rename('new_table_name')
				rename_to('new_table_name')
		 *   Dropping table:
				drop_table('table_name')
		 *   Truncating table:
				truncate_table('table_name')
		 *
		 *  Creating/dropping indexes:
				create_index('index_name', 'table_name', ['column_a', 'column_b', 'column_n'])
				create_unique_index('index_name', 'table_name', ['column_a', 'column_b', 'column_n'])
				drop_index('index_name')
				drop_index('index_name', 'table_name') // MySQL
		 *
		 *  Creating/altering/dropping views:
				create_view('view_name')->methods...
				create_view('view_name', true)->methods... // temporary view
				create_or_replace_view('view_name')->methods...
				create_or_replace_view('view_name', true)->methods... // temporary view
				alter_view('view_name')->methods...
				alter_view('view_name', 'column_a,column_b,column_n')->methods... // MySQL
				with_check_option()
				with_local_check_option()
				with_cascaded_check_option()
				drop_view('view_name')
		 *
		 *  Operation on triggers (SQLite3, WHEN operator is not supported):
		 *   Creating trigger:
		 *    insert:
				create_insert_trigger('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_insert_trigger_before('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_insert_trigger_after('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_insert_trigger_instead_of('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
		 *    update:
				create_update_trigger('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_update_trigger_before('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_update_trigger_after('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_update_trigger_instead_of('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
		 *    delete:
				create_delete_trigger('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_delete_trigger_before('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_delete_trigger_after('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
				create_delete_trigger_instead_of('trigger_name', 'table_name')
				->	create_trigger_begin()
				->	methods...
				->	create_trigger_end()
		 *   Dropping trigger:
				drop_trigger('trigger_name')
		 *
		 *  Creating/altering/dropping types (PostgreSQL):
				create_type('type_name', [
					'first_column_name'=>'first column type',
					'second_column_name'=>'second column type',
					'n_column_name'=>'n column type'
				])
				create_type_enum('type_name', ['enum_a', 'enum_b', 'enum_n'])
				drop_type('type_name')
		 *
		 *  Creating ([] means array):
				insert_into(
					'table_name',
					'first_column_name,second_column_name,n_column_name',
					[ // if the value is null, SQL NULL will be sent unescaped
						['new_value_aa', 'new_value_ab', 'new_value_ac'],
						['new_value_ba', 'new_value_bb', 'new_value_bc'],
						['new_value_ca', 'new_value_cb', 'new_value_cc'],
						['new_value_da', 'new_value_db', 'new_value_dc']
					]
				)
		 *
		 *  Reading:
				select(string_what)
				select_top(int_how_many, string_what)
				select_top_percent(int_how_many, string_what)
				as(string_what)
				group_by(string_what)
				order_by(string_what)
				join(string_inner|left|right|full, string_what, [string_on])
				union()
				union_all()
				asc()
				desc()
				limit(int_how_many, [int_offset])
				fetch_first(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
				fetch_first_percent(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
				fetch_next(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
				fetch_next_percent(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *
		 *  Updating ([] means array):
				replace_into(
					string_table_name,
					'id,second_column_name,n_column_name',
					[ // if the value is null, SQL NULL will be sent unescaped
						['id_a', 'new_value_aa', 'new_value_ab'],
						['id_b', 'new_value_ba', 'new_value_bb'],
						['id_c', 'new_value_ca', 'new_value_cb'],
						['id_d', 'new_value_da', 'new_value_db']
					]
				)
				update(string_table_name)
				set([
					// if the string-or-null_new_value_* is null, SQL NULL will be sent unescaped
					['string_first_column_name', 'string-or-null_new_value_a'],
					['string_second_column_name', 'string-or-null_new_value_b'],
					['string_n_column_name', 'string-or-null_new_value_n']
				])
		 *
		 *  Deleting:
				delete(string_from)
		 *   note: you do not have to use from() method
		 *
		 *  Miscellaneous statements:
				output_into(string_parameters, string_into_where)
				from(string)
				cascade()
				restrict()
		 *   where statements:
				where(string_a, string_operator, string_b)
					and(string_a, string_operator, string_b)
					or(string_a, string_operator, string_b)
				where_like(string_column_name, string_sql_with_wildcards)
				where_not_like(string_column_name, string_sql_with_wildcards)
				where_is(string_a, string_what)
				where_is_null(string_a)
				where_is_not_null(string_a)
				where_not(string_a, string_operator, string_b)
		 *
		 *  Raw sql input:
		 *   raw_sql(string)
		 *    adds sql string to the sql query inside builder object
		 *   raw_parameter(string)
		 *    adds string to the array with sql parameters inside builder object
		 *
		 *  Executing:
		 *   exec([true])
		 *    executes query (true) and returns boolean or output from database server (false) (use this for eg fetch() pdo method)
		 *     note: for fetchAll() PDO method use query()
		 *   fetch_row($pdo_exec_result)
		 *    run fetch() method od PDO object after pdo_crud_builder's exec(true), eg.
		 *     $result=$pdo_crud_builder->select('*')->from('table_name')->exec(true);
		 *     while($row=$pdo_crud_builder->fetch_row($result))
		 *   query()
		 *    executes query and returns output from fetchAll() or false if failed
		 *   note: above metods automatically clears sql query string and parameter's array
		 *    if auto_flush param is true (see flush_all())
		 *   warning: if you using PDO's exec methods, you must $pdo_crud_builder->flush_all() after operation
		 *
		 *  Debugging:
		 *   error_info()
		 *    returns array with messages from PDO
		 *   flush_all()
		 *    clears sql query and parameters
		 *   print_exec([true])
		 *    returns sql query or prints it if true is passed
		 *   print_query([true])
		 *    alias to the print_exec()
		 *   print_parameters([true])
		 *    returns array with parameters or var_dump if true is passed
		 *   print_prepared()
		 *    combined print_exec and print_parameters
		 *    returns string
		 *   table_dump(table_name, [limit], [limit_offset])
		 *    runs flush_all and returns array or false
		 *   list_tables()
		 *    returns an array with table names or false
		 *    supported drivers: mysql pgsql sqlite oci dblib(SQL Server 2000)
		 *
		 * Closing connection:
		 *  $pdo_handle=$query_builder_object->pdo_disconnect()
		 *   unset PDO handle in object (returns null)
		 *  unset($query_builder_object)
		 *
		 * Examples:
		 *  initialization with SQLite3 database:
				$pdo_crud_builder=new pdo_crud_builder([
					'pdo_handle'=>new PDO('sqlite:./database.sqlite3'),
					'on_error'=>function($error) { error_log('pdo_crud_builder'.$error); }
				])
		 *  initialization with pdo_connect library:
				$pdo_crud_builder=new pdo_crud_builder([
					'pdo_handle'=>pdo_connect('pathTo/yourDatabaseConfigDirectory'),
					'on_error'=>function($error) { error_log('pdo_crud_builder'.$error); }
				])
		 *  dump all rows from the table:
				$result=$pdo_crud_builder->table_dump('log');
		 *  fetch result in while loop: see PDO fetch method
				$result=$pdo_crud_builder->select('*')->from('log')->exec(true);
				while($row=$pdo_crud_builder->fetch_row($result)) // or
				while($row=$result->fetch($pdo_crud_builder->get_fetch_method()))
		 */

		protected $pdo_handle;
		protected $fetch_mode;
		protected $on_error;
		protected $auto_flush=true;
		protected $sql_query='';
		protected $sql_parameters=[];

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handle']))
				throw new pdo_crud_builder_exception(
					'No PDO handle given'
				);

			$this->fetch_mode=PDO::FETCH_NAMED;
			$this->on_error[0]=function(){};

			foreach([
				'pdo_handle'=>'object',
				'fetch_mode'=>'integer',
				'auto_flush'=>'boolean'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new pdo_crud_builder_exception(
							'The input array parameter '.$param.' is not a '.$param_type
						);

					$this->$param=$params[$param];
				}

			if(isset($params['on_error']))
			{
				if(!is_callable($params['on_error']))
					throw new pdo_crud_builder_exception(
						'The input array parameter on_error is not callable'
					);

				$this->on_error[0]=$on_error;
			}
		}

		public function get_fetch_mode()
		{
			return $this->fetch_mode;
		}
		public function set_fetch_mode($fetch_mode)
		{
			$this->fetch_mode=$fetch_mode;
		}

		public function from(string $from)
		{
			$this->sql_query.='FROM '.$from.' ';
			return $this;
		}
		public function where(
			string $name,
			string $operator,
			string $value
		){
			$this->sql_query.='WHERE '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function where_is(string $name, string $what)
		{
			$this->sql_query.=''
			.	'WHERE '.$name.' '
			.	'IS '.$what.' ';

			return $this;
		}
		public function where_is_null(string $name)
		{
			return $this->where_is($name, 'NULL');
		}
		public function where_is_not_null(string $name)
		{
			return $this->where_is($name, 'NOT NULL');
		}
		public function where_like(string $name, string $string)
		{
			$this->sql_query.=''
			.	'WHERE '.$name.' '
			.	'LIKE ? ';

			$this->sql_parameters[]=$string;

			return $this;
		}
		public function where_not_like(string $name, string $string)
		{
			$this->sql_query.=''
			.	'WHERE '.$name.' '
			.	'NOT LIKE ? ';

			$this->sql_parameters[]=$string;

			return $this;
		}
		public function where_not(
			string $name,
			string $operator,
			string $value
		){
			$this->sql_query.='WHERE NOT '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function and(
			string $name,
			string $operator,
			string $value
		){
			$this->sql_query.='AND '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function or(
			string $name,
			string $operator,
			string $value
		){
			$this->sql_query.='OR '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function output_into(string $parameters, string $into)
		{
			$this->sql_query.=''
			.	'OUTPUT '.$parameters.' '
			.	'INTO '.$into.' ';

			return $this;
		}
		public function cascade()
		{
			$this->sql_query.='CASCADE ';
			return $this;
		}
		public function restrict()
		{
			$this->sql_query.='RESTRICT ';
			return $this;
		}

		public function create_table(
			string $table_name,
			array $columns,
			?string $mysql_primary_key=null
		){
			$sql_columns='';

			foreach($columns as $column_name=>$column_type)
			{
				if(!is_string($column_type))
					throw new pdo_crud_builder_exception(
						'Array value must be a string'
					);

				$sql_columns.=$column_name.' '.$column_type.', ';
			}

			if($mysql_primary_key !== null)
				$sql_columns.='PRIMARY KEY('.$mysql_primary_key.')';
			else
				$sql_columns=substr($sql_columns, 0, -2);

			$this->sql_query.=''
			.	'CREATE TABLE '.$table_name
			.	'('.$sql_columns.') ';

			return $this;
		}
		public function drop_table(string $table_name)
		{
			$this->sql_query.=''
			.	'DROP TABLE '
			.	'IF EXISTS '
			.	$table_name.' ';

			return $this;
		}
		public function truncate_table(string $table_name)
		{
			$this->sql_query.='TRUNCATE TABLE '.$table_name.' ';
			return $this;
		}

		public function create_index(
			string $index_name,
			string $table_name,
			array $columns
		){
			$this->sql_query.=''
			.	'CREATE INDEX IF NOT EXISTS '.$index_name.' '
			.	'ON '.$table_name
			.	'('.implode(',', $columns).') ';

			return $this;
		}
		public function create_unique_index(
			string $index_name,
			string $table_name,
			array $columns
		){
			$this->sql_query.=''
			.	'CREATE UNIQUE INDEX IF NOT EXISTS '.$index_name.' '
			.	'ON '.$table_name
			.	'('.implode(',', $columns).') ';

			return $this;
		}
		public function drop_index(
			string $index_name,
			?string $table_name=null
		){
			if($table_name === null)
			{
				$this->sql_query.='DROP INDEX IF EXISTS '.$index_name.' ';
				return $this;
			}

			$this->sql_query.=''
			.	'ALTER TABLE '.$table_name.' '
			.	'DROP INDEX '.$index_name.' ';

			return $this;
		}

		public function create_view(
			string $view_name,
			bool $temporary=false
		){
			$temporary_view_query='';

			if($temporary)
				$temporary_view_query='TEMPORARY ';

			$this->sql_query.=''
			.	'CREATE '.$temporary_view_query
			.	'VIEW IF NOT EXISTS '.$view_name.' AS ';

			return $this;
		}
		public function create_or_replace_view(
			string $view_name,
			bool $temporary=false
		){
			$temporary_view_query='';

			if($temporary)
				$temporary_view_query='TEMPORARY ';

			$this->sql_query.=''
			.	'CREATE OR REPLACE '.$temporary_view_query
			.	'VIEW '.$view_name.' AS ';

			return $this;
		}
		public function alter_view(
			string $view_name,
			?string $columns=null
		){
			$alter_columns='';

			if($columns !== null)
				$alter_columns='('.$columns.') AS';

			$this->sql_query.=''
			.	'ALTER VIEW IF EXISTS '.$view_name
			.	$alter_columns.' ';

			return $this;
		}
		public function with_check_option()
		{
			$this->sql_query.='WITH CHECK OPTION ';
			return $this;
		}
		public function with_local_check_option()
		{
			$this->sql_query.='WITH LOCAL CHECK OPTION ';
			return $this;
		}
		public function with_cascaded_check_option()
		{
			$this->sql_query.='WITH CASCADED CHECK OPTION ';
			return $this;
		}
		public function drop_view(string $view_name)
		{
			$this->sql_query.=''
			.	'DROP VIEW '
			.	'IF EXISTS '
			.	$view_name.' ';

			return $this;
		}

		public function create_insert_trigger(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'INSERT ON '.$table_name.' ';

			return $this;
		}
		public function create_insert_trigger_before(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'BEFORE '
			.	'INSERT ON '.$table_name.' ';

			return $this;
		}
		public function create_insert_trigger_after(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.='CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'AFTER '
			.	'INSERT ON '.$table_name.' ';

			return $this;
		}
		public function create_insert_trigger_instead_of(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'INSTEAD OF '
			.	'INSERT ON '.$table_name.' ';

			return $this;
		}
		public function create_update_trigger(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'UPDATE ON '.$table_name.' ';

			return $this;
		}
		public function create_update_trigger_before(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'BEFORE '
			.	'UPDATE ON '.$table_name.' ';

			return $this;
		}
		public function create_update_trigger_after(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.='CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'AFTER '
			.	'UPDATE ON '.$table_name.' ';

			return $this;
		}
		public function create_update_trigger_instead_of(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'INSTEAD OF '
			.	'UPDATE ON '.$table_name.' ';

			return $this;
		}
		public function create_delete_trigger(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'DELETE ON '.$table_name.' ';

			return $this;
		}
		public function create_delete_trigger_before(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'BEFORE '
			.	'DELETE ON '.$table_name.' ';

			return $this;
		}
		public function create_delete_trigger_after(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.='CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'AFTER '
			.	'DELETE ON '.$table_name.' ';

			return $this;
		}
		public function create_delete_trigger_instead_of(
			string $trigger_name,
			string $table_name
		){
			$this->sql_query.=''
			.	'CREATE TRIGGER IF NOT EXISTS '.$trigger_name.' '
			.	'INSTEAD OF '
			.	'DELETE ON '.$table_name.' ';

			return $this;
		}
		public function create_trigger_begin()
		{
			$this->sql_query.='BEGIN ';
			return $this;
		}
		public function create_trigger_end()
		{
			$this->sql_query.='; END ';
			return $this;
		}
		public function drop_trigger(string $trigger_name)
		{
			$this->sql_query.=''
			.	'DROP TRIGGER '
			.	'IF EXISTS '
			.	$trigger_name.' ';

			return $this;
		}

		public function create_type(string $type_name, array $columns)
		{
			$sql_columns='';

			foreach($columns as $column_name=>$column_type)
			{
				if(!is_string($column_type))
					throw new pdo_crud_builder_exception(
						'Array value must be a string'
					);

				$sql_columns.=$column_name.' '.$column_type.', ';
			}

			$sql_columns=substr($sql_columns, 0, -2);

			$this->sql_query.=''
			.	'CREATE TYPE '.$type_name.' AS'
			.	'('.$sql_columns.') ';

			return $this;
		}
		public function create_type_enum(string $type_name, array $elements)
		{
			$this->sql_query.=''
			.	'CREATE TYPE '.$type_name.' '
			.	'AS ENUM'
			.	'('.implode(',', $elements).') ';

			return $this;
		}
		public function drop_type(string $type_name)
		{
			$this->sql_query.=''
			.	'DROP TYPE '
			.	'IF EXISTS '
			.	$type_name.' ';

			return $this;
		}

		public function insert_into(
			string $where,
			string $columns,
			array $what
		){
			$sql_what='';

			foreach($what as $what_data_set)
			{
				if(!is_array($what_data_set))
					throw new pdo_crud_builder_exception(
						'The dataset must be an array'
					);

				$sql_what.='(';

				foreach($what_data_set as $what_value)
				{
					if($what_value === null)
					{
						$sql_what.='NULL,';
						continue;
					}

					$sql_what.='?,';
					$this->sql_parameters[]=$what_value;
				}

				$sql_what=substr($sql_what, 0, -1);
				$sql_what.='), ';
			}

			$sql_what=substr($sql_what, 0, -2);

			$this->sql_query.=''
			.	'INSERT INTO '.$where
			.	'('.$columns.') '
			.	'VALUES'.$sql_what.' ';

			return $this;
		}

		public function alter_table(string $table_name)
		{
			$this->sql_query.='ALTER TABLE '.$table_name.' ';
			return $this;
		}
		public function alter_table_if_exists(string $table_name)
		{
			$this->sql_query.=''
			.	'ALTER TABLE '
			.	'IF EXISTS '
			.	$table_name.' ';

			return $this;
		}
		public function add_column(string $column_name, string $data_type)
		{
			$this->sql_query.='ADD '.$column_name.' '.$data_type.' ';
			return $this;
		}
		public function drop_column(string $column_name)
		{
			$this->sql_query.='DROP COLUMN '.$column_name.' ';
			return $this;
		}
		public function rename_column(string $old_name, string $new_name)
		{
			$this->sql_query.=''
			.	'RENAME COLUMN '.$old_name.' '
			.	'TO '.$new_name.' ';

			return $this;
		}
		public function alter_column(string $column_name, string $data_type)
		{
			$this->sql_query.='ALTER COLUMN '.$column_name.' '.$data_type.' ';
			return $this;
		}
		public function alter_column_type(string $column_name, string $data_type)
		{
			$this->sql_query.=''
			.	'ALTER COLUMN '.$column_name.' '
			.	'TYPE '.$data_type.' ';

			return $this;
		}
		public function modify_column(string $column_name, string $data_type)
		{
			$this->sql_query.='MODIFY COLUMN '.$column_name.' '.$data_type.' ';
			return $this;
		}
		public function modify(string $column_name, string $data_type)
		{
			$this->sql_query.='MODIFY '.$column_name.' '.$data_type.' ';
			return $this;
		}
		public function rename(string $new_name)
		{
			$this->sql_query.='RENAME '.$new_name.' ';
			return $this;
		}
		public function rename_to(string $new_name)
		{
			$this->sql_query.='RENAME TO '.$new_name.' ';
			return $this;
		}

		public function select(string $what)
		{
			$this->sql_query.='SELECT '.$what.' ';
			return $this;
		}
		public function select_top(int $param, string $what)
		{
			$this->sql_query.='SELECT TOP '.$param.' '.$what.' ';
			return $this;
		}
		public function select_top_percent(int $param, string $what)
		{
			$this->sql_query.=''
			.	'SELECT TOP '.$param.' '
			.	'PERCENT '.$what.' ';

			return $this;
		}
		public function as(string $what)
		{
			$this->sql_query.='AS '.$what.' ';
			return $this;
		}
		public function group_by(string $what)
		{
			$this->sql_query.='GROUP BY '.$what.' ';
			return $this;
		}
		public function order_by(string $what)
		{
			$this->sql_query.='ORDER BY '.$what.' ';
			return $this;
		}
		public function join(
			string $method,
			string $what,
			?string $on=null
		){
			switch($method)
			{
				case 'inner':
					$this->sql_query.='INNER JOIN '.$what.' ';
				break;
				case 'left':
					$this->sql_query.='LEFT OUTER JOIN '.$what.' ';
				break;
				case 'right':
					$this->sql_query.='RIGHT OUTER JOIN '.$what.' ';
				break;
				case 'full':
					$this->sql_query.='FULL OUTER JOIN '.$what.' ';
				break;
				default:
					$this->on_error[0](
						'::join(): inner/left/right/full $method not specified'
					);

					return false;
			}

			if($on !== null)
				$this->sql_query.='ON '.$on.' ';

			return $this;
		}
		public function union()
		{
			$this->sql_query.='UNION ';
			return $this;
		}
		public function union_all()
		{
			$this->sql_query.='UNION ALL ';
			return $this;
		}
		public function asc()
		{
			$this->sql_query.='ASC ';
			return $this;
		}
		public function desc()
		{
			$this->sql_query.='DESC ';
			return $this;
		}
		public function limit(int $param, ?int $offset=null)
		{
			if($offset === null)
			{
				$this->sql_query.='LIMIT '.$param.' ';
				return $this;
			}

			$this->sql_query.=''
			.	'LIMIT '.$param.' '
			.	'OFFSET '.$offset.' ';

			return $this;
		}
		public function fetch_first(
			int $param,
			string $rows_param='ROWS ONLY',
			?int $offset=null,
			string $offset_param='ROWS'
		){
			if($offset === null)
			{
				$this->sql_query.='FETCH FIRST '.$param.' '.$rows_param.' ';
				return $this;
			}

			$this->sql_query.=''
			.	'OFFSET '.$offset.' '.$offset_param.' '
			.	'FETCH FIRST '.$param.' '.$rows_param.' ';

			return $this;
		}
		public function fetch_first_percent(
			int $param,
			string $rows_param='ROWS ONLY',
			?int $offset=null,
			string $offset_param='ROWS'
		){
			if($offset === null)
			{
				$this->sql_query.=''
				.	'FETCH FIRST '.$param.' '
				.	'PERCENT '.$rows_param.' ';

				return $this;
			}

			$this->sql_query.=''
			.	'OFFSET '.$offset.' '.$offset_param.' '
			.	'FETCH FIRST '.$param.' '
			.	'PERCENT '.$rows_param.' ';

			return $this;
		}
		public function fetch_next(
			int $param,
			string $rows_param='ROWS ONLY',
			?int $offset=null,
			string $offset_param='ROWS'
		){
			if($offset === null)
			{
				$this->sql_query.='FETCH NEXT '.$param.' '.$rows_param.' ';
				return $this;
			}

			$this->sql_query.=''
			.	'OFFSET '.$offset.' '.$offset_param.' '
			.	'FETCH NEXT '.$param.' '.$rows_param.' ';

			return $this;
		}
		public function fetch_next_percent(
			int $param,
			string $rows_param='ROWS ONLY',
			?int $offset=null,
			string $offset_param='ROWS'
		){
			if($offset === null)
			{
				$this->sql_query.=''
				.	'FETCH NEXT '.$param.' '
				.	'PERCENT '.$rows_param.' ';

				return $this;
			}

			$this->sql_query.=''
			.	'OFFSET '.$offset.' '.$offset_param.' '
			.	'FETCH NEXT '.$param.' '
			.	'PERCENT '.$rows_param.' ';

			return $this;
		}

		public function replace_into(
			string $where,
			string $columns,
			array $what
		){
			$sql_what='';

			foreach($what as $what_data_set)
			{
				if(!is_array($what_data_set))
					throw new pdo_crud_builder_exception(
						'The dataset must be an array'
					);

				$sql_what.='(';

				foreach($what_data_set as $what_value)
				{
					if($what_value === null)
					{
						$sql_what.='NULL,';
						continue;
					}

					$sql_what.='?,';
					$this->sql_parameters[]=$what_value;
				}

				$sql_what=substr($sql_what, 0, -1);
				$sql_what.='), ';
			}

			$sql_what=substr($sql_what, 0, -2);

			$this->sql_query.=''
			.	'REPLACE INTO '.$where
			.	'('.$columns.') '
			.	'VALUES'.$sql_what.' ';

			return $this;
		}
		public function update(string $table)
		{
			$this->sql_query.='UPDATE '.$table.' ';
			return $this;
		}
		public function set(array $what)
		{
			$sql_what='';

			foreach($what as $data_set)
			{
				if(!is_array($data_set))
					throw new pdo_crud_builder_exception(
						'The dataset must be an array'
					);

				if(!isset($data_set[0]))
					throw new pdo_crud_builder_exception(
						'No column name was provided'
					);

				if(!is_string($data_set[0]))
					throw new pdo_crud_builder_exception(
						'Column name must be a string'
					);

				if(count($data_set) !== 2)
					throw new pdo_crud_builder_exception(''
					.	'No value was provided for column '.$data_set[0].' '
					.	'or more than two values were given in the array'
					);

				if($data_set[1] === null)
				{
					$sql_what.=$data_set[0].' = NULL, ';
					continue;
				}

				$sql_what.=$data_set[0].' = ?, ';
				$this->sql_parameters[]=$data_set[1];
			}

			$sql_what=substr($sql_what, 0, -2);

			$this->sql_query.='SET '.$sql_what.' ';

			return $this;
		}

		public function delete(string $from)
		{
			$this->sql_query.='DELETE FROM '.$from.' ';
			return $this;
		}

		public function raw_sql(string $raw_sql)
		{
			$this->sql_query.=$raw_sql.' ';
			return $this;
		}
		public function raw_parameter($raw_param)
		{
			$this->sql_parameters[]=$raw_param;
			return $this;
		}

		public function exec(bool $query=false)
		{
			$result=$this->pdo_handle->prepare($this->sql_query);

			if($result === false)
				$this->on_error[0]('::exec(): error on query preparation');
			else if($query)
				$result->execute($this->sql_parameters);
			else
				$result=$result->execute($this->sql_parameters);

			if($this->auto_flush)
				$this->flush_all();

			return $result;
		}
		public function fetch_row($pdo_object)
		{
			return $pdo_object->fetch($this->fetch_mode);
		}
		public function query()
		{
			$exec_output=$this->exec(true);

			if($exec_output !== false)
				return $exec_output->fetchAll($this->fetch_mode);

			$this->on_error[0]('::query(): exec() returned false');

			return false;
		}

		public function pdo_disconnect()
		{
			$this->pdo_handle=null;
			return null;
		}
		public function error_info()
		{
			return $this->pdo_handle->errorInfo();
		}
		public function flush_all()
		{
			$this->sql_query='';
			$this->sql_parameters=[];

			return $this;
		}
		public function print_exec(bool $echo=false)
		{
			if($echo)
			{
				echo $this->sql_query;
				return;
			}

			return $this->sql_query;
		}
		public function print_query(bool $echo=false)
		{
			return $this->print_exec($echo);
		}
		public function print_parameters(bool $var_dump=false)
		{
			if($var_dump)
			{
				var_dump($this->sql_parameters);
				return;
			}

			return $this->sql_parameters;
		}
		public function print_prepared()
		{
			$stmt=$this->print_exec();

			foreach($this->print_parameters() as $param)
				$stmt=preg_replace('/\?/', $param, $stmt, 1);

			return $stmt;
		}
		public function table_dump(
			string $table_name,
			?int $limit=null,
			?int $limit_offset=null
		){
			$this->flush_all();

			if($limit === null)
				return $this
				->	select('*')
				->	from($table_name)
				->	query();

			return $this
			->	select('*')
			->	from($table_name)
			->	limit($limit, $limit_offset)
			->	query();
		}
		public function list_tables()
		{
			switch($this->pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'mysql':
					$sql='SHOW TABLES';
				break;
				case 'pgsql':
					$sql=''
					.	'SELECT tablename '
					.	'FROM pg_catalog.pg_tables';
				break;
				case 'sqlite':
					$sql=''
					.	'SELECT name '
					.	'FROM sqlite_master '
					.	'WHERE type="table"';
				break;
				case 'oci':
					$sql=''
					.	'SELECT table_name '
					.	'FROM user_tables';
				break;
				case 'dblib':
					$sql=''
					.	'SELECT name '
					.	'FROM SYSOBJECTS';
				break;
				default:
					return false;
			}

			$query=$this->pdo_handle->query($sql);

			return $query->fetchAll(PDO::FETCH_COLUMN);
		}
	}
?>