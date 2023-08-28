<?php
	/*
	 * include_into_namespace.php library test
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

	$failed=false;

	echo ' -> Testing code with function and close tag [A]';
		include_into_namespace(
			'test_namespace',
			'<?php
				function test_function_a() {}
			?>',
			true
		);
		if(function_exists('test_namespace\test_function_a'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing code with function and without close tag [A]';
		include_into_namespace(
			'test_namespace',
			'<?php
				function test_function_b() {}
			',
			false
		);
		if(function_exists('test_namespace\test_function_b'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing code with class and close tag [A]';
		include_into_namespace(
			'test_namespace',
			'<?php
				class test_class {}
			?>',
			true
		);
		if(class_exists('test_namespace\test_class'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing code with class and close tag [B]';
		include_into_namespace(
			'test_namespace',
			'<?php
				class test_class_b {}
			?>',
			null
		);
		if(class_exists('test_namespace\test_class_b'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	//var_dump(get_defined_functions()['user']);
	//var_dump(get_declared_classes());

	if($failed)
		exit(1);
?>