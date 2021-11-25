<?php
	/*
	 * Variable checking library
	 *
	 * Hint:
	 *  If you just want to print the value from the cookie, post or get,
	 *  use check_*_escaped(), but DO NOT use it if you want to process the value.
	 *
	 * Functions:
	 *  ARGV/ARGC:
	 *   check_argv('arg') [returns true if the argument exists, false if not]
	 *    for single command line arguments
	 *
	 *   check_argv_param('arg')
	 *   check_argv_param('arg', ':')
	 *    [returns the argument value with a delimiter if it exists, null if not]
	 *    for command line arguments with a delimiter (default: = )
	 *    eg:
	 *     param=value
	 *     param:value
	 *     "param=long value"
	 *     param="long value"
	 *
	 *   check_argv_param_many('arg')
	 *   check_argv_param_many('arg', ':')
	 *    [returns an array with the argument values with a delimiter if present, null if not]
	 *    for repeated command line arguments with a delimiter (default: = )
	 *    eg:
	 *     param=value1 param=value2
	 *     param:value1 param:value2
	 *     "param=long value 1" "param=long value 2"
	 *     param="long value 1" param="long value 2"
	 *
	 *   check_argv_next_param('arg') [returns the argument value if it exists, null if not]
	 *    for command line arguments with a space as the delimiter
	 *    eg:
	 *     param value
	 *     -param value
	 *     -param "long value"
	 *
	 *   check_argv_next_param_many('arg') [returns an array with the argument values if present, null if not]
	 *    for repeated command line arguments with a space as the delimiter
	 *    eg:
	 *     param value1 param value2
	 *     -param value1 -param value2
	 *     -param "long value 1" -param "long value 2"
	 *
	 *   check_argc() [returns $_SERVER['argc'] or null]
	 *
	 *  $_COOKIE:
	 *   check_cookie('cookie_name') [returns cookie if it exists, null if not]
	 *
	 *   check_cookie_escaped('cookie_name') [returns escaped cookie if it exists, null if not]
	 *
	 *  $_ENV
	 *   check_env('env_variable') [returns the value of an environment variable if it exists, null if not]
	 *
	 *  $_FILES
	 *   check_files('file_name')
	 *   check_files('file_name', 'tmp_name')
	 *    [returns the value of an array with transferred files if it exists, null if not]
	 *
	 *  $_GET
	 *   check_get('get_param') [returns get value if it exists, null if not]
	 *
	 *   check_get_escaped('get_param') [returns escaped get value if it exists, null if not]
	 *
	 *  $_POST
	 *   check_post('post_param') [returns post value if it exists, null if not]
	 *
	 *   check_post_escaped('post_param') [returns escaped post value if it exists, null if not]
	 *
	 *  $_REQUEST
	 *   check_request('request_param') [returns the http request parameter if it exists, null if not]
	 *
	 *  $_SERVER
	 *   check_server('REQUEST_URI')
	 *   check_server('nested_array', nested_array_id)
	 *    [returns $_SERVER value if exists, null if not]
	 *    eg:
	 *     check_server('REQUEST_URI')
	 *     check_files('argv', 1)
	 *
	 *  $_SESSION
	 *   check_session('session_variable') [returns $_SESSION value if exists, null if not]
	 */

	function check_argv($input_arg)
	{
		foreach($_SERVER['argv'] as $arg)
			if($arg === $input_arg)
				return true;
		return false;
	}
	function check_argv_param($param_name, $delimiter='=')
	{
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
	function check_argv_param_many($param_name, $delimiter='=')
	{
		$output_array=array();

		$argv=$_SERVER['argv'];
		array_shift($argv);
		foreach($argv as $arg)
		{
			$arg=explode($delimiter, $arg);
			if($arg[0] === $param_name)
				$output_array[]=$arg[1];
		}

		if(empty($output_array))
			return null;
		return $output_array;
	}
	function check_argv_next_param($param_name)
	{
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
	function check_argv_next_param_many($param_name)
	{
		$output_array=array();

		$argv=$_SERVER['argv'];
		array_shift($argv);

		$arg_found=false;
		foreach($argv as $arg)
		{
			if($arg_found)
			{
				$output_array[]=$arg;
				$arg_found=false;
			}
			if($arg === $param_name)
				$arg_found=true;
		}

		if(empty($output_array))
			return null;
		return $output_array;
	}
	function check_argc()
	{
		if(isset($_SERVER['argc']))
			return $_SERVER['argc'];
		return null;
	}

	function check_cookie($array_item)
	{
		if(isset($_COOKIE[$array_item]))
			return $_COOKIE[$array_item];
		return null;
	}
	function check_cookie_escaped($array_item)
	{
		if(isset($_COOKIE[$array_item]))
			return htmlspecialchars($_COOKIE[$array_item], ENT_QUOTES, 'UTF-8');
		return null;
	}

	function check_env($array_item)
	{
		if(isset($_ENV[$array_item]))
			return $_ENV[$array_item];
		return null;
	}

	function check_files($array_item, $array_nested_item=false)
	{
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
		if(isset($_GET[$array_item]))
			return $_GET[$array_item];
		return null;
	}
	function check_get_escaped($array_item)
	{
		if(isset($_GET[$array_item]))
			return htmlspecialchars($_GET[$array_item], ENT_QUOTES, 'UTF-8');
		return null;
	}

	function check_post($array_item)
	{
		if(isset($_POST[$array_item]))
			return $_POST[$array_item];
		return null;
	}
	function check_post_escaped($array_item)
	{
		if(isset($_POST[$array_item]))
			return htmlspecialchars($_POST[$array_item], ENT_QUOTES, 'UTF-8');
		return null;
	}

	function check_request($array_item)
	{
		if(isset($_REQUEST[$array_item]))
			return $_REQUEST[$array_item];
		return null;
	}

	function check_server($array_item, $array_nested_item=false)
	{
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
		if(isset($_SESSION[$array_item]))
			return $_SESSION[$array_item];
		return null;
	}
?>