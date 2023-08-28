<?php
	/*
	 * singleton.php library test
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

	$GLOBALS['trait_init_called']=false;
	class singleton_trait_test
	{
		use t_singleton;

		public $iterations=0;

		protected function init()
		{
			$GLOBALS['trait_init_called']=true;
		}

		public function increment()
		{
			++$this->iterations;
		}
	}

	$GLOBALS['abstract_init_called']=false;
	class singleton_abstract_test extends a_singleton
	{
		public $iterations=0;

		protected function init()
		{
			$GLOBALS['abstract_init_called']=true;
		}

		public function increment()
		{
			++$this->iterations;
		}
	}

	$failed=false;

	foreach(['trait', 'abstract'] as $class)
	{
		$class_name='singleton_'.$class.'_test';

		echo ' -> Testing '.$class.' constructor';
			$caught=false;
			try {
				$singleton_construct=new $class_name();
			} catch(Throwable $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing '.$class.' get_instance';
			$singleton_instance_a=$class_name::get_instance();
			$singleton_instance_a->increment();
			$singleton_instance_b=$class_name::get_instance();
			$singleton_instance_b->increment();
			if($GLOBALS[$class.'_init_called'])
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if($singleton_instance_a->iterations === 2)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing '.$class.' clone';
			$caught=false;
			try {
				$singleton_construct=clone $singleton_instance_a;
			} catch(Throwable $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing '.$class.' wakeup';
			$singleton_wakeup=serialize($singleton_instance_a);
			$caught=false;
			try {
				unserialize($singleton_wakeup);
			} catch(Throwable $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
	}

	if($failed)
		exit(1);
?>