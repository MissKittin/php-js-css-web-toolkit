<?php
	/*
	 * Random string generation
	 * Usage:
	 *  rand_str(40)
	 *   Warning: do not use it on security sensitive operations
	 *  rand_str_secure(40)
	 *   less customizable than rand_str()
	 */

	function rand_str($input_length, $lowercase=true, $uppercase=true, $numbers=true, $specialchars=true)
	{
		/*
		 * Random string generator
		 * Returns mixed lower and uppercase letters, numbers and special characters
		 *
		 * Usage:
		 *  rand_str(40)
		 */

		$const='';
		if($lowercase) $const.='abcdefghijklmnopqrstuvwxyz';
		if($uppercase) $const.='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($numbers) $const.='0123456789';
		if($specialchars) $const.='!@#$%^&*()-_[]{}?~:<>|';
		$const_length=strlen($const)-1;

		$output='';
		for($i=0; $i<$input_length; ++$i)
			$output.=substr($const, rand(0, $const_length), 1);

		return $output;
	}
	function rand_str_secure($chars, $force_openssl=false)
	{
		/*
		 * Random string generator - cryptographically secure method
		 * Returns alphanumeric string
		 *
		 * Usage:
		 *  rand_str_secure(40)
		 */

		if((PHP_MAJOR_VERSION >= 7) && (!$force_openssl))
			return substr(bin2hex(random_bytes($chars)), 0, $chars);

		return substr(bin2hex(openssl_random_pseudo_bytes($chars)), 0, $chars);
	}
?>