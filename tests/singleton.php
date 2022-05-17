<?php
	/*
	 * singleton.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$GLOBALS['init_called']=false;
	class singleton_test
	{
		use singleton;

		public $iterations=0;

		protected function init()
		{
			$GLOBALS['init_called']=true;
		}

		public function increment()
		{
			++$this->iterations;
		}
	}

	$failed=false;

	echo ' -> Testing constructor';
		$caught=false;
		try {
			$singleton_construct=new singleton_test();
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

	echo ' -> Testing get_instance';
		$singleton_instance_a=singleton_test::get_instance();
		$singleton_instance_a->increment();
		$singleton_instance_b=singleton_test::get_instance();
		$singleton_instance_b->increment();
		if($GLOBALS['init_called'])
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

	echo ' -> Testing clone';
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

	echo ' -> Testing wakeup';
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

	if($failed)
		exit(1);
?>