<?php
	/*
	 * getallheaders.php library test
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
			function function_exists()
			{
				return false;
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Mocking superglobals';
			$_SERVER['HTTP_MY_HEADER_A']='Value a';
			$_SERVER['HTTP_MY_HEADER_B']='Value b';
			$_SERVER['HTTP_MY_HEADER_C']='Value c';
			$_SERVER['HTTP_MY_HEADER_D']='Value d';
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

		echo ' -> Testing library';
			if(str_replace(["\n", ' '], '', var_export(getallheaders(), true)) === "array('My-Header-A'=>'Valuea','My-Header-B'=>'Valueb','My-Header-C'=>'Valuec','My-Header-D'=>'Valued',)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
	}
?>