<?php
	/*
	 * sec_http_basic_auth.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	namespace Test
	{
		echo ' -> Mocking functions';
			function header($header)
			{
				$GLOBALS['http_headers'][]=$header;
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Mocking superglobals';
			$_SERVER['PHP_AUTH_USER']='gooduser';
			$_SERVER['PHP_AUTH_PW']='goodpassword';
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

		echo ' -> Testing library'.PHP_EOL;
		echo '  -> sent, returns true';
			if(http_basic_auth('gooduser', 'goodpassword', 'realm'))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sent, returns false';
			$GLOBALS['http_headers']=[];
			if(http_basic_auth('baduser', 'badpassword', 'realm'))
			{
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			if(empty($GLOBALS['http_headers']))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> ask, returns false';
			unset($_SERVER['PHP_AUTH_USER']);
			unset($_SERVER['PHP_AUTH_PW']);
			$GLOBALS['http_headers']=[];
			if(http_basic_auth('gooduser', 'goodpassword', 'realm'))
			{
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			if($GLOBALS['http_headers'][0] === 'WWW-Authenticate: Basic realm="realm"')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if($GLOBALS['http_headers'][1] === 'HTTP/1.0 401 Unauthorized')
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