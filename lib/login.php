<?php
	/*
	 * Login validation library
	 *
	 * Usage:
	 * login_single('input-login', 'input-plain-password', 'valid-login', 'valid-bcrypted-password')
	 *  use this to authenticate one user
	   login_multi('input-login', 'input-plain-password', array(
	    'first-person' => 'first-person-bcrypt-passwd',
	    'second-person' => 'second-person-bcrypt-passwd',
	    'n-person' => 'n-person-bcrypt-passwd'
	   ))
	 *  use this to authenticate more users
	   login_callback('input-login', 'input-plain-password', function($input_login){
	    if($password=find_password($input_login)) return $password;
	    return null;
	   })
	 *  where find_password() is your defined function or something else
	 *  that returns bcryped password if success or false if failed
	 *  use this to get credentials from eg database
	 * login_refresh('string|callback|file', 'reloadString|callback-function-name-or-function(){}|path-to-file')
	 *  refresh page after successful login to remove credentials from browser's buffer
	 * logout($logout-button-post-or-get-variable)
	 *  if $logout-button-post-or-get-variable is not null, do logout
	 * is_logged()
	 *  if(is_logged()) { do logged stuff } else { do not logged stuff }
	 */

	function login_single($input_login, $input_password, $login, $password)
	{
		if(($input_login === $login) && (password_verify($input_password, $password)))
		{
			$_SESSION['logged']=true;
			return true;
		}
		return false;
	}
	function login_multi($input_login, $input_password, $login_array)
	{
		if(isset($login_array[$input_login]))
			if(password_verify($input_password, $login_array[$input_login]))
			{
				$_SESSION['logged']=true;
				return true;
			}
		return false;
	}
	function login_callback($input_login, $input_password, $callback)
	{
		$password=$callback($input_login);
		if($password !== null)
			if(password_verify($input_password, $password))
			{
				$_SESSION['logged']=true;
				return true;
			}
		return false;
	}

	function login_refresh($input_type, $input)
	{
		switch($input_type)
		{
			case 'string':
				echo $input;
			break;
			case 'callback':
				$input();
			break;
			case 'file':
				include $input;
			break;
		}
	}

	function logout($null)
	{
		if($null !== null)
		{
			$_SESSION['logged']=false;
			session_regenerate_id(false);
			session_destroy();
			return true;
		}
		return false;
	}

	function is_logged()
	{
		if(isset($_SESSION['logged']))
			if($_SESSION['logged'])
				return true;
		return false;
	}

	if(session_status() === PHP_SESSION_NONE)
	{
		session_name('id');
		session_start();
		if(is_logged()) session_regenerate_id(true);
	}
?>