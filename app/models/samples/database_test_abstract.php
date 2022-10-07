<?php
	class database_test_abstract
	{
		/*
		 * database abstraction class
		 * for elimination of sql usage
		 *
		 * Warning:
		 *  pdo_connect.php library is required
		 *  pdo_crud_bulder.php library is required
		 *
		 * Hint:
		 *  you can override default database definition
		 *  by DB_TYPE environment variable
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
			foreach([
				'pdo_connect.php'=>['function', 'pdo_connect'],
				'pdo_crud_builder.php'=>['class', 'pdo_crud_builder']
			] as $library=>$library_meta)
				switch($library_meta[0])
				{
					case 'class':
						if(!class_exists($library_meta[1]))
						{
							if(!is_file(__DIR__.'/../../../lib/'.$library))
								throw new Exception(__DIR__.'/../../../lib/'.$library.' not found');

							include __DIR__.'/../../../lib/'.$library;
						}
					break;
					case 'function':
						if(!function_exists($library_meta[1]))
						{
							if(!is_file(__DIR__.'/../../../lib/'.$library))
								throw new Exception(__DIR__.'/../../../lib/'.$library.' not found');

							include __DIR__.'/../../../lib/'.$library;
						}
				}

			$this->table_name=$table_name;
			$this->table_key=$table_key;
			$this->table_columns=$table_columns;

			if(getenv('DB_IGNORE_ENV') === 'true')
				$pdo_connect_db=false;
			else
				$pdo_connect_db=getenv('DB_TYPE');

			if($pdo_connect_db === false)
				$pdo_connect_db='sqlite';

			if(!is_dir(__DIR__.'/../../databases/samples/'.$pdo_connect_db))
				throw new Exception(__DIR__.'/../../databases/samples/'.$pdo_connect_db.' not exists');

			$this->query_builder=new pdo_crud_builder([
				'pdo_handler'=>pdo_connect(
					__DIR__.'/../../databases/samples/'.$pdo_connect_db,
					$on_pdo_connect_error
				)
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