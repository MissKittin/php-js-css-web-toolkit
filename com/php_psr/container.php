<?php
	if(!class_exists('php_psr_exception'))
		require __DIR__.'/bootstrap.php';

	_php_psr_load_library('class', 'ioc_container.php', 'ioc_closure_container');

	class ioc_container_exception_psr
	extends Exception
	implements Psr\Container\NotFoundExceptionInterface
	{}

	trait t_psr_container
	{
		protected $container;

		public function __call($name, $arguments)
		{
			return $this->container->$name(
				...$arguments
			);
		}

		protected function _get($id)
		{
			try {
				return $this->container->get(
					$id
				);
			} catch(ioc_container_exception $error) {
				throw new ioc_container_exception_psr(
					$error->getMessage()
				);
			}
		}
		protected function _has($id)
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

	(function(){
		$type_hints=false;
		$return_type=false;

		try {
			$interface=(new ReflectionClass(
				'\Psr\Container\ContainerInterface'
			))->getMethod('has');
		} catch(ReflectionException $error) {
			throw new php_psr_exception(''
			.	'psr/container package is not installed '
			.	'('.$error->getMessage().')'
			);
		}

		if($interface->getParameters()[0]->getType() !== null)
			$type_hints=true;

		if($interface->getReturnType() !== null)
			$return_type=true;

		// since 2.0
		if(
			$type_hints &&
			$return_type
		){
			class ioc_closure_container_psr
			implements Psr\Container\ContainerInterface
			{
				use t_psr_container;

				public function __construct()
				{
					$this->container=new ioc_closure_container();
				}

				public function get(string $id)
				{
					return $this->_get($id);
				}
				public function has(string $id): bool
				{
					return $this->_has($id);
				}
			}

			class ioc_autowired_container_psr
			implements Psr\Container\ContainerInterface
			{
				use t_psr_container;

				public function __construct(bool $do_cache=false)
				{
					$this->container=new ioc_autowired_container(
						$do_cache
					);
				}

				public function get(string $id)
				{
					return $this->_get($id);
				}
				public function has(string $id): bool
				{
					return $this->_has($id);
				}
			}

			return;
		}

		// since 1.1
		if(
			$type_hints &&
			(!$return_type)
		){
			class ioc_closure_container_psr
			implements Psr\Container\ContainerInterface
			{
				use t_psr_container;

				public function __construct()
				{
					$this->container=new ioc_closure_container();
				}

				public function get(string $id)
				{
					return $this->_get($id);
				}
				public function has(string $id)
				{
					return $this->_has($id);
				}
			}

			class ioc_autowired_container_psr
			implements Psr\Container\ContainerInterface
			{
				use t_psr_container;

				public function __construct(bool $do_cache=false)
				{
					$this->container=new ioc_autowired_container(
						$do_cache
					);
				}

				public function get(string $id)
				{
					return $this->_get($id);
				}
				public function has(string $id)
				{
					return $this->_has($id);
				}
			}

			return;
		}

		// old versions
		if(
			(!$type_hints) &&
			(!$return_type)
		){
			class ioc_closure_container_psr
			implements Psr\Container\ContainerInterface
			{
				use t_psr_container;

				public function __construct()
				{
					$this->container=new ioc_closure_container();
				}

				public function get($id)
				{
					return $this->_get($id);
				}
				public function has($id)
				{
					return $this->_has($id);
				}
			}

			class ioc_autowired_container_psr
			implements Psr\Container\ContainerInterface
			{
				use t_psr_container;

				public function __construct(bool $do_cache=false)
				{
					$this->container=new ioc_autowired_container(
						$do_cache
					);
				}

				public function get($id)
				{
					return $this->_get($id);
				}
				public function has($id)
				{
					return $this->_has($id);
				}
			}

			return;
		}

		throw new php_psr_exception(
			'Unknown psr/container version'
		);
	})();
?>