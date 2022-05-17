<?php
	/*
	 * getallheaders.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	namespace Test
	{
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

		echo ' -> Including '.basename(__FILE__);
			if(!file_exists(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

			eval(
				'namespace Test { ?>'
					.file_get_contents(__DIR__.'/../lib/'.basename(__FILE__))
				.'<?php }'
			);
		echo ' [ OK ]'.PHP_EOL;

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