<?php
	/*
	 * CAPTCHA protection library
	 *
	 * Warning:
	 *  captcha_check and captcha_get requires started session
	 *  captcha_gd2 requires gd extension
	 *  captcha_imagick and captcha_imagick2 requires imagick extension
	 *  $_SESSION['_captcha'] is reserved
	 *
	 * Note:
	 *  throws an sec_captcha_exception on error
	 *
	 * Functions:
	 *  captcha_get('module_name', ['module_param_a', 'module_param_b'])
	 *   where the second parameter is optional
	 *   saves the token from the module and returns the image
	 *   the module must return [string_token, bin_image]
	 *  captcha_get_once('module_name', ['module_param_a', 'module_param_b'])
	 *    additionally saves the image to the session to save cpu
	 *    in case of wrongly given token
	 *  captcha_check('captcha_token_from_user')
	 *   checks if the token from the session is the same as that from the user
	 *   note: removes the $_SESSION['_captcha'] array when the token is entered correctly
	 *
	 * Modules:
	 *  captcha_gd2
	 *   uses GD2 to generate the captcha image
	 *   warning:
	 *    gd adds a comment like "CREATOR: gd-jpeg v1.0 (using IJG JPEG v90), default quality" to the file
	 *    if you don't want this, don't use this module
	 *  captcha_imagick
	 *   uses ImageMagick to generate the captcha image
	 *   Imagick version of captcha_gd2
	 *  captcha_imagick2
	 *   A very primitive captcha implementation
	 *   License: The PHP License, version 3.01 https://www.php.net/license/3_01.txt
	 *
	 * Example code:
		if((!isset($_POST['captcha'])) || (!captcha_check($_POST['captcha'])))
		{
			echo '<img src="data:image/jpeg;base64,'.base64_encode(captcha_get(new captcha_gd2())).'" style="width: 400px; height: 80px;">';
			echo '<form action="" method="post"><input type="text" name="captcha"><input type="submit"></form>';
		}
		else
			echo 'CAPTCHA token is valid';
	 *
	 * Hint:
	 *  you can combine sec_captcha with check_var.php library:
	 *  if(!captcha_check(check_post('captcha')))
	 */

	class sec_captcha_exception extends Exception {}

	interface captcha_module
	{
		public function generate_token(): string;
		public function generate_image(string $token): string;
	}

	function captcha_get(captcha_module $module)
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new sec_captcha_exception('Session not started');

		$_SESSION['_captcha']['token']=$module->generate_token();

		return $module->generate_image($_SESSION['_captcha']['token']);
	}
	function captcha_get_once(captcha_module $module)
	{
		if(!isset($_SESSION['_captcha']['image']))
			$_SESSION['_captcha']['image']=captcha_get($module);

		return $_SESSION['_captcha']['image'];
	}
	function captcha_check(string $token)
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new sec_captcha_exception(
				'Session not started'
			);

		if(!isset($_SESSION['_captcha']['token']))
			throw new sec_captcha_exception(
				'Run captcha_get() or captcha_get_once() first'
			);

		if($_SESSION['_captcha']['token'] === $token)
		{
			unset($_SESSION['_captcha']);
			return true;
		}

		return false;
	}

	class captcha_gd2 implements captcha_module
	{
		/*
		 * Generates a token and a 100x20px image
		 *
		 * Warning:
		 *  gd extension is required
		 *  gd adds a comment like "CREATOR: gd-jpeg v1.0 (using IJG JPEG v90), default quality" to the file
		 *
		 * Note:
		 *  throws an sec_captcha_exception on error
		 *
		 * Usage:
			$captcha_image=captcha_get(new captcha_gd2(
				string_image_format // 'bmp' (PHP >= 7.2), 'gif', 'png' or 'jpeg', optional, default: 'jpeg'
			));
			$captcha_image=captcha_get_once(new captcha_gd2(
				string_image_format // 'bmp' (PHP >= 7.2), 'gif', 'png' or 'jpeg', optional, default: 'jpeg'
			));
		 *
		 * Source: https://stackoverflow.com/questions/5274563/php-imagecreate-error
		 */

		protected $encoding;

		public function __construct(string $encoding='jpeg')
		{
			if(!function_exists('imagecreate'))
				throw new sec_captcha_exception(
					'gd extension is not loaded'
				);

			if(
				($encoding === 'bmp') &&
				(!function_exists('imagebmp'))
			)
				throw new sec_captcha_exception(
					'gd imagebmp function is not available'
				);

			$this->encoding=$encoding;
		}

		public function generate_token(): string
		{
			return substr(
				md5(rand(0, 999)),
				15, 5
			);
		}
		public function generate_image(string $token): string
		{
			$image_object=imagecreate(100, 20);

			$color_grey=imagecolorallocate(
				$image_object,
				204, 204, 204
			);

			imagefill(
				$image_object,
				0, 0,
				imagecolorallocate(
					$image_object,
					0, 0, 0
				)
			);

			imagestring(
				$image_object,
				3,
				30, 3,
				$token,
				imagecolorallocate(
					$image_object,
					255, 255, 255
				)
			);

			imagerectangle(
				$image_object,
				0, 0,
				99, 19,
				$color_grey
			);

			imageline(
				$image_object,
				0, 10,
				100, 10,
				$color_grey
			);

			imageline(
				$image_object,
				50, 0,
				50, 20,
				$color_grey
			);

			ob_start();

			switch($this->encoding)
			{
				case 'bmp':
					imagebmp($image_object);
				break;
				case 'gif':
					imagegif($image_object);
				break;
				case 'png':
					imagepng($image_object);
				break;
				default:
					imagejpeg($image_object);
			}

			$image_blob=ob_get_clean();

			imagedestroy($image_object);

			return $image_blob;
		}
	}
	class captcha_imagick implements captcha_module
	{
		/*
		 * Generates a token and a 100x20px image
		 * Imagick version of captcha_gd2
		 *
		 * Warning:
		 *  imagick extension is required
		 *
		 * Note:
		 *  throws an sec_captcha_exception on error
		 *
		 * Usage:
			$captcha_image=captcha_get(new captcha_imagick(
				string_image_format, // 'bmp', 'gif', 'png' or 'jpeg', optional, default: 'jpeg'
				string_token_font_name, // optional
				int_token_font_size // optional
			));
			$captcha_image=captcha_get_once(new captcha_imagick(
				string_image_format, // 'bmp', 'gif', 'png' or 'jpeg', optional, default: 'jpeg'
				string_token_font_name, // optional
				int_token_font_size // optional
			));
		 */

		protected $encoding;
		protected $token_image_font;
		protected $token_image_font_size;

		public function __construct(
			string $encoding='jpeg',
			?string $token_image_font=null,
			?int $token_image_font_size=null
		){
			if(!class_exists('Imagick'))
				throw new sec_captcha_exception(
					'imagick extension is not loaded'
				);

			if(empty(Imagick::queryFonts()))
				throw new sec_captcha_exception(
					'Imagick::queryFonts - no fonts found'
				);

			$this->encoding=$encoding;
			$this->token_image_font=$token_image_font;
			$this->token_image_font_size=$token_image_font_size;
		}

		public function generate_token(): string
		{
			return substr(
				md5(rand(0, 999)),
				15, 5
			);
		}
		public function generate_image(string $token): string
		{
			if($this->token_image_font === null)
				$this->token_image_font=Imagick::queryFonts()[0];

			$token_image=new Imagick();
			$token_image_background=new ImagickPixel();
			$token_image_foreground=new ImagickDraw();

			$token_image_background->setColor('#000000');
			$token_image_foreground->setFont(
				$this->token_image_font
			);

			if($this->token_image_font_size !== null)
				$token_image_foreground->setFontSize(
					$this->token_image_font_size
				);

			$token_image_foreground->setFillColor('#ffffff');
			$token_image_foreground->setFontWeight('600');

			$token_image->newImage(
				98, 18,
				$token_image_background
			);
			$token_image->annotateImage(
				$token_image_foreground,
				29, 13,
				0,
				$token
			);

			$token_image_foreground->line(
				50, 0,
				50, 98
			);
			$token_image_foreground->line(
				0, 10,
				100, 10
			);

			$token_image->borderImage(
				'#ffffff',
				1, 1
			);
			$token_image->drawImage($token_image_foreground);

			switch($this->encoding)
			{
				case 'bmp':
				case 'gif':
				case 'png':
					$token_image->setImageFormat(
						$this->encoding
					);
				break;
				default:
					$token_image->setImageFormat('jpg');
			}

			return $token_image->getImageBlob();
		}
	}
	class captcha_imagick2 implements captcha_module
	{
		/*
		 * Generates a token and a 85x30px image
		 *
		 * Warning:
		 *  imagick extension is required
		 *
		 * Note:
		 *  throws an sec_captcha_exception on error
		 *
		 * Usage:
			$captcha_image=captcha_get(new captcha_imagick(
				string_image_format, // 'bmp', 'gif', 'png' or 'jpeg', optional, default: 'jpeg'
				string_token_font_name, // optional
				int_token_font_size // optional
			));
			$captcha_image=captcha_get_once(new captcha_imagick(
				string_image_format, // 'bmp', 'gif', 'png' or 'jpeg', optional, default: 'jpeg'
				string_token_font_name, // optional
				int_token_font_size // optional
			));
		 *
		 * Source: https://github.com/Imagick/imagick/blob/master/examples/captcha.php
		 * License: The PHP License, version 3.01 https://www.php.net/license/3_01.txt
		 */

		protected $encoding;
		protected $token_image_font;
		protected $token_image_font_size;

		public function __construct(
			string $encoding='jpeg',
			?string $token_image_font=null,
			int $token_image_font_size=20
		){
			if(!class_exists('Imagick'))
				throw new sec_captcha_exception(
					'imagick extension is not loaded'
				);

			if(empty(Imagick::queryFonts()))
				throw new sec_captcha_exception(
					'Imagick::queryFonts - no fonts found'
				);

			$this->encoding=$encoding;
			$this->token_image_font=$token_image_font;
			$this->token_image_font_size=$token_image_font_size;
		}

		public function generate_token(): string
		{
			return substr(
				md5(rand(0, 999)),
				15, 5
			);
		}
		public function generate_image(string $token): string
		{
			if($this->token_image_font === null)
				$this->token_image_font=Imagick::queryFonts()[0];

			$token_image=new Imagick();
			$token_image_background=new ImagickPixel();
			$token_image_foreground=new ImagickDraw();

			$token_image_background->setColor('#ffffff');
			$token_image_foreground->setFont(
				$this->token_image_font
			);
			$token_image_foreground->setFontSize(
				$this->token_image_font_size
			);

			$token_image->newImage(
				85, 30,
				$token_image_background
			);
			$token_image->annotateImage(
				$token_image_foreground,
				4, 20,
				0,
				$token
			);
			$token_image->swirlImage(20);

			for($i=0; $i<4; ++$i)
				$token_image_foreground->line(
					rand(0,70), rand(0,30),
					rand(0,70), rand(0,30)
				);

			$token_image->drawImage($token_image_foreground);

			switch($this->encoding)
			{
				case 'bmp':
				case 'gif':
				case 'png':
					$token_image->setImageFormat(
						$this->encoding
					);
				break;
				default:
					$token_image->setImageFormat('jpg');
			}

			return $token_image->getImageBlob();
		}
	}
?>