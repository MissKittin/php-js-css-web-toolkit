<?php
	/*
	 * Login validation library
	 *
	 * Warning:
	 *  $_SESSION['login'] is reserved
	 *  $_SERVER['HTTP_USER_AGENT'] is required
	 *
	 * Usage:
	 *  login_single('input_login', 'input_plain_password', 'valid_login', 'valid_bcrypted_password')
	 *   use this to authenticate one user
	 *   hint: forget about it and use the login_multi
	 *
	    login_multi('input_login', 'input_plain_password', [
			'first-person'=>'first_person_bcrypt_passwd',
			'second-person'=>'second_person_bcrypt_passwd',
			'n-person'=>'n_person_bcrypt_passwd'
	    ])
	 *   use this to authenticate more users
	 *
	    login_callback('input_login', 'input_plain_password', function($input_login){
			if($password=find_password($input_login))
				return $password;
			return null;
	    })
	 *   where find_password() is your defined function or something else
	 *    that returns bcryped password if success or false if failed
	 *   use this to get credentials eg from database
	 *
	 *  login_refresh('string|callback|file', 'reload string|callback_function|path_to_file')
	 *   refresh page after successful login to remove credentials from browser's buffer
	 *
	 *  logout(['logout_button_post_or_get_variable'])
	 *   if $logout_button_post_or_get_variable is not null, do logout
	 *    this is prepared for check_var.php: if(logout(check_post('logout')))
	 *    and it's optional
	 *
	 *  is_logged([bool_session_regenerate], [callback_on_check_fail])
	 *   where session_regenerate=false disables session id regeneration
	 *   and on_check_fail is used to log validation errors
	 *    eg function($message){ error_log('sec_login.php: '.$message); }
	 *   if(is_logged()) { do logged stuff } else { do not logged stuff }
	 */

	function login_single(
		string $input_login,
		string $input_password,
		string $login,
		string $password
	){
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		if(!isset($_SERVER['HTTP_USER_AGENT']))
			return false;

		if(($input_login === $login) && (password_verify($input_password, $password)))
		{
			$_SESSION['login']['state']=true;
			$_SESSION['login']['user']=$input_login;
			$_SESSION['login']['user_agent']=md5($_SERVER['HTTP_USER_AGENT']);

			return true;
		}

		return false;
	}
	function login_multi(string $input_login, string $input_password, array $login_array)
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		if(!isset($_SERVER['HTTP_USER_AGENT']))
			return false;

		if(isset($login_array[$input_login]))
			if(password_verify($input_password, $login_array[$input_login]))
			{
				$_SESSION['login']['state']=true;
				$_SESSION['login']['user']=$input_login;
				$_SESSION['login']['user_agent']=md5($_SERVER['HTTP_USER_AGENT']);

				return true;
			}

		return false;
	}
	function login_callback(string $input_login, string $input_password, callable $callback)
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		if(!isset($_SERVER['HTTP_USER_AGENT']))
			return false;

		$password=$callback($input_login);

		if($password !== null)
			if(password_verify($input_password, $password))
			{
				$_SESSION['login']['state']=true;
				$_SESSION['login']['user']=$input_login;
				$_SESSION['login']['user_agent']=md5($_SERVER['HTTP_USER_AGENT']);

				return true;
			}

		return false;
	}

	function login_refresh(string $input_type, string $input)
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
		}
	}

	function logout($null=false)
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		if($null !== null)
		{
			$_SESSION['login']['state']=false;
			session_regenerate_id(false);
			session_destroy();
			return true;
		}

		return false;
	}

	function is_logged(bool $session_regenerate=true, callable $on_check_fail=function(){})
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
			throw new Exception('Session not started');

		if(!isset($_SERVER['HTTP_USER_AGENT']))
		{
			$on_check_fail('User agent not sent');
			return false;
		}

		if(!isset($_SESSION['login']))
			return false

		if($_SESSION['login']['state'])
		{
			if($_SESSION['login']['user_agent'] !== md5($_SERVER['HTTP_USER_AGENT']))
			{
				$on_check_fail($_SESSION['login']['user'].' user agent is invalid');
				logout();
				return false;
			}

			if($session_regenerate)
				session_regenerate_id(true);

			return true;
		}

		return false;
	}
?>