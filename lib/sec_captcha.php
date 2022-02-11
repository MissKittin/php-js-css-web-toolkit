<?php
	/*
	 * CAPTCHA protection library
	 *
	 * Warning:
	 *  captcha_check and captcha_get require started session
	 *  captcha_gd2 require gd extension
	 *  $_SESSION['captcha_token'] is reserved
	 *
	 * Functions:
	 *  captcha_get('module_name', ['module_param_a', 'module_param_b'])
	 *   where the second parameter is optional
	 *   saves the token from the module and returns the image
	 *   the module must return [string_token, bin_image]
	 *  captcha_check('captcha_token_from_user')
	 *   checks if the token from the session is the same as that from the user
	 *
	 * Modules:
	 *  captcha_gd2
	 *   uses the GD2 extension to generate the captcha image
	 *
	 * Example code:
		if((!isset($_POST['captcha'])) || (!captcha_check($_POST['captcha'])))
		{
			echo '<img src="data:image/jpeg;base64,'.base64_encode(captcha_get('captcha_gd2')).'" style="width: 400px; height: 80px;">';
			echo '<form action="" method="post"><input type="text" name="captcha"><input type="submit"></form>';
		}
		else
			echo 'CAPTCHA token is valid';
	 * or you can combine sec_captcha with check_var:
	 *  if(!captcha_check(check_post('captcha')))
	 */

	function captcha_gd2(string $encoding='jpeg')
	{
		/*
		 * Generates a token and a 100x20px image
		 *
		 * Warning:
		 *  gd extension is required
		 *
		 * Usage: image_captcha(string_image_format)
		 *  where string_image_format is optional and can be bmp gif png or jpeg (default)
		 *
		 * Source: https://stackoverflow.com/questions/5274563/php-imagecreate-error
		 */

		if(!extension_loaded('gd'))
			throw new Exception('gd extension is not loaded');

		$token_string=substr(md5(rand(0, 999)), 15, 5);

		$image_object=imagecreate(100, 20);

		$color_grey=imagecolorallocate($image_object, 204, 204, 204);
		imagefill($image_object, 0, 0, imagecolorallocate($image_object, 0, 0, 0));
		imagestring($image_object, 3, 30, 3, $token_string, imagecolorallocate($image_object, 255, 255, 255));
		imagerectangle($image_object, 0, 0, 99, 19, $color_grey);
		imageline($image_object, 0, 10, 100, 10, $color_grey);
		imageline($image_object, 50, 0, 50, 20, $color_grey);

		ob_start();
		switch($encoding)
		{
			case 'bmp': imagebmp($image_object); break;
			case 'gif': imagegif($image_object); break;
			case 'png': imagepng($image_object); break;
			default: imagejpeg($image_object);
		}
		$token_image=ob_get_clean();

		imagedestroy($image_object);

		return [$token_string, $token_image];
	}

	function captcha_get(callable $module, array $module_params=array())
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		$captcha=call_user_func_array($module, $module_params);
		$_SESSION['captcha_token']=$captcha[0];
		return $captcha[1];
	}
	function captcha_check(string $input_token)
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		if(!isset($_SESSION['captcha_token']))
			throw new Excaption('run captcha_get() first');

		if($_SESSION['captcha_token'] === $input_token)
			return true;
		return false;
	}
?>