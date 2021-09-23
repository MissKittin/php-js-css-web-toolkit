<?php
	class pdo_crud_builder
	{
		/*
		 * PDO CRUD builder
		 *  most of the queries are parameterized to avoid sql injections
		 *
		 * note: all args in [] are optional
		 *
		 * Initializing:
		 *  $query_builder_object=new pdo_crud_builder($pdo_handler, ['callback_function'], [$pdo_fetch_mode])
		 *   where $pdo_fetch_mode is PDO::FETCH_NAMED by default
		 *   and callback_function is for error logging, can be anonymous function (default null) (see examples)
		 *  note: this class does not creates connection to the database
		 *  you have to manually open connection and then pass pdo handler to the builder
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
		 *  Operations on tables:
		 *   Creating table:
		 *    create_table('table_name', array(
		 *     'first_column_name' => 'first column type',
		 *     'second_column_name' => 'second column type,
		 *     'n_column_name' => 'n column type'
		 *    ))
		 *   Dropping table:
		 *    drop_table('table_name')
		 *   Truncating table:
		 *    truncate_table('table_name')
		 *
		 *  Creating:
		 *   insert_into(table_name_string, 'first_column_name,second_column_name,n_column_name', array(
		 *    ['new_value_aa', 'new_value_ab', 'new_value_ac'],
		 *    ['new_value_ba', 'new_value_bb', 'new_value_bc'],
		 *    ['new_value_ca', 'new_value_cb', 'new_value_cc'],
		 *    ['new_value_da', 'new_value_db', 'new_value_dc']
		 *   ))
		 *
		 *  Reading:
		 *   select(what_string)
		 *   select_top(int_how_many, what_string)
		 *   as(what_string)
		 *   group_by(what_string)
		 *   order_by(what_string)
		 *   join(inner|left|right|full, what_string, [on_string])
		 *   union()
		 *   union_all()
		 *   asc()
		 *   desc()
		 *   limit(how_many, [offset_number])
		 *   fetch_first(how_many, [fetch_param], [offset_number], [offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *   fetch_next(how_many, [fetch_param], [offset_number], [offset_param]) // fetch_param default: ROWS ONLY, offset_param default: ROWS
		 *
		 *  Updating:
		 *   update(table_name_string)
		 *   set(array(
		 *    ['first_column_name', 'new_value_a'],
		 *    ['second_column_name', 'new_value_b'],
		 *    ['n_column_name', 'new_value_n']
		 *   ))
		 *
		 *  Deleting:
		 *   delete(from_string)
		 *    note: you do not have to use from() method
		 *
		 *  Miscellaneous statements:
		 *   from(string)
		 *   where statements:
		 *    where(string_a, operator, string_a)
		 *     and(string_a, operator, string_a)
		 *     or(string_a, operator, string_a)
		 *    where_like(column_name, sql_string_with_wildcards)
		 *    where_not_like(column_name, sql_string_with_wildcards)
		 *    where_is(string_a, what_string)
		 *    where_not(string_a, operator, string_a)
		 *   output_into(parameters_string, into_where_string)
		 *   end()
		 *    adds ; character to query - build second query in the same stream
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
		 *     while($row=$pdo_crud_builder->fetch_row($result)
		 *   query()
		 *    executes query and returns output from fetchAll() or false if failed
		 *   note: above metods automatically clears sql query string and parameter's array
		 *   warning: if you using PDO's exec methods, you must $pdo_crud_builder->flush_all() after operation
		 *
		 * Debugging:
		 *  print_exec([true])
		 *   returns sql query or prints it if true is passed
		 *  print_query([true])
		 *   alias to the print_exec()
		 *  print_parameters([true])
		 *   returns array with parameters or var_dump if true is passed
		 *  flush_all()
		 *   clears sql query and parameters(as exec() but without sending query)
		 *
		 * Closing connection:
		 *  unset($query_builder_object)
		 *
		 * Examples:
		 *  initialization with SQLite3 database:
				$pdo_crud_builder=new pdo_crud_builder(
					new PDO('sqlite:./database.sqlite3'),
					function($error) { error_log('pdo_crud_builder'.$error); }
				)
		 *  initialization with pdo_connect library:
				$pdo_crud_builder=new pdo_crud_builder(
					pdo_connect('pathTo/yourDatabaseConfigDirectory'),
					function($error) { error_log('pdo_crud_builder'.$error); }
				)
		 *  dump all rows from table:
		 *   $result=$pdo_crud_builder->select('*')->from('log')->query();
		 *  fetch result in while loop: see PDO fetch method
		 *   $result=$pdo_crud_builder->select('*')->from('log')->exec(true);
		 *   while($row=$pdo_crud_builder->fetch_row($result)) // or
		 *   while($row=$result->fetch($pdo_crud_builder->get_fetch_method()))
		 */

		private $pdo_handler;
		private $fetch_mode;
		private $on_error=array();
		private $sql_query='';
		private $sql_parameters=array();

		public function __construct($pdo_handler, $on_error=null, $fetch_mode=PDO::FETCH_NAMED)
		{
			$this->pdo_handler=$pdo_handler;
			$this->on_error['callback']=$on_error;
			$this->fetch_mode=$fetch_mode;
		}
		public function __destruct()
		{
			$this->pdo_handler=null;
		}

		// PDO $fetch_mode handling
		public function get_fetch_mode()
		{
			return $this->fetch_mode;
		}
		public function set_fetch_mode($fetch_mode)
		{
			$this->fetch_mode=$fetch_mode;
		}

		// misc
		public function from($from)
		{
			$this->sql_query.='FROM '.$from.' ';
			return $this;
		}
		public function where($name, $operator, $value)
		{
			$this->sql_query.='WHERE '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;
			return $this;
		}
		public function where_is($name, $what)
		{
			$this->sql_query.='WHERE '.$name.' IS '.$what.' ';
			return $this;
		}
		public function where_like($name, $string)
		{
			$this->sql_query.='WHERE '.$name.' LIKE ? ';
			$this->sql_parameters[]=$string;
			return $this;
		}
		public function where_not_like($name, $string)
		{
			$this->sql_query.='WHERE '.$name.' NOT LIKE ? ';
			$this->sql_parameters[]=$string;
			return $this;
		}
		public function where_not($name, $operator, $value)
		{
			$this->sql_query.='WHERE NOT '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;
			return $this;
		}
		public function and($name, $operator, $value)
		{
			$this->sql_query.='AND '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;
			return $this;
		}
		public function or($name, $operator, $value)
		{
			$this->sql_query.='OR '.$name.$operator.'? ';
			$this->sql_parameters[]=$value;
			return $this;
		}
		public function output_into($parameters, $into)
		{
			$this->sql_query.='OUTPUT '.$parameters.' INTO '.$into.' ';
			return $this;
		}
		public function end()
		{
			$this->sql_query.='; ';
			return $this;
		}

		// tables
		public function create_table($table_name, $columns)
		{
			$sql_columns='';
			foreach($columns as $column_name=>$column_type)
				$sql_columns.=$column_name.' '.$column_type.', ';
			$sql_columns=substr($sql_columns, 0, -2);

			$this->sql_query.='CREATE TABLE '.$table_name.'('.$sql_columns.') ';
			return $this;
		}
		public function drop_table($table_name)
		{
			$this->sql_query.='DROP TABLE IF EXISTS '.$table_name.' ';
			return $this;
		}
		public function truncate_table($table_name)
		{
			$this->sql_query.='TRUNCATE TABLE '.$table_name.' ';
			return $this;
		}

		// C
		public function insert_into($where, $columns, $what)
		{
			$sql_what='';
			foreach($what as $what_data_set)
			{
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

		// R
		public function select($what)
		{
			$this->sql_query.='SELECT '.$what.' ';
			return $this;
		}
		public function select_top($param, $what)
		{
			$this->sql_query.='SELECT TOP '.$param.' '.$what.' ';
			return $this;
		}
		public function as($what)
		{
			$this->sql_query.='AS '.$what.' ';
			return $this;
		}
		public function group_by($what)
		{
			$this->sql_query.='GROUP BY '.$what.' ';
			return $this;
		}
		public function order_by($what)
		{
			$this->sql_query.='ORDER BY '.$what.' ';
			return $this;
		}
		public function join($method, $what, $on=false)
		{
			switch($method)
			{
				case 'inner': $this->sql_query.='INNER JOIN '.$what.' '; break;
				case 'left': $this->sql_query.='LEFT OUTER JOIN '.$what.' '; break;
				case 'right': $this->sql_query.='RIGHT OUTER JOIN '.$what.' '; break;
				case 'full': $this->sql_query.='FULL OUTER JOIN '.$what.' '; break;
				default: if($this->on_error['callback'] !== null) $this->on_error['callback']('::join(): inner/left/right/full $method not specified'); return false; break;
			}

			if($on !== false)
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
		public function limit($param, $offset=null)
		{
			if($offset === null)
				$this->sql_query.='LIMIT '.$param.' ';
			else
				$this->sql_query.='LIMIT '.$param.' OFFSET '.$offset.' ';
			return $this;
		}
		public function fetch_first($param, $rows_param='ROWS ONLY', $offset=null, $offset_param='ROWS')
		{
			if($offset === null)
				$this->sql_query.='FETCH FIRST '.$param.' '.$rows_param.' ';
			else
				$this->sql_query.='OFFSET '.$offset.' '.$offset_param.' FETCH FIRST '.$param.' '.$rows_param.' ';
			return $this;
		}
		public function fetch_next($param, $rows_param='ROWS ONLY', $offset=null, $offset_param='ROWS')
		{
			if($offset === null)
				$this->sql_query.='FETCH NEXT '.$param.' '.$rows_param.' ';
			else
				$this->sql_query.='OFFSET '.$offset.' '.$offset_param.' FETCH NEXT '.$param.' '.$rows_param.' ';
			return $this;
		}

		// U
		public function update($table)
		{
			$this->sql_query.='UPDATE '.$table.' ';
			return $this;
		}
		public function set($what)
		{
			$sql_what='';
			foreach($what as $data_set)
			{
				$sql_what.=$data_set[0].' = ?, ';
				$this->sql_parameters[]=$data_set[1];
			}
			$sql_what=substr($sql_what, 0, -2);

			$this->sql_query.='SET '.$sql_what.' ';
			return $this;
		}

		// D
		public function delete($from)
		{
			$this->sql_query.='DELETE FROM '.$from.' ';
			return $this;
		}

		// raw sql operations
		public function raw_sql($raw_sql)
		{
			$this->sql_query.=$raw_sql.' ';
			return $this;
		}
		public function raw_parameter($raw_param)
		{
			$this->sql_parameters[]=$raw_param;
			return $this;	
		}

		// run
		public function exec($query=false)
		{
			$result=$this->pdo_handler->prepare($this->sql_query);

			if($result === false)
			{
				if($this->on_error['callback'] !== null) $this->on_error['callback']('::exec(): error on query preparation');
			}
			else
			{
				if($query)
					$result->execute($this->sql_parameters);
				else
					$result=$result->execute($this->sql_parameters);
			}

			$this->sql_query='';
			$this->sql_parameters=array();

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

			if($this->on_error['callback'] !== null) $this->on_error['callback'](' ::query(): exec() returned false');
			return false;
		}

		// debug
		public function print_exec($echo=false)
		{
			if($echo)
				echo $this->sql_query;
			else
				return $this->sql_query;
		}
		public function print_query($echo=false) // alias
		{
			return $this->print_exec($echo);
		}
		public function print_parameters($var_dump=false)
		{
			if($var_dump)
				var_dump($this->sql_parameters);
			else
				return $this->sql_parameters;
		}
		public function flush_all()
		{
			$this->sql_query='';
			$this->sql_parameters=array();
			return $this;
		}
	}
?>