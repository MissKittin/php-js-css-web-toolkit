<?php
	/*
	 * rand_str.php library test
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