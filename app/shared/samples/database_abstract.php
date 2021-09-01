<?php
	// database abstraction class
	// for elimination of sql usage

	class database_abstract
	{
		private $table_name;
		private $table_key;
		private $table_columns;
		private $query_builder;

		public function __construct($table_name, $table_key, $table_columns, $query_builder)
		{
			$this->table_name=$table_name;
			$this->table_key=$table_key;
			$this->table_columns=$table_columns;
			$this->query_builder=$query_builder;

			$this->query_builder->set_fetch_mode(PDO::FETCH_NUM);
		}
		public function __destruct()
		{
			$this->query_builder=null;
		}

		public function create($input_array)
		{
			return $this->query_builder
				->insert_into($this->table_name, $this->table_columns, $input_array)
				->exec();
		}
		public function read($column=null, $value=null, $select='*')
		{
			$this->query_builder
				->select($select)
				->from($this->table_name);

			if(($column !== null) && ($value !== null))
				$this->query_builder->where($column, '=', $value);

			return $this->query_builder->query();
		}
		public function update($id, $sql_set)
		{
			$table_columns=explode(',', $this->table_columns);
			foreach($sql_set as $set_key=>$set_value)
				$set_array[]=[$table_columns[$set_key], $set_value];

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