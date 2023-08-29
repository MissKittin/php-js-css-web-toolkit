<?php
	class registry_exception extends Exception {}
	class registry implements ArrayAccess
	{
		/*
		 * Registry design pattern
		 *
		 * Note:
		 *  this class can be inherited
		 *  throws an registry_exception on error
		 *
		 * Usage:
			$registry=new registry(false);
			$registry->key='value';
			echo $registry->key;
		 * where false is default value if key not exists in registry (optional, default: null)
		 *
		 * Alternative usage:
			$registry=new registry(false);
			$registry['key']='value';
			echo $registry['key'];
		 * note: you cannot use unset()
		 */

		protected $registry=[];
		protected $default_value;

		public function __construct($default_value=null)
		{
			$this->default_value=$default_value;
		}

		public function __get($key)
		{
			if(isset($this->registry[$key]))
				return $this->registry[$key];

			return $this->default_value;
		}
		public function __set($key, $value)
		{
			$this->registry[$key]=$value;
		}

		public function offsetSet($key, $value)
		{
			return $this->__set($key, $value);
		}
		public function offsetExists($key)
		{
			return isset($this->registry[$key]);
		}
		public function offsetGet($key)
		{
			return $this->__get($key);
		}
		public function offsetUnset($key)
		{
			throw new registry_exception('unset is not allowed');
		}
	}
?>