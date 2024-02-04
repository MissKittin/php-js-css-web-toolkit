<?php
	class database_test_abstract
	{
		/*
		 * Database abstraction class
		 * for elimination of SQL usage
		 *
		 * Warning:
		 *  pdo_crud_bulder.php library is required
		 */

		private $table_name;
		private $table_key;
		private $table_columns;
		private $query_builder;

		public function __construct(
			$table_name,
			$table_key,
			$table_columns,
			$on_pdo_connect_error
		){
			require __DIR__.'/../../lib/samples/pdo_instance.php';

			if(!class_exists('pdo_crud_builder'))
			{
				if(!is_file(__DIR__.'/../../../lib/pdo_crud_builder.php'))
					throw new Exception(__DIR__.'/../../../lib/pdo_crud_builder.php not found');

				require __DIR__.'/../../../lib/pdo_crud_builder.php';
			}

			$this->table_name=$table_name;
			$this->table_key=$table_key;
			$this->table_columns=$table_columns;

			$this->query_builder=new pdo_crud_builder([
				'pdo_handler'=>pdo_instance()
			]);

			$this->query_builder->set_fetch_mode(PDO::FETCH_NUM);
		}

		public function create($input_array)
		{
			return $this->query_builder
				->insert_into($this->table_name, $this->table_columns, [$input_array])
				->exec();
		}
		public function read($column=null, $value=null, $select='*')
		{
			$this->query_builder
				->select($select)
				->from($this->table_name);

			if(($column !== null) && ($value !== null))
				$this->query_builder->where($column, '=', $value);

			$query=$this->query_builder->query();

			// the layout of the database is known
			$query_size=count($query);
			for($i=0; $i<$query_size; ++$i)
				$query[$i]=[
					htmlspecialchars($query[$i][0], ENT_QUOTES, 'UTF-8'),
					htmlspecialchars($query[$i][1], ENT_QUOTES, 'UTF-8'),
					htmlspecialchars($query[$i][2], ENT_QUOTES, 'UTF-8')
				];

			return $query;
		}
		public function update($id, $sql_set)
		{
			$table_columns=explode(',', $this->table_columns);
			foreach($sql_set as $set_key=>$set_value)
				$set_array[]=[
					$table_columns[$set_key],
					$set_value
				];

			return $this->query_builder
				->update($this->table_name)
				->set($set_array)
				->where($this->table_key, '=', $id)
				->exec();
		}
		public function delete($id=null)
		{
			$this->query_builder
				->delete($this->table_name);

			if($id !== null)
				$this->query_builder->where($this->table_key, '=', $id);

			return $this->query_builder->exec();
		}
	}
?>