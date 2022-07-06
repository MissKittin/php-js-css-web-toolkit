<?php
	class pdo_crud_builder
	{
		/*
		 * PDO CRUD builder
		 *
		 * Note:
		 *  most of the queries are parameterized to avoid sql injections
		 *  all args in [] are optional
		 *  for PHP7 and newer
		 *
		 * Initializing:
		 *  $query_builder_object=new pdo_crud_builder(params_array)
		 *   where params_array has:
				'pdo_handler'=>$pdo_object // required
				'pdo_fetch_mode'=>PDO::FETCH_NAMED // optional
				'auto_flush'=>true // flush query after exec(), optional
				'on_error'=>function($message){ error_log($message); } // error logging, optional (see examples)
		 *  note: this class does not creates connection to the database
		 *   you have to manually open connection and then pass pdo handler to the builder
		 *
		 * PDO fetch method
		 *  current fetch method is used in query() exec method
		 *  can be changed via set_fetch_method(PDO::FETCH_METHOD)
		 *  if query() is not used, this value can be passed to PDO method via get_fetch_method(), eg.
		 *    $pdo_crud_builder=new pdo_crud_builder(new PDO('sqlite:./database.sqlite3'));
		 *    $result=$pdo_crud_builder->select('*')->from('log')->where_not_like('date', '%-%-% 17:30:00')->exec(true); // true tells the exec() method this is a query
		 *    while($row=$pdo_crud_builder->fetch_row($result))
		 *     echo '0: '.$row[0].', 1: '.$row[1].', 2: '.$row[2].', 3: '.$row[3].', 4: '.$row[4].PHP_EOL;
		 *
		 * Usage:
		 *  $query_builder_object->first_statement()->second_statement()->n_statement()->execution_method()
		 *
		 *  Operations on tables:
		 *   Creating table ([] means array):
				create_table(
					'table_name',
					[
						'id'=>pdo_crud_builder::ID_DEFAULT_PARAMS,
						'first_column_name'=>'first column type',
						'second_column_name'=>'second column type',
						'n_column_name'=>'n column type'
					]
				)
		 *   Dropping table:
		 *    drop_table('table_name')
		 *   Truncating table:
		 *    truncate_table('table_name')
		 *
		 *  Creating ([] means array):
				insert_into(
					'table_name',
					'first_column_name,second_column_name,n_column_name',
					[
						['new_value_aa', 'new_value_ab', 'new_value_ac'],
						['new_value_ba', 'new_value_bb', 'new_value_bc'],
						['new_value_ca', 'new_value_cb', 'new_value_cc'],
						['new_value_da', 'new_value_db', 'new_value_dc']
					]
				)
		 *
		 *  Reading:
		 *   select(string_what)
		 *   select_top(int_how_many, string_what)
		 *   select_top_percent(int_how_many, string_what)
		 *   as(string_what)
		 *   group_by(string_what)
		 *   order_by(string_what)
		 *   join(string_inner|left|right|full, string_what, [string_on])
		 *   union()
		 *   union_all()
		 *   asc()
		 *   desc()
		 *   limit(int_how_many, [int_offset])
		 *   fetch_first(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *   fetch_first_percent(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *   fetch_next(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *   fetch_next_percent(int_how_many, [string_fetch_param], [int_offset_number], [string_offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *
		 *  Updating ([] means array):
				replace_into(
					string_table_name,
					'id,second_column_name,n_column_name',
					[
						['id_a', 'new_value_aa', 'new_value_ab'],
						['id_b', 'new_value_ba', 'new_value_bb'],
						['id_c', 'new_value_ca', 'new_value_cb'],
						['id_d', 'new_value_da', 'new_value_db']
					]
				)
				update(string_table_name)
				set([
					['first_column_name', 'new_value_a'],
					['second_column_name', 'new_value_b'],
					['n_column_name', 'new_value_n']
				])
		 *
		 *  Deleting:
		 *   delete(string_from)
		 *    note: you do not have to use from() method
		 *
		 *  Miscellaneous statements:
		 *   from(string)
		 *   where statements:
		 *    where(string_a, string_operator, string_b)
		 *     and(string_a, string_operator, string_b)
		 *     or(string_a, string_operator, string_b)
		 *    where_like(string_column_name, string_sql_with_wildcards)
		 *    where_not_like(string_column_name, string_sql_with_wildcards)
		 *    where_is(string_a, string_what)
		 *    where_not(string_a, string_operator, string_b)
		 *   output_into(string_parameters, string_into_where)
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
		 *  unset($query_builder_object)
		 *
		 * Examples:
		 *  initialization with SQLite3 database:
				$pdo_crud_builder=new pdo_crud_builder([
					'pdo_handler'=>new PDO('sqlite:./database.sqlite3'),
					'on_error'=>function($error) { error_log('pdo_crud_builder'.$error); }
				])
		 *  initialization with pdo_connect library:
				$pdo_crud_builder=new pdo_crud_builder([
					'pdo_handler'=>pdo_connect('pathTo/yourDatabaseConfigDirectory'),
					'on_error'=>function($error) { error_log('pdo_crud_builder'.$error); }
				])
		 *  dump all rows from the table:
		 *   $result=$pdo_crud_builder->table_dump('log');
		 *  fetch result in while loop: see PDO fetch method
				$result=$pdo_crud_builder->select('*')->from('log')->exec(true);
				while($row=$pdo_crud_builder->fetch_row($result)) // or
				while($row=$result->fetch($pdo_crud_builder->get_fetch_method()))
		 */

		const ID_DEFAULT_PARAMS='INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';

		protected $pdo_handler;
		protected $fetch_mode;
		protected $on_error;
		protected $auto_flush=true;
		protected $sql_query='';
		protected $sql_parameters=[];

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new Exception('No PDO handler given');

			$this->fetch_mode=PDO::FETCH_NAMED;
			$this->on_error['callback']=function(){};

			foreach(['pdo_handler', 'fetch_mode', 'auto_flush'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			if(isset($params['on_error']))
				$this->on_error['callback']=$on_error;
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
		public function where(string $name, string $operator, string $value)
		{
			$this->sql_query.='WHERE '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function where_is(string $name, string $what)
		{
			$this->sql_query.='WHERE '.$name.' IS '.$what.' ';
			return $this;
		}
		public function where_like(string $name, string $string)
		{
			$this->sql_query.='WHERE '.$name.' LIKE ? ';
			$this->sql_parameters[]=$string;

			return $this;
		}
		public function where_not_like(string $name, string $string)
		{
			$this->sql_query.='WHERE '.$name.' NOT LIKE ? ';
			$this->sql_parameters[]=$string;

			return $this;
		}
		public function where_not(string $name, string $operator, string $value)
		{
			$this->sql_query.='WHERE NOT '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function and(string $name, string $operator, string $value)
		{
			$this->sql_query.='AND '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function or(string $name, string $operator, string $value)
		{
			$this->sql_query.='OR '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;

			return $this;
		}
		public function output_into(string $parameters, string $into)
		{
			$this->sql_query.='OUTPUT '.$parameters.' INTO '.$into.' ';
			return $this;
		}

		public function create_table(string $table_name, array $columns)
		{
			$sql_columns='';
			foreach($columns as $column_name=>$column_type)
			{
				if(!is_string($column_type))
					throw new Exception('Array value must be a string');
				$sql_columns.=$column_name.' '.$column_type.', ';
			}
			$sql_columns=substr($sql_columns, 0, -2);

			$this->sql_query.='CREATE TABLE '.$table_name.'('.$sql_columns.') ';

			return $this;
		}
		public function drop_table(string $table_name)
		{
			$this->sql_query.='DROP TABLE IF EXISTS '.$table_name.' ';
			return $this;
		}
		public function truncate_table(string $table_name)
		{
			$this->sql_query.='TRUNCATE TABLE '.$table_name.' ';
			return $this;
		}

		public function insert_into(string $where, string $columns, array $what)
		{
			$sql_what='';
			foreach($what as $what_data_set)
			{
				if(!is_array($what_data_set))
					throw new Exception('The dataset must be an array');

				$sql_what.='(';
				foreach($what_data_set as $what_value)
				{
					$sql_what.='?, ';
					$this->sql_parameters[]=$what_value;
				}
				$sql_what=substr($sql_what, 0, -2);
				$sql_what.='), ';
			}
			$sql_what=substr($sql_what, 0, -2);

			$this->sql_query.='INSERT INTO '.$where.'('.$columns.') VALUES'.$sql_what.' ';

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
			$this->sql_query.='SELECT TOP '.$param.' PERCENT '.$what.' ';
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
		public function join(string $method, string $what, string $on=null)
		{
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
					$this->on_error['callback']('::join(): inner/left/right/full $method not specified');
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
		public function limit(int $param, int $offset=null)
		{
			if($offset === null)
				$this->sql_query.='LIMIT '.$param.' ';
			else
				$this->sql_query.='LIMIT '.$param.' OFFSET '.$offset.' ';

			return $this;
		}
		public function fetch_first(int $param, string $rows_param='ROWS ONLY', int $offset=null, string $offset_param='ROWS')
		{
			if($offset === null)
				$this->sql_query.='FETCH FIRST '.$param.' '.$rows_param.' ';
			else
				$this->sql_query.='OFFSET '.$offset.' '.$offset_param.' FETCH FIRST '.$param.' '.$rows_param.' ';

			return $this;
		}
		public function fetch_first_percent(int $param, string $rows_param='ROWS ONLY', int $offset=null, string $offset_param='ROWS')
		{
			if($offset === null)
				$this->sql_query.='FETCH FIRST '.$param.' PERCENT '.$rows_param.' ';
			else
				$this->sql_query.='OFFSET '.$offset.' '.$offset_param.' FETCH FIRST '.$param.' PERCENT '.$rows_param.' ';

			return $this;
		}
		public function fetch_next(int $param, string $rows_param='ROWS ONLY', int $offset=null, string $offset_param='ROWS')
		{
			if($offset === null)
				$this->sql_query.='FETCH NEXT '.$param.' '.$rows_param.' ';
			else
				$this->sql_query.='OFFSET '.$offset.' '.$offset_param.' FETCH NEXT '.$param.' '.$rows_param.' ';

			return $this;
		}
		public function fetch_next_percent(int $param, string $rows_param='ROWS ONLY', int $offset=null, string $offset_param='ROWS')
		{
			if($offset === null)
				$this->sql_query.='FETCH NEXT '.$param.' PERCENT '.$rows_param.' ';
			else
				$this->sql_query.='OFFSET '.$offset.' '.$offset_param.' FETCH NEXT '.$param.' PERCENT '.$rows_param.' ';

			return $this;
		}

		public function replace_into(string $where, string $columns, array $what)
		{
			$sql_what='';
			foreach($what as $what_data_set)
			{
				if(!is_array($what_data_set))
					throw new Exception('The dataset must be an array');

				$sql_what.='(';
				foreach($what_data_set as $what_value)
				{
					$sql_what.='?, ';
					$this->sql_parameters[]=$what_value;
				}
				$sql_what=substr($sql_what, 0, -2);
				$sql_what.='), ';
			}
			$sql_what=substr($sql_what, 0, -2);

			$this->sql_query.='REPLACE INTO '.$where.'('.$columns.') VALUES'.$sql_what.' ';

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
					throw new Exception('The dataset must be an array');

				if(!isset($data_set[0]))
					throw new Exception('No column name was provided');

				if(!is_string($data_set[0]))
					throw new Exception('Column name must be a string');

				if(!isset($data_set[1]))
					throw new Exception('No value was provided for column '.$data_set[0]);

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
			$result=$this->pdo_handler->prepare($this->sql_query);

			if($result === false)
				$this->on_error['callback']('::exec(): error on query preparation');
			else
			{
				if($query)
					$result->execute($this->sql_parameters);
				else
					$result=$result->execute($this->sql_parameters);
			}

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

			$this->on_error['callback'](' ::query(): exec() returned false');

			return false;
		}

		public function error_info()
		{
			return $this->pdo_handler->errorInfo();
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
				echo $this->sql_query;
			else
				return $this->sql_query;
		}
		public function print_query(bool $echo=false)
		{
			return $this->print_exec($echo);
		}
		public function print_parameters(bool $var_dump=false)
		{
			if($var_dump)
				var_dump($this->sql_parameters);
			else
				return $this->sql_parameters;
		}
		public function print_prepared()
		{
			$stmt=$this->print_exec();

			foreach($this->print_parameters() as $param)
				$stmt=preg_replace('/\?/', $param, $stmt, 1);

			return $stmt;
		}
		public function table_dump(string $table_name, int $limit=null, int $limit_offset=null)
		{
			$this->flush_all();

			if($limit === null)
				return $this->select('*')->from($table_name)->query();

			return $this->select('*')->from($table_name)->limit($limit, $limit_offset)->query();
		}
		public function list_tables()
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'mysql':
					$sql='SHOW TABLES';
				break;
				case 'pgsql':
					$sql='SELECT tablename FROM pg_catalog.pg_tables';
				break;
				case 'sqlite':
					$sql='SELECT name FROM sqlite_master WHERE type="table"';
				break;
				case 'oci':
					$sql='SELECT table_name FROM user_tables';
				break;
				case 'dblib':
					$sql='SELECT name FROM SYSOBJECTS';
				break;
				default:
					return false;
			}

			$query=$this->pdo_handler->query($sql);

			return $query->fetchAll(PDO::FETCH_COLUMN);
		}
	}
?>