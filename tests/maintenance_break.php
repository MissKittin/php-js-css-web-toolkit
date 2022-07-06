<?php
	/*
	 * maintenance_break.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		echo ' -> Mocking functions';
			function setcookie() {}
			class Exception extends \Exception {}
			class InvalidArgumentException extends \InvalidArgumentException {}
		echo ' [ OK ]'.PHP_EOL;

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(_include_tested_library(
				__NAMESPACE__,
				__DIR__.'/../lib/'.basename(__FILE__)
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

		$failed=false;

		echo ' -> Testing maintenance_break_get'.PHP_EOL;
		echo '  -> returns false';
			if(maintenance_break_get([
				'cookie_name'=>'cookiename',
				'get_name'=>'getname'
			])){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> setcookie/returns true';
			$_GET['getname']='';
			if(maintenance_break_get([
				'cookie_name'=>'cookiename',
				'get_name'=>'getname'
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_GET['getname']);
		echo '  -> returns true';
			$_COOKIE['cookiename']='d94796106dd616ad49c896bf72bb8878';
			if(maintenance_break_get([
				'cookie_name'=>'cookiename',
				'get_name'=>'getname'
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_COOKIE['cookiename']);

		echo ' -> Testing maintenance_break_path'.PHP_EOL;
		echo '  -> throws an Exception';
			$caught=false;
			try {
				maintenance_break_path([
					'cookie_name'=>'cookiename',
					'path'=>'/uripath'
				]);
			} catch(Exception $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> returns false';
			$_SERVER['REQUEST_URI']='/badpath?trash';
			if(maintenance_break_path([
				'cookie_name'=>'cookiename',
				'path'=>'/uripath'
			])){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
			unset($_SERVER['REQUEST_URI']);
		echo '  -> setcookie/returns true';
			$_SERVER['REQUEST_URI']='/uripath?trash';
			if(maintenance_break_path([
				'cookie_name'=>'cookiename',
				'path'=>'/uripath'
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_SERVER['REQUEST_URI']);
		echo '  -> returns true';
			$_SERVER['REQUEST_URI']='';
			$_COOKIE['cookiename']='deb79fad44597d5f18af757f806b8541';
			if(maintenance_break_path([
				'cookie_name'=>'cookiename',
				'path'=>'/uripath'
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_COOKIE['cookiename']);
			unset($_SERVER['REQUEST_URI']);

		echo ' -> Testing maintenance_break_http'.PHP_EOL;
		echo '  -> returns false';
			if(maintenance_break_http([
				'header_name'=>'Secret-Header',
				'header_value'=>'secretvalue'
			])){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> returns true';
			$_SERVER['HTTP_X_SECRET_HEADER']='secretvalue';
			if(maintenance_break_http([
				'header_name'=>'Secret-Header',
				'header_value'=>'secretvalue'
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_SERVER['HTTP_X_SECRET_HEADER']);

		echo ' -> Testing maintenance_break_ip'.PHP_EOL;
		echo '  -> throws an Exception';
			$caught=false;
			try {
				maintenance_break_ip([
					'allowed_ip'=>'127.0.0.1'
				]);
			} catch(Exception $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> throws InvalidArgumentException';
			$_SERVER['REMOTE_ADDR']='';
			$caught=false;
			try {
				maintenance_break_ip([
					'allowed_ip'=>700
				]);
			} catch(InvalidArgumentException $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_SERVER['REMOTE_ADDR']);
		echo '  -> input string'.PHP_EOL;
		echo '   -> returns false';
			$_SERVER['REMOTE_ADDR']='10.0.0.1';
			if(maintenance_break_ip([
				'allowed_ip'=>'127.0.0.1'
			])){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
			unset($_SERVER['REMOTE_ADDR']);
		echo '   -> returns true';
			$_SERVER['REMOTE_ADDR']='127.0.0.1';
			if(maintenance_break_ip([
				'allowed_ip'=>'127.0.0.1'
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_SERVER['REMOTE_ADDR']);
		echo '  -> input array'.PHP_EOL;
		echo '   -> returns false';
			$_SERVER['REMOTE_ADDR']='10.0.0.1';
			if(maintenance_break_ip([
				'allowed_ip'=>['127.0.0.1', '127.0.0.2']
			])){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
			unset($_SERVER['REMOTE_ADDR']);
		echo '   -> returns true for first address';
			$_SERVER['REMOTE_ADDR']='127.0.0.1';
			if(maintenance_break_ip([
				'allowed_ip'=>['127.0.0.1', '127.0.0.2']
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_SERVER['REMOTE_ADDR']);
		echo '   -> returns true for second address';
			$_SERVER['REMOTE_ADDR']='127.0.0.2';
			if(maintenance_break_ip([
				'allowed_ip'=>['127.0.0.1', '127.0.0.2']
			]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			unset($_SERVER['REMOTE_ADDR']);

		if($failed)
			exit(1);
	}
?>