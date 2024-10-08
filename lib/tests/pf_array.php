<?php
	/*
	 * pf_array.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		echo ' -> Mocking functions';
			function function_exists()
			{
				return false;
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php'
		] as $library){
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
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
		}

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
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

		echo ' -> Testing array_all';
			if(array_all(['foo@example.com', 'bar@example.com', 'baz@example.com'], function($value){
				return filter_var($value, FILTER_VALIDATE_EMAIL);
			}))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(array_all(['foo@example.com', 'bar@example.com', 'baz'], function($value){
				return filter_var($value, FILTER_VALIDATE_EMAIL);
			})){
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			if(array_all([1=>'', 2=>'', 3=>''], function($value, $key){
				return is_numeric($key);
			}))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing array_any';
			if(array_any(['foo@example.com', 'https://php.watch', 'foobar'], function($value){
				return filter_var($value, FILTER_VALIDATE_URL);
			}))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(array_any(['https://php.watch', new class(){}], function($value){
				return filter_var($value, FILTER_VALIDATE_EMAIL);
			})){
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			if(array_any([1=>'', 'bar'=>'', 'baz'=>''], function($value, $key){
				return is_numeric($key);
			}))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing array_find';
			if(array_find([1, 2, 3, 4, 5], function($value){
				return ($value%2 === 0);
			}) === 2)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(array_find(['a'=>'foo', 2=>'bar'], function($value, $key){
				return is_numeric($key);
			}) === 'bar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing array_find_key';
			if(array_find_key(['foo'=>1, 'bar'=>2, 'baz'=>3], function($value){
				return ($value%2 === 0);
			}) === 'bar')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(array_find_key(['a'=>'foo', 2=>'bar'], function($value, $key){
				return is_numeric($key);
			}) === 2)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing array_is_list'.PHP_EOL;
			echo '  -> returns true';
				if(array_is_list([]))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(array_is_list(['apple', 2, 3]))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(array_is_list([0 => 'apple', 'orange']))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false';
				// The array does not start at 0
				if(array_is_list([1 => 'apple', 'orange']))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				// The keys are not in the correct order
				if(array_is_list([1 => 'apple', 0 => 'orange']))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				// Non-integer keys
				if(array_is_list([0 => 'apple', 'foo' => 'bar']))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				// Non-consecutive keys
				if(array_is_list([0 => 'apple', 2 => 'bar']))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing array_key_first';
			if(array_key_first(['b'=>1, 'd'=>2, 'c'=>3]) === 'b')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing array_key_last';
			if(array_key_last(['b'=>1, 'd'=>2, 'a'=>3]) === 'a')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		if($failed)
			exit(1);
	}
?>