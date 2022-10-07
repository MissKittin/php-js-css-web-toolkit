<?php
	/*
	 * sec_captcha.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  gd extension is recommended
	 *  imagick extension is recommended
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

		echo ' -> Mocking functions and classes';
			function session_status()
			{
				return PHP_SESSION_ACTIVE;
			}
			if(extension_loaded('imagick'))
			{
				class Imagick extends \Imagick {}
				class ImagickDraw extends \ImagickDraw {}
				class ImagickPixel extends \ImagickPixel {}
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
		$no_extensions=[
			'gd2'=>false,
			'imagick'=>false
		];

		echo ' -> Testing gd2 module';
		if(extension_loaded('gd'))
		{
			echo PHP_EOL;

			foreach(['jpeg', 'bmp', 'gif', 'png'] as $format)
			{
				echo '  -> Testing '.$format.PHP_EOL;

				echo '   -> captcha_get';
					$image=captcha_get('Test\captcha_gd2', [$format]);
					file_put_contents(__DIR__.'/tmp/sec_captcha_gd2-'.$_SESSION['_captcha']['token'].'.'.$format, $image);
					if(!empty($image))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}

				echo '   -> captcha_check';
					if(captcha_check($_SESSION['_captcha']['token']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(!isset($_SESSION['_captcha']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL.' <-  gd2 extension is not loaded'.PHP_EOL;
			$no_extensions['gd2']=true;
		}

		echo ' -> Testing imagick module';
		if(extension_loaded('imagick'))
			try {
				echo PHP_EOL;

				foreach(['jpeg', 'bmp', 'gif', 'png'] as $format)
				{
					echo '  -> Testing '.$format.PHP_EOL;

					echo '   -> captcha_get';
						$image=captcha_get('Test\captcha_imagick', [$format]);
						file_put_contents(__DIR__.'/tmp/sec_captcha_imagick-'.$_SESSION['_captcha']['token'].'.'.$format, $image);
						if(!empty($image))
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$failed=true;
						}

					echo '   -> captcha_check';
						if(captcha_check($_SESSION['_captcha']['token']))
							echo ' [ OK ]';
						else
						{
							echo ' [FAIL]';
							$failed=true;
						}
						if(!isset($_SESSION['_captcha']))
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$failed=true;
						}
				}
			} catch(\Throwable $error) {
				echo PHP_EOL.'  <- error: '.$error->getMessage().PHP_EOL;
			}
		else
		{
			echo ' [FAIL]'.PHP_EOL.' <-  imagick extension is not loaded'.PHP_EOL;
			$no_extensions['imagick']=true;
		}

		echo ' -> Testing imagick2 module';
		if(extension_loaded('imagick'))
			try {
				echo PHP_EOL;

				foreach(['jpeg', 'bmp', 'gif', 'png'] as $format)
				{
					echo '  -> Testing '.$format.PHP_EOL;

					echo '   -> captcha_get';
						$image=captcha_get('Test\captcha_imagick2', [$format]);
						file_put_contents(__DIR__.'/tmp/sec_captcha_imagick2-'.$_SESSION['_captcha']['token'].'.'.$format, $image);
						if(!empty($image))
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$failed=true;
						}

					echo '   -> captcha_check';
						if(captcha_check($_SESSION['_captcha']['token']))
							echo ' [ OK ]';
						else
						{
							echo ' [FAIL]';
							$failed=true;
						}
						if(!isset($_SESSION['_captcha']))
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$failed=true;
						}
				}
			} catch(\Throwable $error) {
				echo PHP_EOL.'  <- error: '.$error->getMessage().PHP_EOL;
			}
		else
		{
			echo ' [FAIL]'.PHP_EOL.' <-  imagick extension is not loaded'.PHP_EOL;
			$no_extensions['imagick']=true;
		}

		if($failed)
			exit(1);

		$failed=true;
		foreach($no_extensions as $no_extension)
			if($no_extension === false)
			{
				$failed=false;
				break;
			}

		if($failed)
			exit(1);
	}
?>