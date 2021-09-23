<?php
	class registry
	{
		/*
		 * Registry design pattern
		 *
		 * Usage:
			$registry=new registry(false);
			$registry->key='value';
			echo $registry->key;
		 * where false is default value if key not exists in registry (optional, default: null)
		 */

		private $registry=array();
		private $default_value;

		public function __construct($default_value=null)
		{
			$this->default_value=$default_value;
		}
		public function __get($key)
		{
			if(array_key_exists($key, $this->registry))
				return $this->registry[$key];
			return $this->default_value;
		}
		public function __set($key, $value)
		{
			$this->registry[$key]=$value;
		}
	}
?>