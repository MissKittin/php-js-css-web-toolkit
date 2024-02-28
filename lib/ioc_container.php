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

	class ioc_container_exception extends Exception {}
	class ioc_closure_container
	{
		/*
		 * Dependency Injection container
		 *
		 * Note:
		 *  throws an ioc_container_exception on error
		 *
		 * Usage:
			$container=new ioc_closure_container();
			// here you can globalize the container
			$container->set('class_a', function(){
				return new class_a();
			});
			$container->share('class_b', function($container){
				return new class_b($container->get('class_a'));
			});
			$myclassb=$container->get('class_b');
			$myclassb->do_someting();
			$container->unset('class_a'); // optional
			$container->unshare('class_b'); // optional
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

		protected $closure_container=[];
		protected $object_container=[];
		protected static $shared_containers=[];

		public function set(string $name, Closure $closure)
		{
			if(isset($this->closure_container[$name]))
				throw new ioc_container_exception($name.' closure is already registered');

			$this->closure_container[$name]=$closure;
		}
		public function get(string $name)
		{
			if(isset($this->object_container[$name]))
				return $this->object_container[$name];

			if(!isset($this->closure_container[$name]))
				throw new ioc_container_exception($name.' closure is not registered');

			return $this->closure_container[$name]($this);
		}
		public function share(string $name, Closure $closure)
		{
			if(isset($this->object_container[$name]))
				throw new ioc_container_exception($name.' object is already registered');

			$this->object_container[$name]=$closure($this);
		}
		public function unset(string $name)
		{
			if(!isset($this->closure_container[$name]))
				throw new ioc_container_exception($name.' is not registered');

			unset($this->closure_container[$name]);
		}
		public function unshare(string $name)
		{
			if(!isset($this->object_container[$name]))
				throw new ioc_container_exception($name.' is not registered');

			unset($this->object_container[$name]);
		}

		public static function set_container(string $name, ioc_closure_container $container)
		{
			if(isset(static::$shared_containers[$name]))
				throw new ioc_container_exception($name.' container is already shared');

			static::$shared_containers[$name]=$container;
		}
		public static function get_container(string $name)
		{
			if(!isset(static::$shared_containers[$name]))
				throw new ioc_container_exception($name.' container is not shared');

			return static::$shared_containers[$name];
		}
		public static function unset_container(string $name)
		{
			if(!isset(static::$shared_containers[$name]))
				throw new ioc_container_exception($name.' container is not saved');

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
		 *  throws an ioc_container_exception on error
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

		protected $args=[];
		protected $do_cache;
		protected $cache=[];

		public function __construct(bool $do_cache=false)
		{
			$this->do_cache=$do_cache;
		}

		public function get(string $name)
		{
			try {
				return parent::{__FUNCTION__}($name);
			} catch(ioc_container_exception $error) {
				// closure is not registered in $this->closure_container
				// keep going
			}

			if(isset($this->cache[$name]))
				return $this->cache[$name];

			if(!class_exists($name))
				throw new ioc_container_exception('Class '.$name.' does not exists');

			$reflector=new ReflectionClass($name);
			if(!$reflector->isInstantiable())
				throw new ioc_container_exception($name.' is not instantiable');

			$constructor=$reflector->getConstructor();
			if($constructor === null)
				return new $name();

			$dependencies=[];
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

		public function remove_from_cache(string $name)
		{
			if(!$this->do_cache)
				throw new ioc_container_exception('Cache is disabled');

			if(!isset($this->cache[$name]))
				throw new ioc_container_exception($name.' is not cached');

			unset($this->cache[$name]);
		}

		public function register_constructor_arg(string $name, string $arg_name, $arg)
		{
			$this->args[$name][$arg_name]=$arg;
			return $this;
		}
		public function unregister_constructor_arg(string $name, string $arg_name)
		{
			if(!isset($this->args[$name][$arg_name]))
				throw new ioc_container_exception($arg_name.' argument is not registered for '.$name);

			unset($this->args[$name][$arg_name]);

			return $this;
		}
	}
?>