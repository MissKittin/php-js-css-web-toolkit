<?php
	/*
	 * sec_captcha.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  gd extension is required
	 */

	namespace Test
	{
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