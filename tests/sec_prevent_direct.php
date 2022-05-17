<?php
	/*
	 * sec_prevent_direct.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	namespace Test
	{
		echo ' -> Mocking functions';
			function http_response_code($code)
			{
				$GLOBALS['http_response_code']=$code;
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Mocking superglobals';
			$_SERVER['REQUEST_URI']='/goodscript?trash';
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