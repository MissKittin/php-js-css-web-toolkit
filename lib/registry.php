<?php
	/*
	 * Registry design pattern library
	 *
	 * Traits:
	 *  t_registry
	 *   for multiple inheritance
	 * Classes:
	 *  registry
	 *   standard registry class
	 *  static_registry
	 *   if you need global access to the registry instance
	 */

	class registry_exception extends Exception {}

	trait t_registry
	{
		/*
		 * Registry design pattern - trait version
		 *
		 * Note:
		 *  throws an registry_exception on error
		 *
		 * Usage:
			class my_registry implements ArrayAccess { use t_registry; }
			$registry=new my_registry(false);
			$registry->key='value';
			echo $registry->key; // prints 'value'
		 * where false is default value if key not exists in registry (optional, default: null)
		 *
		 * Alternative usage:
			class my_registry implements ArrayAccess { use t_registry; }
			$registry=new registry(false);
			$registry['key']='value';
			echo $registry['key']; // prints 'value'
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

	class registry implements ArrayAccess
	{
		/*
		 * Registry design pattern - class wrapper
		 *
		 * Warning:
		 *  t_registry trait is required
		 *
		 * Usage:
			$registry=new registry(false);
			$registry->key='value';
			echo $registry->key; // prints 'value'
		 * where false is default value if key not exists in registry (optional, default: null)
		 *
		 * Alternative usage:
			$registry=new registry(false);
			$registry['key']='value';
			echo $registry['key']; // prints 'value'
		 * note: you cannot use unset()
		 */

		use t_registry;
	}
	abstract class static_registry
	{
		/*
		 * Registry design pattern - static class wrapper
		 *
		 * Warning:
		 *  registry class is required
		 *
		 * Note:
		 *  throws an registry_exception on error
		 *
		 * Usage:
			// first container
			abstract class my_registry extends static_registry { protected static $registry=null; }
			my_registry::r('default_value')['key']='value';
			// second container
			abstract class my_reg_b extends static_registry { protected static $registry=null; }
			my_reg_b::r()->key='value2';
			echo my_registry::r()->key // prints 'value'
			echo my_reg_b::r()['key'] // prints 'value2'
			echo my_registry::r()['nonexistent'] // prints 'default_value'
			echo my_reg_b::r()['nonexistent'] // prints null
		 * note:
		 *  'default_value' is only considered once
		 *  you cannot use unset()
		 *  the method name does not matter
		 */

		public static function __callStatic($method, $args)
		{
			try {
				if(!(static::$registry instanceof registry))
				{
					$default_value=null;

					if(isset($args[0]))
						$default_value=$args[0];

					static::$registry=new registry($default_value);
				}

				return static::$registry;
			} catch(Error $error) {
				throw new registry_exception('You did not declare a protected static $registry property or you used the static_registry class directly');
			}
		}
	}
?>