<?php
	/*
	 * Singleton can be implemented in 2 ways:
	 *  as trait (t_singleton)
	 *  or as abstract class (a_singleton)
	 * The difference is that the abstract class
	 *  supports the final keyword but offers less flexibility
	 */

	trait t_singleton
	{
		/*
		 * Each time you use a singleton, one little kitten dies
		 * Don't be indifferent - pets need love too
		 *
		 * Usage:
			class my_singleton
			{
				use t_singleton;

				protected function init()
				{
					// this method is optional
					echo 'init method called';
				}

				public function my_method()
				{
					echo 'my_method called';
				}
			}
			$my_singleton_object_a=my_singleton::get_instance();
			$my_singleton_object_b=my_singleton::get_instance();
			$my_singleton_object_b->my_method();
		 * or you can use getInstance() if you prefer camelCase
		 */

		protected static $instance=null;

		protected function __construct()
		{
			static::init();
		}
		public function __clone()
		{
			throw new Exception(static::class.': cloning singleton is not allowed');
		}
		public function __wakeup()
		{
			throw new Exception(static::class.': unserialization singleton is not allowed');
		}

		public static function get_instance()
		{
			if(static::$instance === null)
				static::$instance=new static();

			return static::$instance;
		}
		public function getInstance()
		{
			return static::get_instance();
		}

		protected function init() {}
	}

	abstract class a_singleton
	{
		/*
		 * Each time you use a singleton, one little kitten dies
		 * Don't be indifferent - pets need love too
		 *
		 * Warning:
		 *  t_singleton trait is required
		 *
		 * Usage:
			class my_singleton extends a_singleton
			{
				protected function init()
				{
					// this method is optional
					echo 'init method called';
				}

				public function my_method()
				{
					echo 'my_method called';
				}
			}
			$my_singleton_object_a=my_singleton::get_instance();
			$my_singleton_object_b=my_singleton::get_instance();
			$my_singleton_object_b->my_method();
		 * or you can use getInstance() if you prefer camelCase
		 */

		use t_singleton
		{
			__construct as _t_singleton_construct;
			__clone as _t_singleton_clone;
			__wakeup as _t_singleton_wakeup;
			get_instance as _t_singleton_get_instance;
		}

		final protected function __construct()
		{
			$this->_t_singleton_construct();
		}
		final public function __clone()
		{
			$this->_t_singleton_clone();
		}
		final public function __wakeup()
		{
			$this->_t_singleton_wakeup();
		}

		final public static function get_instance()
		{
			return static::_t_singleton_get_instance();
		}
		final public static function getInstance()
		{
			return static::_t_singleton_get_instance();
		}
	}
?>