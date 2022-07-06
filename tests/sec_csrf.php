<?php
	/*
	 * sec_csrf.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
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

		echo ' -> Mocking functions and classes';
			class Exception extends \Exception {}
			function session_status()
			{
				return PHP_SESSION_ACTIVE;
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(_include_tested_library(
				__NAMESPACE__,
				__DIR__.'/../lib/'.basename(__FILE__)
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

		$failed=false;

		echo ' -> Testing csrf_check_token'.PHP_EOL;
		echo '  -> GET';
			$_GET['csrf_token']=$_SESSION['csrf_token'];
			if(csrf_check_token('get'))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> POST';
			$_POST['csrf_token']=$_SESSION['csrf_token'];
			if(csrf_check_token('post'))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing csrf_print_token';
			if(csrf_print_token('value') === $_SESSION['csrf_token'])
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