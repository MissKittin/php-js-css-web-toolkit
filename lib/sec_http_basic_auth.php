<?php
	function http_basic_auth(string $username, string $password, string $realm, callable $error_callback=null)
	{
		/*
		 * Request and validate basic HTTP authentication
		 *
		 * Usage:
			if(!http_basic_auth('username', 'password', 'realm0', function($cancel_button_pressed){
				if($cancel_button_pressed)
					echo 'Cancel button pressed';
				else
					echo 'Wrong username and/or password';
			})) exit();
		 */

		if(isset($_SERVER['PHP_AUTH_USER']))
		{
			if(
				($_SERVER['PHP_AUTH_USER'] === $username) &&
				($_SERVER['PHP_AUTH_PW'] === $password)
			)
				return true;
			else
				if($error_callback !== null)
				{
					header('HTTP/1.0 401 Unauthorized');
					$error_callback(false);
				}
		}
		else
		{
			header('WWW-Authenticate: Basic realm="'.$realm.'"');
			header('HTTP/1.0 401 Unauthorized');

			if($error_callback !== null)
				$error_callback(true);
		}

		return false;
	}
?>