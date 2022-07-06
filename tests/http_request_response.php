<?php
	/*
	 * http_request_response.php library test
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
			class Exception extends \Exception {}
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

		echo ' -> Testing http_request'.PHP_EOL;
			$request=new http_request();
		echo '  -> accept';
			$_SERVER['HTTP_ACCEPT']='text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
			if(str_replace(["\n", ' '], '', var_export($request->accept(), true)) === "array('text/html'=>1,'image/webp'=>1,'image/avif'=>1,'image/apng'=>1,'application/xml'=>'0.9','application/xhtml+xml'=>1,'application/signed-exchange'=>'0.9','*/*'=>'0.8',)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='http_request accept';
			}
		echo '  -> cache_control';
			$_SERVER['HTTP_CACHE_CONTROL']='public, max-age=604800, immutable';
			if(str_replace(["\n", ' '], '', var_export($request->cache_control(), true)) === "array('public'=>true,'max-age'=>'604800','immutable'=>true,)")
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
			if(str_replace(["\n", ' '], '', var_export($request->charset(), true)) === "array('utf-8'=>1,'iso-8859-1'=>'0.7','*'=>'0.9',)")
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
			if(str_replace(["\n", ' '], '', var_export($request->encoding(), true)) === "array('gzip'=>1,'deflate'=>'0.9','br'=>1,)")
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
			if(str_replace(["\n", ' '], '', var_export($request->language(), true)) === "array('pl-PL'=>1,'pl'=>'0.9','en-US'=>'0.8','en'=>'0.7',)")
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
			$_SERVER['REQUEST_URI']='/app/uri?param=value';
			if($request->uri() === '/app/uri')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='http_request uri';
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
			if(str_replace(["\n", ' '], '', var_export($files->list_uploaded_files(), true)) === "array(0=>'uploaded_file',1=>'bad_filesize',2=>'bad_mime',)")
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
			} catch(Exception $e) {
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
			} catch(Exception $e) {
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
			if(str_replace(["\n", ' '], '', var_export($files->list_moved_files(), true)) === "array('uploaded_file'=>array('name'=>'filename.txt','tmp_name'=>'/tmp/php/php1h4j1o-uploaded_file','file_name'=>'filename.txt','destination'=>'testfile.txt','moved_file'=>false,),)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='http_files list_moved_files';
			}

		echo ' -> Testing http_response'.PHP_EOL;
			$response=new http_response();
				$response->cookie('testcookie', 'testvalue', 2, 'testpath', 'testdomain', true, true);
				$response->etag('ETAGG');
				$response->expire(10, true);
				$response->content_type('custom/type')->charset('czarset');
				$response->response_content('CZIKITA CZIKITA');
				$response->status(http_response::http_loop_detected);
			ob_start();
				$response->send_response();
			$response_content=ob_get_clean();
		echo '  -> cookie';
			if(str_replace(["\n", ' '], '', var_export($GLOBALS['http_response_cookies'], true)) === "array(0=>array(0=>'testcookie',1=>'testvalue',2=>2,3=>'testpath',4=>'testdomain',5=>true,6=>true,),)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='http_response cookie';
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
		echo '  -> header [SKIP]'.PHP_EOL;
		echo '  -> response_content';
			if($response_content === 'CZIKITA CZIKITA')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='http_response response_content';
			}
		echo '  -> status';
			if($GLOBALS['http_response_code'] === 508)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='http_response status';
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