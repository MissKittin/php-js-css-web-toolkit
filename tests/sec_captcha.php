<?php
	/*
	 * sec_captcha.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  gd extension is required
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

		if(!extension_loaded('gd'))
		{
			echo 'gd extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Mocking functions';
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

		@mkdir(__DIR__.'/tmp');
		$failed=false;

		foreach(['jpeg', 'bmp', 'gif', 'png'] as $format)
		{
			echo ' -> Testing '.$format.PHP_EOL;

			echo '  -> captcha_get';
				$image=captcha_get('Test\captcha_gd2', [$format]);
				file_put_contents(__DIR__.'/tmp/sec_captcha-'.$_SESSION['captcha_token'].'.'.$format, $image);
				if(!empty($image))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

			echo '  -> captcha_check';
				if(captcha_check($_SESSION['captcha_token']))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
		}

		if($failed)
			exit(1);
	}
?>