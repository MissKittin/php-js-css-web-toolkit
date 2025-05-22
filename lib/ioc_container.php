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
	 *
	 * PSR-11:
	 *  you need an adapter compatible with the version of the psr/container package you are using, e.g.
		trait t_ioc_container_psr
		{
			protected $container;

			public function __call($name, $arguments)
			{
				return $this->container->$name(
					...$arguments
				);
			}

			public function get(string $id) // edit this line
			{
				try {
					return $this->container->get(
						$id
					);
				} catch(ioc_container_exception $error) {
					throw new Psr\Container\NotFoundExceptionInterface(
						$error->getMessage()
					);
				}
			}
			public function has(string $id): bool // edit this line
			{
				try {
					$this->container->get(
						$id
					);
				} catch(ioc_container_exception $error) {
					return false;
				}

				return true;
			}
		}

		class ioc_closure_container_psr
		implements Psr\Container\ContainerInterface
		{
			use t_ioc_container_psr;

			public function __construct()
			{
				$this->container=new ioc_closure_container();
			}
		}
		class ioc_autowired_container_psr
		implements Psr\Container\ContainerInterface
		{
			use t_ioc_container_psr;

			public function __construct(bool $do_cache=false)
			{
				$this->container=new ioc_autowired_container(
					$do_cache
				);
			}
		}
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
			// hint: you can use something like this
			//  together with ioc_closure_container::get_container('mycontainer')->method()
			ioc_closure_container::set_container('mycontainer', new ioc_closure_container());

			$container // set() and share() returns self
			->	set('class_a', function(){
					return new class_a();
				})
			->	share('class_b', function($container){
					// class_b wants class_a instance

					return new class_b(
						$container->get('class_a')
					);
				});

			// get class_b instance
			$myclassb=$container->get('class_b');
			$myclassb->do_someting();

			// remove class_a and class_b from registry (optional)
			$container // unset() and unshare() returns self
			->	unset('class_a')
			->	unshare('class_b');
		 * where set() saves the closure and executes it on demand
		 *  each get() returns new object instance
		 * and share() executes the closure and saves the object
		 *  the same object instance will be returned from get()
		 *
		 * Container globalization:
			ioc_closure_container::set_container('mycontainer', $container); // returns self
			$container_b=ioc_closure_container::get_container('mycontainer');
			ioc_closure_container::unset_container('mycontainer'); // returns self
		 */

		protected $closure_container=[];
		protected $object_container=[];
		protected static $shared_containers=[];

		public function set(
			string $name,
			Closure $closure
		){
			if(isset(
				$this->closure_container[$name]
			))
				throw new ioc_container_exception(
					$name.' closure is already registered'
				);

			$this->closure_container[$name]=$closure;

			return $this;
		}
		public function get(string $name)
		{
			if(isset(
				$this->object_container[$name]
			))
				return $this->object_container[$name];

			if(!isset(
				$this->closure_container[$name]
			))
				throw new ioc_container_exception(
					$name.' closure is not registered'
				);

			return $this->closure_container[$name]($this);
		}
		public function share(
			string $name,
			Closure $closure
		){
			if(isset(
				$this->object_container[$name]
			))
				throw new ioc_container_exception(
					$name.' object is already registered'
				);

			$this->object_container[$name]=$closure(
				$this
			);

			return $this;
		}
		public function unset(string $name)
		{
			if(!isset(
				$this->closure_container[$name]
			))
				throw new ioc_container_exception(
					$name.' is not registered'
				);

			unset(
				$this->closure_container[$name]
			);

			return $this;
		}
		public function unshare(string $name)
		{
			if(!isset(
				$this->object_container[$name]
			))
				throw new ioc_container_exception(
					$name.' is not registered'
				);

			unset(
				$this->object_container[$name]
			);

			return $this;
		}

		public static function set_container(
			string $name,
			ioc_closure_container $container
		){
			if(isset(
				static::$shared_containers[$name]
			))
				throw new ioc_container_exception(
					$name.' container is already shared'
				);

			static::$shared_containers[$name]=$container;

			return static::class;
		}
		public static function get_container(string $name)
		{
			if(!isset(
				static::$shared_containers[$name]
			))
				throw new ioc_container_exception(
					$name.' container is not shared'
				);

			return static::$shared_containers[$name];
		}
		public static function unset_container(string $name)
		{
			if(!isset(
				static::$shared_containers[$name]
			))
				throw new ioc_container_exception(
					$name.' container is not saved'
				);

			unset(
				static::$shared_containers[$name]
			);

			return static::class;
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
		 *  ReflectionParameter::getType method is required
		 *
		 * Note:
		 *  throws an ioc_container_exception on error
		 *
		 * Initialization:
			$container=new ioc_autowired_container(); // without cache
			$container=new ioc_autowired_container(true); // with cache
		 *
		 * Registering the constructor argument:
			$container->register_constructor_arg('class_name', 'parameter_name', parameter_value); // returns self
			$container->register_constructor_arg('my_model', 'PDO', new PDO('sqlite::memory:'));
		 *  where parameter_name is only used by the container
		 *  warning: the order of the parameters matters
		 *
		 * Unregistering the constructor argument:
			$container->unregister_constructor_arg('class_name', 'parameter_name'); // returns self
		 *
		 * Object initialization:
			$myobject=$container->get('my_class');
		 *  returns new or cached instance of the object
		 *
		 * You can also use methods from the ioc_closure_container
		 *
		 * Container globalization:
			ioc_autowired_container::set_container('mycontainer', $container); // returns self
			$container_b=ioc_autowired_container::get_container('mycontainer');
			ioc_autowired_container::unset_container('mycontainer'); // returns self
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
				return parent::{__FUNCTION__}(
					$name
				);
			} catch(ioc_container_exception $error) {
				// closure is not registered in $this->closure_container
				// keep going
			}

			if(isset(
				$this->cache[$name]
			))
				return $this->cache[$name];

			if(!class_exists($name))
				throw new ioc_container_exception(
					'Class '.$name.' does not exists'
				);

			$reflector=new ReflectionClass($name);

			if(!$reflector->isInstantiable())
				throw new ioc_container_exception(
					$name.' is not instantiable'
				);

			$constructor=$reflector->getConstructor();

			if($constructor === null)
				return new $name();

			$dependencies=[];

			foreach($constructor->getParameters() as $dependency)
			{
				$dependency_type=$dependency->getType();

				if($dependency_type !== null)
					$dependencies[]=$this->get(
						$dependency_type->getName()
					);
			}

			if(isset(
				$this->args[$name]
			))
				foreach($this->args[$name] as $constructor_arg)
					$dependencies[]=$constructor_arg;

			$instance=$reflector->newInstanceArgs(
				$dependencies
			);

			if($this->do_cache)
				$this->cache[$name]=$instance;

			return $instance;
		}

		public function remove_from_cache(string $name)
		{
			if(!$this->do_cache)
				throw new ioc_container_exception(
					'Cache is disabled'
				);

			if(!isset(
				$this->cache[$name]
			))
				throw new ioc_container_exception(
					$name.' is not cached'
				);

			unset(
				$this->cache[$name]
			);

			return $this;
		}

		public function register_constructor_arg(
			string $name,
			string $arg_name,
			$arg
		){
			$this->args[$name][$arg_name]=$arg;
			return $this;
		}
		public function unregister_constructor_arg(
			string $name,
			string $arg_name
		){
			if(!isset(
				$this->args[$name][$arg_name]
			))
				throw new ioc_container_exception(
					$arg_name.' argument is not registered for '.$name
				);

			unset(
				$this->args[$name][$arg_name]
			);

			return $this;
		}
	}
?>