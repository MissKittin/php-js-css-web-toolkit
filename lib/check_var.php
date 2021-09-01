<?php
	/*
	 * Variable checking library
	 * Return variable value if exists, null if not
	 *  or return true if variable exists, false if not
	 *
	 * Functions:
	 *  check_argv('arg')
	 *   for single command line arguments
	 *  check_argv_param('arg')
	 *  check_argv_param('arg', ':')
	 *   for command line arguments with delimiter (default: = )
	 *  check_argv_next_param('arg')
	 *   for command line arguments with space as delimiter
	 *  get_argc()
	 *   return arguments number or false
	 *  check_cookie('cookie_name')
	 *   for cookie array
	 *  check_env('env_variable')
	 *   for environment variables array
	 *  check_files('file_name')
	 *  check_files('file_name', 'tmp_name')
	 *   for file upload array
	 *  check_get('get_param')
	 *   for GET
	 *  check_post('post_param')
	 *   for POST
	 *  check_request('request_param')
	 *   for http request aray
	 *  check_server('REQUEST_URI')
	 *  check_server('argv', 1)
	 *   for server array
	 *  check_session('session_variable')
	 *   for session array
	 */

	function check_argv($input_arg)
	{
		// Return true if argument exists, false if not

		foreach($_SERVER['argv'] as $arg)
			if($arg === $input_arg)
				return true;
		return false;
	}
	function check_argv_param($param_name, $delimiter='=')
	{
		// Return arg's value separated by delimiter if exists, null if not
		// eg: param=value or param:value or "param=long value"

		$argv=$_SERVER['argv'];
		array_shift($argv);
		foreach($argv as $arg)
		{
			$arg=explode($delimiter, $arg);
			if($arg[0] === $param_name)
				return $arg[1];
		}
		return null;
	}
	function check_argv_next_param($param_name)
	{
		// Return arg's value if exists, null if not
		// eg: param value or -param value or -param "long value"

		$argv=$_SERVER['argv'];
		array_shift($argv);

		$arg_found=false;
		foreach($argv as $arg)
		{
			if($arg_found)
				return $arg;
			if($arg === $param_name)
				$arg_found=true;
		}
		return null;
	}
	function get_argc()
	{
		// Return $_SERVER['argc'] if exists, null if not

		if(isset($_SERVER['argc']))
			return $_SERVER['argc'];
		return null;
	}
	function check_cookie($array_item)
	{
		// Return cookie value if exists, null if not

		if(isset($_COOKIE[$array_item]))
			return $_COOKIE[$array_item];
		return null;
	}
	function check_env($array_item)
	{
		// Return environment variable content if exists, null if not

		if(isset($_ENV[$array_item]))
			return $_ENV[$array_item];
		return null;
	}
	function check_files($array_item, $array_nested_item=false)
	{
		// Return uploaded files array value if exists, null if not
		// eg: check_files('filename') or check_files('filename', 'tmp_name')

		if($array_nested_item === false)
			if(isset($_FILES[$array_item]))
				return $_FILES[$array_item];
		else
			if(isset($_FILES[$array_item][$array_nested_item]))
				return $_FILES[$array_item][$array_nested_item];
		return null;
	}
	function check_get($array_item)
	{
		// Return get param's value if exists, null if not

		if(isset($_GET[$array_item]))
			return $_GET[$array_item];
		return null;
	}
	function check_post($array_item)
	{
		// Return post param's value if exists, null if not

		if(isset($_POST[$array_item]))
			return $_POST[$array_item];
		return null;
	}
	function check_request($array_item)
	{
		// Return http request param's value if exists, null if not

		if(isset($_REQUEST[$array_item]))
			return $_REQUEST[$array_item];
		return null;
	}
	function check_server($array_item, $array_nested_item=false)
	{
		// Return server array value if exists, null if not
		// eg: check_server('REQUEST_URI') or check_files('argv', 1)

		if($array_nested_item === false)
			if(isset($_SERVER[$array_item]))
				return $_SERVER[$array_item];
		else
			if(isset($_SERVER[$array_item][$array_nested_item]))
				return $_SERVER[$array_item][$array_nested_item];
		return null;
	}
	function check_session($array_item)
	{
		// Return session param's value if exists, null if not

		if(isset($_SESSION[$array_item]))
			return $_SESSION[$array_item];
		return null;
	}
?>