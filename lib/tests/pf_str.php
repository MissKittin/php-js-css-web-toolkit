<?php
	/*
	 * pf_str.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  pf_ValueError.php library is required
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
			'include_into_namespace.php',
			'pf_ValueError.php'
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

		echo ' -> Mocking classes';
			class ValueError extends \ValueError {}
		echo ' [ OK ]'.PHP_EOL;

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

		echo ' -> Testing str_contains'.PHP_EOL;
			echo '  -> returns true';
				if(str_contains('abcdefghi', 'abc'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_contains('abcdefghi', 'def'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_contains('abcdefghi', 'ghi'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_contains('abcdefghi', 'abcdefghi'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false (haystack shorter)';
				if(str_contains('def', 'abcdefghi'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing str_decrement';
			try {
				str_decrement('A');
				echo ' [FAIL]';
				$failed=true;
			} catch(ValueError $error) {
				echo ' [ OK ]';
			}
			try {
				if(str_decrement('ABC') === 'ABB')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_decrement('ZA') === 'YZ')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_decrement('AA') === 'Z')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(ValueError $error) {
				echo ' [FAIL] (caught)'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing str_ends_with'.PHP_EOL;
			echo '  -> returns true';
				if(str_ends_with('abcdefghi', 'ghi'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_ends_with('abcdefghi', 'abcdefghi'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false';
				if(str_ends_with('abcdefghi', 'xyz'))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				if(str_ends_with('def', 'abcdefghi'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing str_increment';
			if(str_increment('ABC') === 'ABD')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(str_increment('DZ') === 'EA')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(str_increment('ZZ') === 'AAA')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing str_starts_with'.PHP_EOL;
			echo '  -> returns true';
				if(str_starts_with('abcdefghi', 'abc'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(str_starts_with('abcdefghi', 'abcdefghi'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false';
				if(str_starts_with('abcdefghi', 'xyz'))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				if(str_starts_with('def', 'abcdefghi'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;

		if($failed)
			exit(1);
	}
?>