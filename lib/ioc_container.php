<?php
	/*
	 * Inversion of Control - basic implementation
	 *
	 * Classes:
	 * ioc_closure_container
	 *  basic IoC implementation
	 * ioc_autowired_container
	 *  extended ioc_closure_container
	 *  with autowiring and caching functionalities
	 */

	class ioc_closure_container
	{
		/*
		 * Dependency Injection container
		 *
		 * Note:
		 *  this class can be inherited
		 *
		 * Usage:
			$container=new ioc_closure_container();
			// here you can globalize the container
			$container->set('class_a', function(){
				return new class_a();
			});
			$container->share('class_b', function($container){
				return new class_b($container->get('class_b'));
			});
			$myclassb=$container->get('class_b');
			$myclassb->do_someting();
		 * where set() saves the closure and executes it on demand
		 *   (each get() returns new object instance)
		 *  and share() executes the closure and saves the object
		 *   (the same object instance will be returned from get())
		 *
		 * Container globalization:
		 *  ioc_closure_container::set_container('mycontainer', $container)
		 *  $container_b=ioc_closure_container::get_container('mycontainer')
		 *  ioc_closure_container::unset_container('mycontainer')
		 */

		protected $closure_container=array();
		protected $object_container=array();
		protected static $shared_containers=array();

		public function set($name, Closure $closure)
		{
			if(isset($this->closure_container[$name]))
				throw new Exception($name.' closure is already registered');
			$this->closure_container[$name]=$closure;
		}
		public function get($name)
		{
			if(isset($this->object_container[$name]))
				return $this->object_container[$name];

			if(!isset($this->closure_container[$name]))
				throw new Exception($name.' closure is not registered');
			return $this->closure_container[$name]($this);
		}
		public function share($name, Closure $closure)
		{
			if(isset($this->object_container[$name]))
				throw new Exception($name.' object is already registered');
			$this->object_container[$name]=$closure($this);
		}
		public function unset($name)
		{
			if(!isset($this->container[$name]))
				throw new Exception($name.' is not registered');
			unset($this->container[$name]);
		}

		public static function set_container($name, ioc_closure_container $container)
		{
			if(isset(static::$shared_containers[$name]))
				throw new Exception($name.' container is already shared');
			static::$shared_containers[$name]=$container;
		}
		public static function get_container($name)
		{
			if(!isset(static::$shared_containers[$name]))
				throw new Exception($name.' container is not shared');
			return static::$shared_containers[$name];
		}
		public static function unset_container($name)
		{
			if(!isset(static::$shared_containers[$name]))
				throw new Exception($name.' container is not saved');
			unset(static::$shared_containers[$name]);
		}
	}
	class ioc_autowired_container extends ioc_closure_container
	{
		/*
		 * Dependency Injection container
		 *
		 * Warning:
		 *  the constructor parameter for object instance requires a type hint
		 *  ioc_closure_container class is required
		 *  ReflectionParameter::getType is required
		 *
		 * Note:
		 *  this class can be inherited
		 *
		 * Usage:
		 *  initialization:
		 *   $container=new ioc_autowired_container() // without cache
		 *   $container=new ioc_autowired_container(true) // with cache
		 *  registering the constructor argument:
		 *   $container->register_constructor_arg('class_name', 'parameter_name', parameter_value)
		 *    where parameter_name is only used by the container
		 *    warning: the order of the parameters matters
		 *  unregistering the constructor argument:
		 *   $container->unregister_constructor_arg('class_name', 'parameter_name')
		 *  object initialization:
		 *   $myobject=$container->get('my_class')
		 *    returns new or cached instance of the object
		 * you can also use methods from the ioc_closure_container
		 *
		 * Container globalization:
		 *  see ioc_closure_container class
		 */

		protected $args=array();
		protected $do_cache;
		protected $cache=array();

		public function __construct($do_cache=false)
		{
			$this->do_cache=$do_cache;
		}

		public function get($name)
		{
			try
			{
				return parent::get($name);
			}
			catch(Exception $e) {}

			if(isset($this->cache[$name]))
				return $this->cache[$name];

			if(!class_exists($name))
				throw new Exception('class '.$name.' does not exists');

			$reflector=new ReflectionClass($name);
			if(!$reflector->isInstantiable())
				throw new Exception($name.' is not instantiable');

			$constructor=$reflector->getConstructor();
			if($constructor === null)
				return new $name();

			$dependencies=array();
			foreach($constructor->getParameters() as $dependency)
			{
				$dependency_type=$dependency->getType();
				if($dependency_type !== null)
					$dependencies[]=$this->get($dependency_type->getName());
			}

			if(isset($this->args[$name]))
				foreach($this->args[$name] as $constructor_arg)
					$dependencies[]=$constructor_arg;

			$instance=$reflector->newInstanceArgs($dependencies);
			if($this->do_cache)
				$this->cache[$name]=$instance;

			return $instance;
		}

		public function remove_from_cache($name)
		{
			if(!$this->do_cache)
				throw new Exception('cache is disabled');
			if(!isset($this->cache[$name]))
				throw new Exception($name.' is not cached');
			unset($this->cache[$name]);
		}

		public function register_constructor_arg($name, $arg_name, $arg)
		{
			$this->args[$name][$arg_name]=$arg;
			return $this;
		}
		public function unregister_constructor_arg($name, $arg_name)
		{
			if(!isset($this->args[$name][$arg_name]))
				throw new Exception($arg_name.' argument is not registered for '.$name);
			unset($this->args[$name][$arg_name]);

			return $this;
		}
	}
?>