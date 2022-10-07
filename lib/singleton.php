<?php
	trait singleton
	{
		/*
		 * Each time you use a singleton, one little kitten dies
		 * Don't be indifferent - pets need love too
		 *
		 * Usage:
			class my_singleton
			{
				use singleton;

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
		 *
		 * Note: the "final" keyword for methods is prepared if you want to change this trait to an abstract class
		 */

		protected static $instance=null;

		final protected function __construct()
		{
			static::init();
		}
		final public function __clone()
		{
			throw new Exception(static::class.': cloning singleton is not allowed');
		}
		final public function __wakeup()
		{
			throw new Exception(static::class.': unserialization singleton is not allowed');
		}

		final public static function get_instance()
		{
			if(static::$instance === null)
				static::$instance=new static();
			return static::$instance;
		}
		final public function getInstance()
		{
			return static::get_instance();
		}

		protected function init() {}
	}
?>