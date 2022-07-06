<?php
	/*
	 * Maintenance break
	 * Check to send the maintenance break pattern
	 *
	 * Functions:
		maintenance_break_get([
			'cookie_name'=>string_cookie_name,
			'get_name'=>string_get_key,
			'set_cookie'=>function($a, $b, $c, $d, $e, $f, $g){ // optional
				my_setcookie_function($a, $b, $c, $d, $e, $f, $g);
			}
		])
		maintenance_break_path([
			'cookie_name'=>string_cookie_name,
			'path'=>string_uri_path,
			'request_uri'=>$_SERVER['REQUEST_URI'], // optional
			'set_cookie'=>function($a, $b, $c, $d, $e, $f, $g){ // optional
				my_setcookie_function($a, $b, $c, $d, $e, $f, $g);
			}
		])
		maintenance_break_http([
			'header_name'=>string_header_name,
			'header_value'=>string_header_value
		])
		maintenance_break_ip([
			'allowed_ip'=>string_ip_addr,
			'remote_ip'=>$_SERVER['REMOTE_ADDR'] // optional
		])
		maintenance_break_ip([ // alternative
			'allowed_ip'=>[string_ip_addr, string_ip_addr],
			'remote_ip'=>$_SERVER['REMOTE_ADDR'] // optional
		])
	 *
	 * Example usage:
		if(!maintenance_break_get([
			'cookie_name'=>'mbtoken',
			'get_name'=>'secretpassw0rd'
		])){
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

	function maintenance_break_get(array $params)
	{
		/*
		 * Maintenance break
		 * GET->cookie method
		 *
		 * Note:
		 *  you only need to enter the password once
		 *
		 * Usage:
		 *  type yourapp.addr?string_get_key in the address bar of your browser
			maintenance_break_get([
				'cookie_name'=>string_cookie_name,
				'get_name'=>string_get_key,
				'set_cookie'=>function($a, $b, $c, $d, $e, $f, $g){ // optional
					my_setcookie_function($a, $b, $c, $d, $e, $f, $g);
				}
			])
		 */

		foreach(['cookie_name', 'get_name'] as $param)
		{
			if(!isset($params[$param]))
				throw new InvalidArgumentException($param.' parameter is not defined');

			if(!is_string($params[$param]))
				throw new InvalidArgumentException($param.' is not a string');
		}

		$cookie_name=$params['cookie_name'];
		$get_name=$params['get_name'];
		$set_cookie=function($a, $b, $c, $d, $e, $f, $g){
			setcookie($a, $b, $c, $d, $e, $f, $g);
		};

		if(isset($params['set_cookie']))
		{
			if(!$params['set_cookie'] instanceOf Closure)
				throw new InvalidArgumentException('set_cookie: closure expected');

			$set_cookie=$params['set_cookie'];
		}

		if(isset($_COOKIE[$cookie_name]))
			if($_COOKIE[$cookie_name] === md5($get_name))
				return true;

		if(isset($_GET[$get_name]))
		{
			$set_cookie($cookie_name, md5($get_name), time()+3600, '', '', false, true);
			return true;
		}

		return false;
	}
	function maintenance_break_path(array $params)
	{
		/*
		 * Maintenance break
		 * URI->cookie method
		 *
		 * Note:
		 *  you only need to enter the password once
		 *  if you do not supply $request_uri, $_SERVER['REQUEST_URI'] will be used
		 *
		 * Usage:
		 *  type yourapp.addr/string_uri_path in the address bar of your browser
			maintenance_break_path([
				'cookie_name'=>string_cookie_name,
				'uri_path'=>string_uri_path,
				'request_uri'=>string_request_uri, // optional
				'set_cookie'=>function($a, $b, $c, $d, $e, $f, $g){ // optional
					my_setcookie_function($a, $b, $c, $d, $e, $f, $g);
				}
			])
		 */

		foreach(['cookie_name', 'path'] as $param)
		{
			if(!isset($params[$param]))
				throw new InvalidArgumentException($param.' parameter is not defined');

			if(!is_string($params[$param]))
				throw new InvalidArgumentException($param.' is not a string');
		}

		$cookie_name=$params['cookie_name'];
		$path=$params['path'];
		$request_uri=null;
		$set_cookie=function($a, $b, $c, $d, $e, $f, $g){
			setcookie($a, $b, $c, $d, $e, $f, $g);
		};

		if(isset($params['request_uri']))
		{
			if(!is_string($params['request_uri']))
				throw new InvalidArgumentException('request_uri is not a string');

			$request_uri=$params['request_uri'];
		}

		if(isset($params['set_cookie']))
		{
			if(!$params['set_cookie'] instanceOf Closure)
				throw new InvalidArgumentException('set_cookie: closure expected');

			$set_cookie=$params['set_cookie'];
		}

		if($request_uri === null)
		{
			if(!isset($_SERVER['REQUEST_URI']))
				throw new Exception('$_SERVER["REQUEST_URI"] is not set');

			$request_uri=strtok($_SERVER['REQUEST_URI'], '?');
		}

		if($request_uri === $path)
		{
			$set_cookie($cookie_name, md5($path), time()+3600, '', '', false, true);
			return true;
		}

		if(isset($_COOKIE[$cookie_name]))
			if($_COOKIE[$cookie_name] === md5($path))
				return true;

		return false;
	}
	function maintenance_break_http(array $params)
	{
		/*
		 * Maintenance break
		 * HTTP header method
		 *
		 * Note:
		 *  you have to pass the http header with each request
		 *
		 * Usage:
		 *  in the client, add the http header: X-string_header_name: string_header_value
			maintenance_break_http([
				'header_name'=>string_header_name,
				'header_value'=>string_header_value
			])
		 */

		foreach(['header_name', 'header_value'] as $param)
		{
			if(!isset($params[$param]))
				throw new InvalidArgumentException($param.' parameter is not defined');

			if(!is_string($params[$param]))
				throw new InvalidArgumentException($param.' is not a string');
		}

		$header_name=strtr(strtoupper($params['header_name']), '-', '_');
		$header_value=$params['header_value'];

		if(
			isset($_SERVER['HTTP_X_'.$header_name]) &&
			($_SERVER['HTTP_X_'.$header_name] === $header_value)
		)
			return true;

		return false;
	}
	function maintenance_break_ip(array $params)
	{
		/*
		 * Maintenance break
		 * IP method
		 *
		 * Note:
		 *  if you do not supply $remote_ip, $_SERVER['REMOTE_ADDR'] will be used
		 *
		 * Usage:
			maintenance_break_ip([
				'allowed_ip'=>string_allowed_ip_addr,
				'remote_ip'=>string_client_ip // optional
			])
			maintenance_break_ip([
				'allowed_ip'=>[string_allowed_ip_addr, string_allowed_ip_addr],
				'remote_ip'=>string_client_ip // optional
			])
		 */

		if(!isset($params['allowed_ip']))
			throw new InvalidArgumentException('allowed_ip parameter is not defined');
		$allowed_ip=$params['allowed_ip'];

		$remote_ip=null;
		if(isset($params['remote_ip']))
		{
			if(!is_string($params['remote_ip']))
				throw new InvalidArgumentException('remote_ip is not a string');

			$remote_ip=$params['remote_ip'];
		}

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
			throw new InvalidArgumentException('allowed_ip: string or array expected');

		return false;
	}
?>