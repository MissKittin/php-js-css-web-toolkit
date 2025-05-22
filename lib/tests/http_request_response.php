<?php
	/*
	 * http_request_response.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  var_export_contains.php library is required
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
			class Exception extends \Exception {}
			function filter_input($method, $key, $filter)
			{
				switch($method)
				{
					case INPUT_GET:
						return filter_var($_GET[$key], $filter);
					break;
					case INPUT_POST:
						return filter_var($_POST[$key], $filter);
				}
			}
			function session_status()
			{
				return PHP_SESSION_ACTIVE;
			}
			function headers_sent()
			{
				return false;
			}
			function http_response_code($code)
			{
				$GLOBALS['http_response_code']=$code;
			}
			$GLOBALS['http_response_headers']=[];
			function header($header)
			{
				$strpos=strpos($header, ': ');
				$GLOBALS['http_response_headers'][substr($header, 0, $strpos)]=substr($header, $strpos+2);
			}
			$GLOBALS['http_response_cookies']=[];
			function setcookie($name, $a, $b, $c, $d, $e, $f)
			{
				$GLOBALS['http_response_cookies'][]=[$name, $a, $b, $c, $d, $e, $f];
			}
			function filesize($file)
			{
				if($file === '/tmp/php/php1h4j1o-bad_filesize')
					return 124;

				return 123;
			}
			function mime_content_type($file)
			{
				if($file === '/tmp/php/php1h4j1o-bad_mime')
					return 'bad/mime';

				return 'text/plain';
			}
			function copy()
			{
				return true;
			}
			function unlink() {}
			function gmdate()
			{
				return 'gmdate';
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php',
			'var_export_contains.php'
		] as $library){
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;

		$errors=[];

		echo ' -> Testing http_input_stream'.PHP_EOL;
			$stream=http_input_stream::from_scalar('TEST CONTENT');
			echo '  -> eof (1/2)';
				if($stream->eof())
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream eof (1/2)';
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> is_readable';
				if($stream->is_readable())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream is_readable';
				}
			echo '  -> read';
				if($stream->read(3) === 'TES')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream read';
				}
			echo '  -> is_seekable';
				if($stream->is_seekable())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream is_seekable';
				}
			echo '  -> tell';
				if($stream->tell() === 3)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream tell';
				}
			echo '  -> seek/tell';
				$stream->seek(5);
				if($stream->tell() === 5)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream seek/tell';
				}
			echo '  -> rewind/tell';
				$stream->rewind();
				if($stream->tell() === 0)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream rewind/tell';
				}
			echo '  -> is_writable';
				if($stream->is_writable())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream is_writable';
				}
			echo '  -> get_contents';
				if($stream->get_contents() === 'TEST CONTENT')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream get_contents';
				}
			echo '  -> rewind/write/rewind/get_contents';
				$stream->rewind();
				if($stream->write('tesT COntEnt') === 12)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_input_stream rewind/write/rewind/get_contents 1/2';
				}
				$stream->rewind();
				if($stream->get_contents() === 'tesT COntEnt')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream rewind/write/rewind/get_contents 2/2';
				}
			echo '  -> eof (2/2)';
				if($stream->eof())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream eof (2/2)';
				}
			echo '  -> get_metadata';
				//echo ' ('.var_export_contains($stream->get_metadata(), '', true).')';
				if(var_export_contains(
					$stream->get_metadata(),
					"array('wrapper_type'=>'PHP','stream_type'=>'TEMP','mode'=>'w+b','unread_bytes'=>0,'seekable'=>true,'uri'=>'php://temp',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream get_metadata';
				}
			echo '  -> get_size';
				if($stream->get_size() === 12)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream get_size';
				}
			echo '  -> detach';
				$raw_stream=$stream->detach();
				if(is_resource($raw_stream))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_input_stream detach 1/2';
				}
				if($stream->detach() === null)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream detach 2/2';
				}
			echo '  -> close';
				$stream=http_input_stream::from_scalar('TEST CONTENT');
				$stream->close();
				if($stream->detach() === null)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_input_stream close';
				}

		echo ' -> Testing http_uri'.PHP_EOL;
			$uri=new http_uri('http://user:pass@host.com:8080/path?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph');
			echo '  -> scheme';
				if($uri->scheme() === 'http')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri scheme';
				}
			echo '  -> authority';
				if($uri->authority() === 'user:pass@host.com:8080')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri authority';
				}
			echo '  -> user_info';
				if($uri->user_info() === 'user:pass')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri user_info';
				}
			echo '  -> host';
				if($uri->host() === 'host.com')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri host';
				}
			echo '  -> port';
				if($uri->port() === 8080)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri port';
				}
			echo '  -> path';
				if($uri->path() === '/path')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri path';
				}
			echo '  -> query';
				if($uri->query() === 'query=qvalue&qarr[]=fval&qarr[]=fvab')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri query';
				}
			echo '  -> query_array';
				//echo ' ('.var_export_contains($uri->query_array(), '', true).')';
				if(var_export_contains(
					$uri->query_array(),
					"array('query'=>'qvalue','qarr'=>array(0=>'fval',1=>'fvab',),)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri query_array';
				}
			echo '  -> fragment';
				if($uri->fragment() === 'paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri fragment';
				}
			echo '  -> __toString';
				if($uri->__toString() === 'http://user:pass@host.com:8080/path?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri __toString';
				}
			echo '  -> set_scheme';
				if($uri->set_scheme('https')->__toString() === 'https://user:pass@host.com:8080/path?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_scheme';
				}
			echo '  -> set_user_info';
				if($uri->set_user_info('nuser', 'npass')->__toString() === 'http://nuser:npass@host.com:8080/path?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_user_info';
				}
			echo '  -> set_host';
				if($uri->set_host('nhost.com')->__toString() === 'http://user:pass@nhost.com:8080/path?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_host';
				}
			echo '  -> set_port';
				if($uri->set_port(8000)->__toString() === 'http://user:pass@host.com:8000/path?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_port';
				}
			echo '  -> set_path';
				if($uri->set_path('/path/new')->__toString() === 'http://user:pass@host.com:8080/path/new?query=qvalue&qarr[]=fval&qarr[]=fvab#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_path';
				}
			echo '  -> set_query';
				if($uri->set_query('nquery=nval')->__toString() === 'http://user:pass@host.com:8080/path?nquery=nval#paragraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_query';
				}
			echo '  -> set_fragment';
				if($uri->set_fragment('nparagraph')->__toString() === 'http://user:pass@host.com:8080/path?query=qvalue&qarr[]=fval&qarr[]=fvab#nparagraph')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_uri set_fragment';
				}

		echo ' -> Testing http_request'.PHP_EOL;
			$request=new http_request();
			echo '  -> accept';
				$_SERVER['HTTP_ACCEPT']='text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
				if(var_export_contains(
					$request->accept(),
					"array('text/html'=>1,'image/webp'=>1,'image/avif'=>1,'image/apng'=>1,'application/xml'=>'0.9','application/xhtml+xml'=>1,'application/signed-exchange'=>'0.9','*/*'=>'0.8',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request accept';
				}
			echo '  -> auth_user';
				if($request->auth_user() === false)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_request auth_user 1/2';
				}
				$_SERVER['PHP_AUTH_USER']='ba-user';
				if($request->auth_user() === 'ba-user')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request auth_user 2/2';
				}
			echo '  -> auth_password';
				if($request->auth_password() === false)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_request auth_password 1/2';
				}
				$_SERVER['PHP_AUTH_PW']='ba-pass';
				if($request->auth_password() === 'ba-pass')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request auth_password 2/2';
				}
			echo '  -> cache_control';
				$_SERVER['HTTP_CACHE_CONTROL']='public, max-age=604800, immutable';
				if(var_export_contains(
					$request->cache_control(),
					"array('public'=>true,'max-age'=>'604800','immutable'=>true,)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request cache_control';
				}
			echo '  -> content_length';
				$_SERVER['HTTP_CONTENT_LENGTH']='190';
				if($request->content_length() === '190')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request content_length';
				}
			echo '  -> content_type';
				$_SERVER['HTTP_CONTENT_TYPE']='text/html';
				if($request->content_type() === 'text/html')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request content_type';
				}
			echo '  -> cookie'.PHP_EOL;
				echo '   -> returns default value';
					if($request->cookie('examplecookie', null) === null)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request cookie returns default value';
					}
				echo '   -> returns current value';
					$_COOKIE['examplecookie']='goodvalue';
					if($request->cookie('examplecookie', null) === 'goodvalue')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request cookie returns current value';
					}
			echo '  -> charset';
				$_SERVER['HTTP_ACCEPT_CHARSET']='utf-8, iso-8859-1;q=0.7, *;q=0.9';
				if(var_export_contains(
					$request->charset(),
					"array('utf-8'=>1,'iso-8859-1'=>'0.7','*'=>'0.9',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request charset';
				}
			echo '  -> date';
				$_SERVER['HTTP_DATE']='Tue, 15 Nov 1994 08:12:31 GMT';
				if($request->date() === 'Tue, 15 Nov 1994 08:12:31 GMT')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request date';
				}
			echo '  -> do_not_track'.PHP_EOL;
				echo '   -> returns false';
					if(!$request->do_not_track())
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='http_request do_not_track returns false phase 1';
					}
					$_SERVER['HTTP_DNT']='1';
					if(!$request->do_not_track())
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request do_not_track returns false phase 2';
					}
				echo '   -> returns true';
					$request=new http_request();
					if($request->do_not_track())
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request do_not_track returns true';
					}
			echo '  -> encoding';
				$_SERVER['HTTP_ACCEPT_ENCODING']='gzip, deflate;q=0.9, br';
				if(var_export_contains(
					$request->encoding(),
					"array('gzip'=>1,'deflate'=>'0.9','br'=>1,)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request encoding';
				}
			echo '  -> get'.PHP_EOL;
				echo '   -> returns default value';
					if($request->get('exampleparam', null) === null)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request get returns default value';
					}
				echo '   -> returns current value';
					$_GET['exampleparam']='goodvalue';
					if($request->get('exampleparam', null) === 'goodvalue')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request get returns current value';
					}
			echo '  -> http_host';
				$_SERVER['HTTP_HOST']='example.com';
				if($request->http_host() === 'example.com')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request host';
				}
			echo '  -> is_https'.PHP_EOL;
				echo '   -> returns false';
					if(!$request->is_https())
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request is_https returns false';
					}
				echo '   -> returns true';
					$_SERVER['HTTPS']='on';
					if($request->is_https())
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request is_https returns true';
					}
			echo '  -> language';
				$_SERVER['HTTP_ACCEPT_LANGUAGE']='pl-PL,pl;q=0.9,en-US;q=0.8,en;q=0.7';
				if(var_export_contains(
					$request->language(),
					"array('pl-PL'=>1,'pl'=>'0.9','en-US'=>'0.8','en'=>'0.7',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request language';
				}
			echo '  -> method';
				$_SERVER['REQUEST_METHOD']='GET';
				if($request->method() === 'GET')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request method';
				}
			echo '  -> post'.PHP_EOL;
				echo '   -> returns default value';
					if($request->post('exampleparam', null) === null)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request post returns default value';
					}
				echo '   -> returns current value';
					$_POST['exampleparam']='goodvalue';
					if($request->post('exampleparam', null) === 'goodvalue')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request post returns current value';
					}
			echo '  -> pragma';
				$_SERVER['HTTP_PRAGMA']='no-cache';
				if($request->pragma() === 'no-cache')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request pragma';
				}
			echo '  -> protocol';
				$_SERVER['SERVER_PROTOCOL']='HTTP/1.0';
				if($request->protocol() === 'HTTP/1.0')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request protocol';
				}
			echo '  -> remote_host';
				$_SERVER['REMOTE_HOST']='127.0.0.1';
				if($request->remote_host() === '127.0.0.1')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request remote_host';
				}
			echo '  -> remote_port';
				$_SERVER['REMOTE_PORT']='80';
				if($request->remote_port() === '80')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request remote_port';
				}
			echo '  -> request_uri';
				$_SERVER['REQUEST_URI']='/app/uri?param=value';
				if($request->request_uri() === '/app/uri')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request request_uri';
				}
			echo '  -> user_agent';
				$_SERVER['HTTP_USER_AGENT']='Mozilla/5.0 (X11; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/12.0';
				if($request->user_agent() === 'Mozilla/5.0 (X11; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/12.0')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request user_agent';
				}
			echo '  -> upgrade_insecure_request'.PHP_EOL;
				echo '   -> returns false';
					if(!$request->upgrade_insecure_request())
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='http_request upgrade_insecure_request returns false phase 1';
					}
					$_SERVER['HTTP_UPGRADE_INSECURE_REQUEST']='1';
					if(!$request->upgrade_insecure_request())
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request upgrade_insecure_request returns false phase 2';
					}
				echo '   -> returns true';
					$request=new http_request();
					if($request->upgrade_insecure_request())
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_request upgrade_insecure_request returns true';
					}
			echo '  -> uri';
				if($request->uri() instanceof http_uri)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request uri';
				}
			echo '  -> input_stream';
				if($request->input_stream() instanceof http_input_stream)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_request input_stream';
				}
			echo '  -> json [SKIP]'.PHP_EOL;

		echo ' -> Testing http_session'.PHP_EOL;
			$session=new http_session();
			echo '  -> setter (set)';
				$session->set_examplevariable('goodvalue');
				echo ' [ OK ]'.PHP_EOL;
			echo '  -> getter (phase 1)'.PHP_EOL;
				echo '   -> returns current value';
					if($session->get_examplevariable(null) === 'goodvalue')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_session getter phase 1 returns current value';
					}
			echo '  -> setter (unset)';
				$session->set_examplevariable();
				echo ' [ OK ]'.PHP_EOL;
			echo '  -> getter (phase 2)'.PHP_EOL;
				echo '   -> returns default value';
					if($session->get_examplevariable(null) === null)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_session getter phase 2 returns default value';
					}

		echo ' -> Testing http_files'.PHP_EOL;
			$_SERVER['REQUEST_METHOD']='POST';
			$_FILES=[
				'uploaded_file'=>[
					'name'=>'filename.txt',
					'type'=>'text/plain',
					'tmp_name'=>'/tmp/php/php1h4j1o-uploaded_file',
					'error'=>UPLOAD_ERR_OK,
					'size'=>123
				],
				'bad_filesize'=>[
					'name'=>'filename.txt',
					'type'=>'text/plain',
					'tmp_name'=>'/tmp/php/php1h4j1o-bad_filesize',
					'error'=>UPLOAD_ERR_OK,
					'size'=>124
				],
				'bad_mime'=>[
					'name'=>'filename.txt',
					'type'=>'text/plain',
					'tmp_name'=>'/tmp/php/php1h4j1o-bad_mime',
					'error'=>UPLOAD_ERR_OK,
					'size'=>123
				]
			];
			$files=new http_files([
				'max_file_size'=>123,
				'allowed_mimes'=>['text/plain']
			]);
			echo '  -> list_uploaded_files';
				if(var_export_contains(
					$files->list_uploaded_files(),
					"array(0=>'uploaded_file',1=>'bad_filesize',2=>'bad_mime',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_files list_uploaded_files';
				}
			echo '  -> move_uploaded_file'.PHP_EOL;
				echo '   -> good file';
					if($files->move_uploaded_file('uploaded_file', 'testfile.txt'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_files move_uploaded_file good file';
					}
				echo '   -> bad filesize';
					$caught=false;
					try {
						$files->move_uploaded_file('bad_filesize', 'testfile.txt');
					} catch(\Throwable $error) {
						$caught=true;
					}
					if($caught)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_files move_uploaded_file bad filesize';
					}
				echo '   -> bad mime';
					$caught=false;
					try {
						$files->move_uploaded_file('bad_mime', 'testfile.txt');
					} catch(\Throwable $error) {
						$caught=true;
					}
					if($caught)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_files move_uploaded_file bad mime';
					}
			echo '  -> list_moved_files';
				if(var_export_contains(
					$files->list_moved_files(),
					"array('uploaded_file'=>array('name'=>'filename.txt','tmp_name'=>'/tmp/php/php1h4j1o-uploaded_file','file_name'=>'filename.txt','destination'=>'testfile.txt','moved_file'=>false,),)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_files list_moved_files';
				}

		echo ' -> Testing http_response'.PHP_EOL;
			echo '  -> send_response [....]'.PHP_EOL;
				$GLOBALS['middleware_before']=0;
				$GLOBALS['middleware_before_arg']=false;
				$GLOBALS['middleware_after']=0;
				$GLOBALS['middleware_after_arg']=false;
				http_response
				::	middleware_arg('test', 'value')
				::	middleware(function($test){
						++$GLOBALS['middleware_before'];

						if($test === 'value')
							$GLOBALS['middleware_before_arg']=true;
					}, true)
				::	middleware(function($test){
						++$GLOBALS['middleware_after'];

						if($test === 'value')
							$GLOBALS['middleware_after_arg']=true;
					});
				$response=new http_response();
				$response
				->	cookie('testcookie', 'testvalue', 2, 'testpath', 'testdomain', true, true)
				->	etag('ETAGG')
				->	expire(10, true)
				->	content_type('custom/type')->charset('czarset')
				->	response_content('CZIKITA CZIKITA')
				->	status(http_response::http_loop_detected);
				ob_start();
				$response->send_response();
				$response_content=ob_get_clean();
			echo '  -> send_response middleware'.PHP_EOL;
				echo '   -> before';
					if($GLOBALS['middleware_before'] === 1)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='http_response send_response middleware before 1/2';
					}
					if($GLOBALS['middleware_before_arg'])
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_response send_response middleware before 2/2';
					}
				echo '   -> after';
					if($GLOBALS['middleware_after'] === 1)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]='http_response send_response middleware after 1/2';
					}
					if($GLOBALS['middleware_after_arg'])
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='http_response send_response middleware after 2/2';
					}
			echo '  -> cookie';
				if(var_export_contains(
					$GLOBALS['http_response_cookies'],
					"array(0=>array(0=>'testcookie',1=>'testvalue',2=>2,3=>'testpath',4=>'testdomain',5=>true,6=>true,),)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response cookie';
				}
			echo '  -> get_cookie';
				//echo ' ('.var_export_contains($response->get_cookie(), '', true).')';
				if(var_export_contains(
					$response->get_cookie(),
					"array('testcookie'=>array(0=>'testvalue',1=>2,2=>'testpath',3=>'testdomain',4=>true,5=>true,),)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response get_cookie';
				}
			echo '  -> cookie_expire/get_cookie';
				$response->cookie_expire('testcookie', 'testpath', 'testdomain', true, true);
				//echo ' ('.var_export_contains($response->get_cookie('testcookie'), '', true).')';
				if(var_export_contains(
					$response->get_cookie('testcookie'),
					"array(0=>'',1=>-1,2=>'testpath',3=>'testdomain',4=>true,5=>true,)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response cookie_expire/get_cookie';
				}
			echo '  -> cookie_remove/get_cookie';
				$response->cookie_remove('testcookie');
				if($response->get_cookie('testcookie') === false)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response cookie_remove/get_cookie';
				}
			echo '  -> etag';
				if(
					isset($GLOBALS['http_response_headers']['Pragma']) &&
					($GLOBALS['http_response_headers']['Pragma'] === 'cache') &&
					isset($GLOBALS['http_response_headers']['ETag']) &&
					($GLOBALS['http_response_headers']['ETag'] === '"ETAGG"')
				)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response etag';
				}
			echo '  -> expire';
				if(
					isset($GLOBALS['http_response_headers']['Pragma']) &&
					($GLOBALS['http_response_headers']['Pragma'] === 'cache') &&
					isset($GLOBALS['http_response_headers']['Cache-Control']) &&
					($GLOBALS['http_response_headers']['Cache-Control'] === 'max-age=10, must-revalidate') &&
					isset($GLOBALS['http_response_headers']['Expires']) &&
					($GLOBALS['http_response_headers']['Expires'] === 'gmdate GMT')
				)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response expire';
				}
			echo '  -> no_cache [SKIP]'.PHP_EOL;
			echo '  -> content_type/charset';
				if(
					isset($GLOBALS['http_response_headers']['Content-Type']) &&
					($GLOBALS['http_response_headers']['Content-Type'] === 'custom/type;charset=czarset')
				)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response content_type/charset';
				}
			echo '  -> header/has_header/get_header';
				$response->header('X-Test-Header', 'test value');
				if($response->has_header('X-Test-Header'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_response header/has_header/get_header 1/2';
				}
				if($response->get_header('X-Test-Header') === 'test value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response header/has_header/get_header 2/2';
				}
			echo '  -> header_append/get_header';
				$response->header_append('X-Test-Header', 'append value');
				if($response->get_header('X-Test-Header') === 'test value,append value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response header_append/get_header';
				}
			echo '  -> header_remove/has_header/get_header';
				$response->header_remove('X-Test-Header');
				if($response->has_header('X-Test-Header'))
				{
					echo ' [FAIL]';
					$errors[]='http_response header_remove/has_header/get_header 1/2';
				}
				else
					echo ' [ OK ]';
				if($response->get_header('X-Test-Header') === false)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response header_remove/has_header/get_header 2/2';
				}
			echo '  -> response_content';
				if($response_content === 'CZIKITA CZIKITA')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response response_content';
				}
			echo '  -> get_response_content';
				if($response->get_response_content() === 'CZIKITA CZIKITA')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response get_response_content';
				}
			echo '  -> get_response_stream';
				if($response->get_response_stream() instanceof http_input_stream)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_response get_response_stream 1/2';
				}
				if($response->get_response_stream()->__toString() === 'CZIKITA CZIKITA')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response get_response_stream 2/2';
				}
			echo '  -> status';
				if($GLOBALS['http_response_code'] === 508)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response status';
				}
			echo '  -> get_status';
				if($response->get_status()[0] === 508)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='http_response get_status 1/2';
				}
				if($response->get_status()[1] === 'Loop Detected')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='http_response get_status 2/2';
				}
			echo '  -> send_redirect [SKIP]'.PHP_EOL;
			echo '  -> send_file [SKIP]'.PHP_EOL;
			echo '  -> send_json [SKIP]'.PHP_EOL;

		if(!empty($errors))
		{
			echo PHP_EOL;

			foreach($errors as $error)
				echo $error.' failed'.PHP_EOL;

			exit(1);
		}
	}
?>