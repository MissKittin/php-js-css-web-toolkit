<?php
	/*
	 * sec_csrf.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	namespace Test
	{
		echo ' -> Mocking functions and classes';
			class Exception extends \Exception {}
			function session_status()
			{
				return PHP_SESSION_ACTIVE;
			}
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