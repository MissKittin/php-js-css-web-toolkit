<?php
	/*
	 * sec_prevent_direct.php library test
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

		echo ' -> Mocking functions';
			function http_response_code($code)
			{
				$GLOBALS['http_response_code']=$code;
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Mocking superglobals';
			$_SERVER['REQUEST_URI']='/goodscript?trash';
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

		echo ' -> Testing prevent_index';
			$GLOBALS['http_response_code']='';
			ob_start();
			prevent_index('good value', 'echo');
			if((ob_get_clean() === 'good value') && ($GLOBALS['http_response_code'] === 404))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing prevent_direct'.PHP_EOL;
		echo '  -> checking failed';
			$GLOBALS['http_response_code']='';
			prevent_direct('badscript');
			if($GLOBALS['http_response_code'] === '')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		if($failed)
		{
			echo PHP_EOL.'Exiting due to previous errors'.PHP_EOL;
			exit(1);
		}

		echo '  -> checking success (now exit)'.PHP_EOL;
			prevent_direct('goodscript');
			echo '  -> checking success [FAIL]'.PHP_EOL;
			exit(1);
	}
?>