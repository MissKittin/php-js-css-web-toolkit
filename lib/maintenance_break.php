<?php
	/*
	 * Maintenance break
	 * Check to send the maintenance break pattern
	 *
	 * Functions:
	 *  maintenance_break_get(string_cookie_name, string_get_key)
	 *  maintenance_break_path(string_cookie_name, string_uri_path, $_SERVER['REQUEST_URI'])
	 *   note: $_SERVER['REQUEST_URI'] is optional
	 *  maintenance_break_http(string_header_name, string_header_value)
	 *  maintenance_break_ip(string_ip_addr, $_SERVER['REMOTE_ADDR'])
	 *   alternative: maintenance_break_ip([string_ip_addr, string_ip_addr], $_SERVER['REMOTE_ADDR'])
	 *   note: $_SERVER['REMOTE_ADDR'] is optional
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

	function maintenance_break_get(string $cookie_name, string $get_name)
	{
		/*
		 * Maintenance break
		 * GET->cookie method
		 *
		 * Note:
		 *  you only need to enter the password once
		 *
		 * Usage:
		 *  maintenance_break_get(string_cookie_name, string_get_key)
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
	function maintenance_break_path(
		string $cookie_name,
		string $path,
		string $request_uri=null
	){
		/*
		 * Maintenance break
		 * URI->cookie method
		 *
		 * Note:
		 *  you only need to enter the password once
		 *  if you do not supply $request_uri, $_SERVER['REQUEST_URI'] will be used
		 *
		 * Usage:
		 *  maintenance_break_path(string_cookie_name, string_uri_path)
		 *  maintenance_break_path(string_cookie_name, string_uri_path, string_request_uri)
		 *  type yourapp.addr/string_uri_path in the address bar of your browser
		 */

		if($request_uri === null)
		{
			if(!isset($_SERVER['REQUEST_URI']))
				throw new Exception('$_SERVER["REQUEST_URI"] is not set');

			$request_uri=strtok($_SERVER['REQUEST_URI'], '?');
		}

		if($request_uri === $path)
		{
			setcookie($cookie_name, md5($path), time()+3600, '', '', false, true);
			return true;
		}

		if(isset($_COOKIE[$cookie_name]))
			if($_COOKIE[$cookie_name] === md5($path))
				return true;

		return false;
	}
	function maintenance_break_http(string $header_name, string $header_value)
	{
		/*
		 * Maintenance break
		 * HTTP header method
		 *
		 * Note:
		 *  you have to pass the http header with each request
		 *
		 * Usage:
		 *  maintenance_break_http(string_header_name, string_header_value)
		 *  in the client, add the http header: X-string_header_name: string_header_value
		 */

		$header_name=strtr(strtoupper($header_name), '-', '_');

		if(isset($_SERVER['HTTP_X_'.$header_name]))
			if($_SERVER['HTTP_X_'.$header_name] === $header_value)
				return true;
		return false;
	}
	function maintenance_break_ip($allowed_ip, string $remote_ip=null)
	{
		/*
		 * Maintenance break
		 * IP method
		 *
		 * Note:
		 *  if you do not supply $remote_ip, $_SERVER['REMOTE_ADDR'] will be used
		 *
		 * Usage:
		 *  maintenance_break_ip(string_allowed_ip_addr)
		 *  maintenance_break_ip(string_allowed_ip_addr, string_client_ip)
		 *  maintenance_break_ip([string_allowed_ip_addr, string_allowed_ip_addr])
		 *  maintenance_break_ip([string_allowed_ip_addr, string_allowed_ip_addr], string_client_ip)
		 */

		if($remote_ip === null)
		{
			if(!isset($_SERVER['REMOTE_ADDR']))
				throw new Exception('$_SERVER["REMOTE_ADDR"] is not set');

			$remote_ip=$_SERVER['REMOTE_ADDR'];
		}

		if(is_array($allowed_ip))
		{
			foreach($allowed_ip as $ip)
				if($remote_ip === $ip)
					return true;
		}
		else if(is_string($allowed_ip))
		{
			if($remote_ip === $allowed_ip)
				return true;
		}
		else
			throw new InvalidArgumentException('String or array expected');

		return false;
	}
?>