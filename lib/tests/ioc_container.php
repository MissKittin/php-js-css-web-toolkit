<?php
	/*
	 * ioc_container.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	class requiredclass
	{
		private $param;

		public function __construct($param)
		{
			$this->param=$param;
		}

		public function get_param()
		{
			return $this->param;
		}
	}
	class testclass
	{
		private static $count=0;
		private $requiredclass;

		public function __construct(requiredclass $requiredclass)
		{
			++static::$count;
			$this->requiredclass=$requiredclass;
		}

		public function get_count()
		{
			return static::$count;
		}
		public function get_requiredclass()
		{
			return $this->requiredclass;
		}

		public static function reset_count()
		{
			static::$count=0;
		}
	}

	$failed=false;

	echo ' -> Testing ioc_closure_container'.PHP_EOL;
		$closure_container=new ioc_closure_container();
		echo '  -> set/unset';
			$closure_container->set('set', function(){
				return new testclass(new requiredclass(null));
			});
			$closure_container->get('set');
			if($closure_container->get('set')->get_count() === 2)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			$closure_container->unset('set');
		echo '  -> share/unshare';
			testclass::reset_count();
			$closure_container->share('share', function(){
				return new testclass(new requiredclass(null));
			});
			$closure_container->get('share');
			if($closure_container->get('share')->get_count() === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> global';
			ioc_closure_container::set_container('mycontainer', $closure_container);
			$closure_container_b=ioc_closure_container::get_container('mycontainer');
			ioc_closure_container::unset_container('mycontainer');
			if($closure_container_b->get('share')->get_count() === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> unshare';
			$closure_container->unshare('share');
			echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing ioc_autowired_container'.PHP_EOL;
		echo '  -> without cache';
			testclass::reset_count();
			$autowired_container=new ioc_autowired_container(false);
			$autowired_container->register_constructor_arg('requiredclass', 'parameter', 'ok ok');
			if($autowired_container->get('testclass')->get_requiredclass()->get_param() === 'ok ok')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if($autowired_container->get('testclass')->get_count() === 2)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			echo '   -> unregister argument';
				$exception_occured=false;
				$autowired_container->unregister_constructor_arg('requiredclass', 'parameter');
				try {
					$autowired_container->get('testclass')->get_requiredclass()->get_param();
				} catch(ArgumentCountError $error) {
					$exception_occured=true;
				}
				if($exception_occured)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
		echo '  -> with cache';
			testclass::reset_count();
			$autowired_container_cache=new ioc_autowired_container(true);
			$autowired_container_cache->register_constructor_arg('requiredclass', 'parameter', 'ok ok');
			if($autowired_container_cache->get('testclass')->get_requiredclass()->get_param() === 'ok ok')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if($autowired_container_cache->get('testclass')->get_count() === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			echo '   -> unregister argument';
				$exception_occured=false;
				$autowired_container_cache->remove_from_cache('testclass');
				$autowired_container_cache->remove_from_cache('requiredclass');
				$autowired_container_cache->unregister_constructor_arg('requiredclass', 'parameter');
				try {
					$autowired_container_cache->get('testclass')->get_requiredclass()->get_param();
				} catch(ArgumentCountError $error) {
					$exception_occured=true;
				}
				if($exception_occured)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

	if($failed)
		exit(1);
?>