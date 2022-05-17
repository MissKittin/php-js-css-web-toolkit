<?php
	/*
	 * rand_str.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	namespace Test
	{
		echo ' -> Mocking functions';
			function rand()
			{
				static $number=0;
				$number+=8;

				return $number;
			}
			function random_bytes()
			{
				return 'iqyGOW4#-:';
			}
			function openssl_random_pseudo_bytes()
			{
				return 'iqyGOW4#-:';
			}
			function extension_loaded()
			{
				return true;
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

		echo ' -> Testing rand_str';
			if(rand_str(10) === 'iqyGOW4#-:')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing rand_str_secure';
			foreach([false, true] as $openssl)
				if(rand_str_secure(10, $openssl) === '697179474f')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
			echo PHP_EOL;

		if($failed)
			exit(1);
	}
?>