<?php
	/*
	 * file_http_request.php library test
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

		echo ' -> Mocking functions and classes';
			$GLOBALS['stream_context_options']=[];
			function stream_context_create($options)
			{
				$GLOBALS['stream_context_options']=$options;
				return $options;
			}
			function file_get_contents($filename, $flags='no-mock', $context=null)
			{
				if($flags === 'no-mock')
					return \file_get_contents($filename);

				return false;
			}
			class Exception extends \Exception {}
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

		$errors=[];

		echo ' -> Mocking file_http_request';
			$GLOBALS['allow_reset_fields']=true;
			class file_http_request_test extends file_http_request
			{
				protected function reset_fields($reset_context=true)
				{
					if($GLOBALS['allow_reset_fields'])
						parent::{__FUNCTION__}($reset_context);
				}

				public function _inject_response_headers($headers)
				{
					$this->response_headers=$headers;
				}
				public function _inject_response_content($response)
				{
					$this->response_content=$response;
				}
				public function _get_field($field)
				{
					return $this->$field;
				}
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Initializing file_http_request';
			$request_response=new file_http_request_test();
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing response methods (throws an Exception)'.PHP_EOL;
			foreach([
				'get_response_headers',
				'get_response_protocol',
				'get_response_status',
				'get_response_cookie',
				'get_response_content'
			] as $method){
				echo '  -> '.$method;

				$caught=false;
				try{
					if($method === 'get_response_cookie')
						$request_response->$method('mycookie');
					else
						$request_response->$method();
				} catch(Exception $error) {
					$caught=true;
				}
				if($caught)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$method.' throws an Exception';
				}
			}

		echo ' -> Testing request methods'.PHP_EOL;
		echo '  -> send throws an Exception';
			$caught=false;
			try {
				$request_response->send();
			} catch(Exception $error) {
				$caught=true;
			}
			if($caught)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='send throws an Exception';
			}
		echo '  -> set_url';
			$request_response->set_url('myurl');
			if($request_response->_get_field('url') === 'myurl')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='set_url';
			}
		echo '  -> set_method';
			$request_response->set_method('mymethod');
			if($request_response->_get_field('request_method') === 'mymethod')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='set_method';
			}
		echo '  -> set_header';
			$request_response->set_header('myheader', 'myheadervalue');
			if($request_response->_get_field('request_headers')['myheader'] === 'myheadervalue')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='set_header';
			}
		echo '  -> set_content'.PHP_EOL;
		echo '   -> set';
			$request_response->set_content('my content', false);
			if($request_response->_get_field('request_content') === 'my content')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='set_content set';
			}
		echo '   -> append';
			$request_response->set_content('appended content', true);
			if($request_response->_get_field('request_content') === 'my contentappended content')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='set_content append';
			}
		echo '  -> send returns false';
			$GLOBALS['allow_reset_fields']=false;
			if($request_response->send() === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='send returns false';
			}
			$GLOBALS['allow_reset_fields']=true;

		echo ' -> Injecting response headers and content';
			$request_response->_inject_response_headers([
				'HTTP/1.1 301 Moved Permanently',
				'Location: newlocation',
				'HTTP/1.1 200 OK',
				'Date: Sat, 12 Apr 2008 17:30:38 GMT',
				'Server: Apache/2.2.3 (CentOS)',
				'Last-Modified: Tue, 15 Nov 2005 13:24:10 GMT',
				'ETag: "280100-1b6-80bfd280"',
				'Accept-Ranges: bytes',
				'Content-Length: 438',
				'Connection: close',
				'Content-Type: text/html; charset=UTF-8',
				'Set-Cookie: mycookie=goodvalue; Domain=somecompany.co.uk; Path=/; Expires=Wed, 21 Oct 2015 07:28:00 GMT',
				'Set-Cookie: mysecondcookie=goodvaluee; Domain=somecompanyy.co.uk; Path=/; Expires=Weed, 21 Oct 2015 07:28:00 GMT'
			]);
			$request_response->_inject_response_content('good content');
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing response methods'.PHP_EOL;
		echo '  -> get_response_headers';
			if(str_replace(["\n", ' '], '', var_export($request_response->get_response_headers(), true)) === "array('Date'=>array(0=>'Sat,12Apr200817:30:38GMT',),'Server'=>array(0=>'Apache/2.2.3(CentOS)',),'Last-Modified'=>array(0=>'Tue,15Nov200513:24:10GMT',),'ETag'=>array(0=>'\"280100-1b6-80bfd280\"',),'Accept-Ranges'=>array(0=>'bytes',),'Content-Length'=>array(0=>'438',),'Connection'=>array(0=>'close',),'Content-Type'=>array(0=>'text/html;charset=UTF-8',),'Set-Cookie'=>array(0=>'mycookie=goodvalue;Domain=somecompany.co.uk;Path=/;Expires=Wed,21Oct201507:28:00GMT',1=>'mysecondcookie=goodvaluee;Domain=somecompanyy.co.uk;Path=/;Expires=Weed,21Oct201507:28:00GMT',),)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='get_response_headers';
			}
		echo '  -> get_response_protocol';
			if($request_response->get_response_protocol() === 'HTTP/1.1')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='get_response_protocol';
			}
		echo '  -> get_response_status';
			if($request_response->get_response_status() === '200')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='get_response_status';
			}
		echo '  -> get_response_cookie';
			if(str_replace(["\n", ' '], '', var_export($request_response->get_response_cookie('mycookie'), true)) === "array('value'=>'goodvalue','Domain'=>'somecompany.co.uk','Path'=>'/','Expires'=>'Wed,21Oct201507:28:00GMT',)")
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='get_response_cookie phase 1';
			}
			if(str_replace(["\n", ' '], '', var_export($request_response->get_response_cookie('mysecondcookie'), true)) === "array('value'=>'goodvaluee','Domain'=>'somecompanyy.co.uk','Path'=>'/','Expires'=>'Weed,21Oct201507:28:00GMT',)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='get_response_cookie phase 2';
			}
		echo '  -> get_response_content';
			if($request_response->get_response_content() === 'good content')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='get_response_content';
			}

		if(!empty($errors))
		{
			echo PHP_EOL;

			foreach($errors as $error)
				echo $error.' failed'.PHP_EOL;

			exit(1);
		}
	}
?>