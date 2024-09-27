<?php
	/*
	 * Login validation library
	 *
	 * Warning:
	 *  $_SESSION['_sec_login'] is reserved
	 *
	 * Note:
	 *  throws an login_exception on error
	 *
	 * Usage:
	 *  login_single('input_login', 'input_plain_password', 'valid_login', 'valid_bcrypted_password')
	 *   use this to authenticate one user
	 *   hint: forget about it and use the login_multi or login_callback
	 *
	    login_multi('input_login', 'input_plain_password', [
			'first-person'=>'first_person_bcrypt_passwd',
			'second-person'=>'second_person_bcrypt_passwd',
			'n-person'=>'n_person_bcrypt_passwd'
	    ])
	 *   use this to authenticate more users
	 *
	    login_callback('input_login', 'input_plain_password', function($input_login){
			if($password_hash=find_password($input_login))
				return $password_hash; // success

			return null; // fail
	    })
	 *   where find_password() is your defined function or something else
	 *    that returns bcryped password if success or false if failed
	 *   use this to get credentials eg from database
	 *
	 *  login_refresh('string', 'reload string')
	 *  login_refresh('callback', 'callback_function')
	 *  login_refresh('callback', 'callback_function', ['callback_arg_a', 'callback_arg_b'])
	 *  login_refresh('file', 'path/to/file') // uses include
	 *  login_refresh('require-file', 'path/to/file') // uses require
	 *   refresh page after successful login to remove credentials from browser's buffer
	 *
	 *  logout()
	 *
	 *  is_logged([bool_session_regenerate], [callable_on_check_fail])
	 *   where session_regenerate=false disables session id regeneration
	 *   and on_check_fail is used to log validation errors
	 *    eg function($message){ error_log('sec_login.php: '.$message); }
	 *   if(is_logged()) { do logged stuff } else { do not logged stuff }
	 *
	 *  string2bcrypt(string_password)
	 *   convert string to bcrypt hash
	 *
	 * Callbacks and validators:
	 *  the library allows you to define your own validators and callbacks
	 *  by default, it checks the browser's user agent and logs out if it does not match
	 *  also saves the username to $_SESSION['_sec_login']['user']
	 *  if you don't want default settings, add before the functions definition:
	 *   login_validator::flush_callbacks() // returns self (you can chain this method)
	 *  the following methods are used to define callbacks: on_login and on_logout
	 *  to define the validator, use the add method, eg:
	    login_validator::on_login(function($input_login){
			// save escaped user name
			$_SESSION['_sec_login']['user_escaped']=htmlspecialchars($input_login, ENT_QUOTES, 'UTF-8');
			return true;
		})::on_login(function(){
			// save IP address

			if(!isset($_SERVER['REMOTE_ADDR']))
				return false;

			$_SESSION['_sec_login']['ip']=$_SERVER['REMOTE_ADDR'];

			return true;
		})::on_logout(function(){
			my_logout_stuff($_SESSION['_sec_login']['user']);
		})::add(function($on_check_fail){
			// check IP address

			if(!isset($_SERVER['REMOTE_ADDR']))
			{
				$on_check_fail('_SERVER["REMOTE_ADDR"] does not exists');
				return false;
			}

			if(!isset($_SESSION['_sec_login']['ip']))
			{
				$on_check_fail('Client IP not set in _SESSION');
				return false;
			}

			return ($_SESSION['_sec_login']['ip'] === $_SERVER['REMOTE_ADDR']);
		});
	 */

	class login_exception extends Exception {}

	final class login_validator
	{
		private static $login_callbacks=[];
		private static $logout_callbacks=[];
		private static $validators=[];

		private static function run_login_callbacks($input_login)
		{
			foreach(self::$login_callbacks as $callback)
				if(!$callback($input_login))
					return false;

			return true;
		}
		private static function run_logout_callbacks()
		{
			foreach(self::$logout_callbacks as $callback)
				$callback();
		}
		private static function run_validators($on_check_fail)
		{
			foreach(self::$validators as $callback)
				if(!$callback($on_check_fail))
					return false;

			return true;
		}

		public static function on_login(callable $function)
		{
			self::$login_callbacks[]=$function;
			return self::class;
		}
		public static function on_logout(callable $function)
		{
			self::$logout_callbacks[]=$function;
			return self::class;
		}
		public static function add(callable $function)
		{
			self::$validators[]=$function;
			return self::class;
		}
		public static function flush_callbacks()
		{
			self::$login_callbacks=[];
			self::$logout_callbacks=[];
			self::$validators=[];

			return self::class;
		}

		public static function login_callback(
			?string $input_login=null,
			?string $input_password=null,
			callable $callback
		){
			if(session_status() !== PHP_SESSION_ACTIVE)
				throw new login_exception('Session not started');

			$password=$callback($input_login);

			if(
				($password !== null) &&
				(password_verify($input_password, $password))
			){
				$_SESSION['_sec_login']['state']=true;

				if(!self::run_login_callbacks($input_login))
				{
					self::logout();
					return false;
				}

				return true;
			}

			return false;
		}
		public static function login_single(
			?string $input_login=null,
			?string $input_password=null,
			string $login,
			string $password
		){
			return self::login_callback(
				$input_login,
				$input_password,
				function($input_login) use($login, $password)
				{
					if($input_login === $login)
						return $password;

					return null;
				}
			);
		}
		public static function login_multi(
			?string $input_login=null,
			?string $input_password=null,
			array $login_array
		){
			return self::login_callback(
				$input_login,
				$input_password,
				function($input_login) use($login_array)
				{
					if(isset($login_array[$input_login]))
						return $login_array[$input_login];

					return null;
				}
			);
		}
		public static function logout()
		{
			if(session_status() !== PHP_SESSION_ACTIVE)
				throw new login_exception('Session not started');

			$_SESSION['_sec_login']['state']=false;

			self::run_logout_callbacks();
			session_regenerate_id(true);
			session_destroy();
		}
		public static function is_logged(
			bool $session_regenerate=true,
			?callable $on_check_fail=null
		){
			if(session_status() !== PHP_SESSION_ACTIVE)
				throw new login_exception('Session not started');

			if(!isset($_SESSION['_sec_login']))
				return false;

			if($on_check_fail === null)
				$on_check_fail=function(){};

			if($_SESSION['_sec_login']['state'] === true)
			{
				if(!self::run_validators($on_check_fail))
				{
					self::logout();
					return false;
				}

				if($session_regenerate)
					session_regenerate_id(true);

				return true;
			}

			return false;
		}

		private function __construct() {}
	}

	function login_single(...$args)
	{
		return login_validator::login_single(...$args);
	}
	function login_multi(...$args)
	{
		return login_validator::login_multi(...$args);
	}
	function login_callback(...$args)
	{
		return login_validator::login_callback(...$args);
	}
	function login_refresh(
		string $input_type,
		string $input,
		array $callback_args=[]
	){
		switch($input_type)
		{
			case 'string':
				echo $input;
			break;
			case 'callback':
				call_user_func_array($input, $callback_args);
			break;
			case 'file':
				readfile($input);
			break;
			case 'require-file':
				require $input;
			break;
			default:
				throw new login_exception('input_type must be "string", "callback", "file" or "require-file"');
		}
	}
	function logout(...$args)
	{
		return login_validator::logout(...$args);
	}
	function is_logged(...$args)
	{
		return login_validator::is_logged(...$args);
	}
	function string2bcrypt(string $string)
	{
		return password_hash($string, PASSWORD_BCRYPT);
	}

	login_validator::on_login(function($input_login){
		$_SESSION['_sec_login']['user']=$input_login;
		return true;
	})::on_login(function(){
		if(!isset($_SERVER['HTTP_USER_AGENT']))
			return false;

		$_SESSION['_sec_login']['user_agent']=md5($_SERVER['HTTP_USER_AGENT']);

		return true;
	})::add(function($on_check_fail){
		if(!isset($_SERVER['HTTP_USER_AGENT']))
		{
			$on_check_fail('User agent not sent');
			return false;
		}

		if(!isset($_SESSION['_sec_login']['user_agent']))
		{
			$on_check_fail('User agent not set in _SESSION');
			return false;
		}

		return ($_SESSION['_sec_login']['user_agent'] === md5($_SERVER['HTTP_USER_AGENT']));
	});
?>