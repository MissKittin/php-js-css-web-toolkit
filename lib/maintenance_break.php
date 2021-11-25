<?php
	/*
	 * Maintenance break
	 * Check to send the maintenance break pattern
	 *
	 * Functions:
	 *  maintenance_break_get(string_cookie_name, string_get_key)
	 *  maintenance_break_path(string_cookie_name, string_uri_path)
	 *  maintenance_break_http(string_header_name, string_header_value)
	 *  maintenance_break_ip(string_ip_addr)
	 *   alternative: maintenance_break_ip([string_ip_addr, string_ip_addr])
	 *
	 * Example usage:
		if(!maintenance_break_get('mbtoken', 'secretpassw0rd'))
		{
			include './app/views/maintenance-break.html';
			exit();
		}
	 * Hint: you can add redirect header after above if():
		if($APP_ROUTER[1] === 'secretpassw0rd')
		{
			header('Location: /');
			exit();
		}
	 * where $APP_ROUTER[1] is for maintenance_break_path
	 */

	function maintenance_break_get($cookie_name, $get_name)
	{
		/*
		 * Maintenance break
		 * GET->cookie method
		 *
		 * Note: you only need to enter the password once
		 *
		 * Usage: maintenance_break_get(string_cookie_name, string_get_key)
		 *  type yourapp.addr?string_get_key in the address bar of your browser
		 */

		if(isset($_COOKIE[$cookie_name]))
			if($_COOKIE[$cookie_name] === md5($get_name))
				return true;

		if(isset($_GET[$get_name]))
		{
			setcookie($cookie_name, md5($get_name), time()+3600, '', '', false, true);
			return true;
		}

		return false;
	}
	function maintenance_break_path($cookie_name, $path)
	{
		/*
		 * Maintenance break
		 * URI->cookie method
		 *
		 * Warning: $_SERVER['REQUEST_URI'] is required
		 *
		 * Note: you only need to enter the password once
		 *
		 * Usage: maintenance_break_path(string_cookie_name, string_uri_path)
		 *  type yourapp.addr/string_uri_path in the address bar of your browser
		 */

		if(strtok($_SERVER['REQUEST_URI'], '?') === $path)
		{
			setcookie($cookie_name, md5($path), time()+3600, '', '', false, true);
			return true;
		}

		if(isset($_COOKIE[$cookie_name]))
			if($_COOKIE[$cookie_name] === md5($path))
				return true;

		return false;
	}
	function maintenance_break_http($header_name, $header_value)
	{
		/*
		 * Maintenance break
		 * HTTP header method
		 *
		 * Note: you have to pass the http header with each request
		 *
		 * Usage: maintenance_break_http(string_header_name, string_header_value)
		 *  in the client, add the http header: X-string_header_name: string_header_value
		 */

		$header_name=strtr(strtoupper($header_name), '-', '_');

		if(isset($_SERVER['HTTP_X_'.$header_name]))
			if($_SERVER['HTTP_X_'.$header_name] === $header_value)
				return true;
		return false;
	}
	function maintenance_break_ip($allowed_ip)
	{
		/*
		 * Maintenance break
		 * IP method
		 *
		 * Warning:
		 *  $_SERVER['REMOTE_ADDR'] is required
		 *
		 * Usage:
		 *  maintenance_break_ip(string_ip_addr)
		 *  maintenance_break_ip([string_ip_addr, string_ip_addr])
		 */

		if(is_array($allowed_ip))
		{
			foreach($allowed_ip as $ip)
				if($_SERVER['REMOTE_ADDR'] === $ip)
					return true;
		}
		else
			if($_SERVER['REMOTE_ADDR'] === $allowed_ip)
				return true;

		return false;
	}
?>