<?php
	/*
	 * OOP overlay for standard request-response handling
	 *
	 * Classes:
	 *  http_request (getters)
	 *  http_session (overlay)
	 *  http_files (manager)
	 *   constructor params (optional): destination=>string, max_file_size=>int_bytes, allowed_mimes=>['mime1', 'mime2']
	 *   warning: throws an exception if the request method is not POST
	 *  http_response (setters and senders)
	 *   note: you can only send a response once
	 *
	 * Request:
	 *  accept() [returns array(mime=>quality)]
	 *  cache_control() [returns array(parameter=>value)]
	 *  content_length() [returns header value]
	 *  content_type() [returns header value]
	 *  cookie(string_key, default_value=null) [returns cookie value (string) or default value]
	 *  charset() [returns array(charset=>quality)]
	 *   for Accept-Charset header
	 *  date() [returns header value]
	 *  do_not_track() [returns bool]
	 *   checks if DNT header is present
	 *  encoding() [returns array(encoding=>quality)]
	 *   for Accept-Encoding header
	 *  get(string_key, default_value=null) [returns sanitized GET value or default value]
	 *  http_host() [returns header value]
	 *   for Host header
	 *  is_https() [returns bool]
	 *  language() [returns array(language=>quality)]
	 *   for Accept-Language header
	 *  method() [returns request method string]
	 *  post(string_key, default_value=null) [returns sanitized POST value or default value]
	 *  pragma() [returns header value]
	 *  protocol() [returns current protocol string]
	 *  remote_host() [returns string or false]
	 *  remote_port() [returns string or false]
	 *  user_agent() [returns header value]
	 *  upgrade_insecure_request() [returns bool]
	 *   checks if Upgrade-Insecure-Request header is present
	 *  uri() [returns string]
	 *   the returned string is with no GET parameters
	 *  json() [returns decoded data]
	 *   works only with POST
	 *
	 * Session:
	 *  get_key_name(default_value=null) [returns (default) value]
	 *  set_key_name(value) [returns self]
	 *   if no value is passed, the key will be removed
	 *
	 * File upload:
	 *  list_uploaded_files() [returns array]
	 *  list_moved_files()
	 *   [returns array(file=>[
	 *    name=>string,
	 *    tmp_name=>string,
	 *    file_name=>string_after_sanitization,
	 *    destination=>string,
	 *    moved_file=>string_realpath
	 *   ])]
	 *  move_uploaded_file(string_file, string_destination=null, string_file_name=null) [returns bool]
	 *   if destination is null, value from constructor wiil be used
	 *   if file_name is null, sanitized $_FILES[file]['name'] will be used
	 *
	 * Response:
	 *  [static] redirect(string_url, bool_exit=true)
	 *   sends http_moved_permanently
	 *  cookie(string_name, string_value, int_expires=0, string_path='', string_domain='', bool_secure=false, bool_httponly=false) [returns self]
	 *   if https is used, sets secure to true
	 *  etag(string_etag) [returns self]
	 *  expire(int_expire, bool_must_revalidate=true) [returns self]
	 *  no_cache() [returns self]
	 *  charset(string_charset) [returns self]
	 *   for Content-Type header
	 *  content_type(string_content_type) [returns self]
	 *  header(string_header, string_value) [returns self]
	 *   for other headers
	 *  response_content(content) [returns self]
	 *   output string
	 *  status(int_status) [returns self]
	 *   response code
	 *  send_response() [returns bool]
	 *   optional, will be executed by destructor
	 *  send_redirect(string_url, bool_exit=true)
	 *   shortcut for static method
	 *  send_file(string_file_path, string_file_name, string_file_description, string_content_type) [returns bool]
	 *  send_json(content) [returns bool]
	 *   note: response_content must not be called
	 *
	 * Response code constants:
	 *  http_continue
	 *  http_switching_protocols
	 *  http_processing
	 *  http_early_hints
	 *  http_ok
	 *  http_created
	 *  http_accepted
	 *  http_non_authoritative_information
	 *  http_no_content
	 *  http_reset_content
	 *  http_partial_content
	 *  http_multi_status
	 *  http_already_reported
	 *  http_im_used
	 *  http_multiple_choices
	 *  http_moved_permanently
	 *  http_found
	 *  http_see_other
	 *  http_not_modified
	 *  http_use_proxy
	 *  http_reserved
	 *  http_temporary_redirect
	 *  http_permanently_redirect
	 *  http_bad_request
	 *  http_unauthorized
	 *  http_payment_required
	 *  http_forbidden
	 *  http_not_found
	 *  http_method_not_allowed
	 *  http_not_acceptable
	 *  http_proxy_authentication_required
	 *  http_request_timeout
	 *  http_conflict
	 *  http_gone
	 *  http_length_required
	 *  http_precondition_failed
	 *  http_request_entity_too_large
	 *  http_request_uri_too_long
	 *  http_unsupported_media_type
	 *  http_requested_range_not_satisfiable
	 *  http_expectation_failed
	 *  http_i_am_a_teapot
	 *  http_misdirected_request
	 *  http_unprocessable_entity
	 *  http_locked
	 *  http_failed_dependency
	 *  http_too_early
	 *  http_upgrade_required
	 *  http_precondition_required
	 *  http_too_many_requests
	 *  http_request_header_fields_too_large
	 *  http_unavailable_for_legal_reasons
	 *  http_internal_server_error
	 *  http_not_implemented
	 *  http_bad_gateway
	 *  http_service_unavailable
	 *  http_gateway_timeout
	 *  http_version_not_supported
	 *  http_variant_also_negotiates_experimental
	 *  http_insufficient_storage
	 *  http_loop_detected
	 *  http_not_extended
	 *  http_network_authentication_required
	 *
	 * Source:
	 *  https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/Response.php
	 */

	class http_request
	{
		protected $cache_get=array();
		protected $cache_http_headers=array();
		protected $cache_parsed_http_headers=array();
		protected $cache_post=array();
		protected $cache_uri=null;

		protected function get_http_header($header, $cache=true)
		{
			if(isset($this->cache_http_headers[$header]))
				return $this->cache_http_headers[$header];

			$variable_name='HTTP_'.strtr(strtoupper($header), '-', '_');

			if(isset($_SERVER[$variable_name]))
			{
				if($cache)
					$this->cache_http_headers[$header]=$_SERVER[$variable_name];

				return $_SERVER[$variable_name];
			}

			if($cache)
				$this->cache_http_headers[$header]=false;

			return false;
		}
		protected function process_quality_http_header($header)
		{
			if(isset($this->cache_parsed_http_headers[$header]))
				return $this->cache_parsed_http_headers[$header];

			$params=$this->get_http_header($header, false);

			if($params === false)
			{
				$this->cache_parsed_http_headers[$header]=false;
				return false;
			}

			$params_sorted=array();
			foreach(explode(',', $params) as $param)
			{
				if(strpos($param, ';') === false)
					$params_sorted[trim($param)]=1;
				else
					$params_sorted[trim(substr($param, 0, strpos($param, ';')))]=trim(substr($param, strrpos($param, '=')+1));
			}
			krsort($params_sorted);

			$this->cache_parsed_http_headers[$header]=$params_sorted;

			return $params_sorted;
		}

		public function accept()
		{
			return $this->process_quality_http_header('Accept');
		}
		public function cache_control()
		{
			if(isset($this->cache_parsed_http_headers['Cache-Control']))
				return $this->cache_parsed_http_headers['Cache-Control'];

			$params=$this->get_http_header('Cache-Control', false);

			if($params === false)
			{
				$this->cache_parsed_http_headers['Cache-Control']=false;
				return false;
			}

			$params_sorted=array();
			foreach(explode(',', $params) as $param)
			{
				$param_delimiter=strpos($param, '=');
				if($param_delimiter === false)
					$params_sorted[trim($param)]=true;
				else
					$params_sorted[trim(substr($param, 0, $param_delimiter))]=trim(substr($param, $param_delimiter+1));
			}
			krsort($params_sorted);

			$this->cache_parsed_http_headers['Cache-Control']=$params_sorted;

			return $params_sorted;
		}
		public function content_length()
		{
			return $this->get_http_header('Content-Length');
		}
		public function content_type()
		{
			return $this->get_http_header('Content-Type');
		}
		public function cookie(string $key, $default_value=null)
		{
			if(isset($_COOKIE[$key]))
				return $_COOKIE[$key];

			return $default_value;
		}
		public function charset()
		{
			return $this->process_quality_http_header('Accept-Charset');
		}
		public function date()
		{
			return $this->get_http_header('Date');
		}
		public function do_not_track()
		{
			if(isset($this->cache_parsed_http_headers['DNT']))
				return $this->cache_parsed_http_headers['DNT'];

			if($this->get_http_header('DNT', false) === false)
			{
				$this->cache_parsed_http_headers['DNT']=false;
				return false;
			}

			$this->cache_parsed_http_headers['DNT']=true;
			return true;
		}
		public function encoding()
		{
			return $this->process_quality_http_header('Accept-Encoding');
		}
		public function get(string $key, $default_value=null)
		{
			if(isset($this->cache_get[$key]))
				return $this->cache_get[$key];

			if(isset($_GET[$key]))
			{
				$this->cache_get[$key]=filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
				return $this->cache_get[$key];
			}

			return $default_value;
		}
		public function http_host()
		{
			return $this->get_http_header('Host');
		}
		public function is_https()
		{
			return isset($_SERVER['HTTPS']);
		}
		public function language()
		{
			return $this->process_quality_http_header('Accept-Language');
		}
		public function method()
		{
			if(!isset($_SERVER['REQUEST_METHOD']))
				return false;

			return $_SERVER['REQUEST_METHOD'];
		}
		public function post(string $key, $default_value=null)
		{
			if(isset($this->cache_post[$key]))
				return $this->cache_post[$key];

			if(isset($_POST[$key]))
			{
				$this->cache_post[$key]=filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
				return $this->cache_post[$key];
			}

			return $default_value;
		}
		public function pragma()
		{
			return $this->get_http_header('Pragma');
		}
		public function protocol()
		{
			if(!isset($_SERVER['SERVER_PROTOCOL']))
				return false;

			return $_SERVER['SERVER_PROTOCOL'];
		}
		public function remote_host()
		{
			if(!isset($_SERVER['REMOTE_HOST']))
				return false;

			return $_SERVER['REMOTE_HOST'];
		}
		public function remote_port()
		{
			if(!isset($_SERVER['REMOTE_PORT']))
				return false;

			return $_SERVER['REMOTE_PORT'];
		}
		public function user_agent()
		{
			return $this->get_http_header('User-Agent');
		}
		public function upgrade_insecure_request()
		{
			if($this->get_http_header('Upgrade-Insecure-Request') === false)
				return false;

			return true;
		}
		public function uri()
		{
			if($this->cache_uri === null)
				$this->cache_uri=strtok($_SERVER['REQUEST_URI'], '?');

			return $this->cache_uri;
		}

		public function json()
		{
			if(($this->method() !== 'POST') && ($this->content_type() !== 'application/json'))
				return false;

			return json_decode(trim(file_get_contents('php://input')));
		}
	}
	class http_session
	{
		public function __construct()
		{
			if(session_status() !== PHP_SESSION_ACTIVE)
				throw new Exception('Session not started');
		}
		public function __call($key, $value)
		{
			switch(substr($key, 0, 4))
			{
				case 'get_':
					$key=substr($key, 4);

					if(isset($_SESSION[$key]))
						return $_SESSION[$key];

					if(isset($value[0]))
						return $value[0];

					return null;
				break;
				case 'set_':
					$key=substr($key, 4);

					if(isset($value[0]))
						$_SESSION[$key]=$value[0];
					else
						unset($_SESSION[$key]);

					return $this;
				break;
				default:
					throw new Exception('No get_ or set_ prefix');
			}
		}
	}
	class http_files
	{
		protected $destination=null;
		protected $max_file_size=null;
		protected $allowed_mimes=array();
		protected $moved_files=array();

		public function __construct(array $params=array())
		{
			if($_SERVER['REQUEST_METHOD'] !== 'POST')
				throw new Exception('Only POST method is supported');

			foreach(['destination', 'max_file_size', 'allowed_mimes'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];
		}

		public function list_uploaded_files()
		{
			$result=array();

			foreach($_FILES as $file_name=>$file_array)
				if($file_array['error'] === UPLOAD_ERR_OK)
					$result[]=$file_name;

			return $result;
		}
		public function list_moved_files()
		{
			return $this->moved_files;
		}
		public function move_uploaded_file(string $file, string $destination=null, string $file_name=null)
		{
			if(!isset($_FILES[$file]))
				throw new Exception($file.' not exists');

			if($_FILES[$file]['error'] !== UPLOAD_ERR_OK)
				throw new Exception($file.' was not uploaded correctly');

			$file_size=filesize($_FILES[$file]['tmp_name']);
			if($file_size === 0)
			{
				unlink($_FILES[$file]['tmp_name']);
				unset($_FILES[$file]);
				throw new Exception($file.' is empty');
			}
			if(($this->max_file_size !== null) && ($file_size > $this->max_file_size))
			{
				unlink($_FILES[$file]['tmp_name']);
				unset($_FILES[$file]);
				throw new Exception($file.' has exceeded max size');
			}

			if(!empty($this->allowed_mimes))
			{
				$file_mime=mime_content_type($_FILES[$file]['tmp_name']);
				if(!in_array($file_mime, $this->allowed_mimes))
				{
					unlink($_FILES[$file]['tmp_name']);
					unset($_FILES[$file]);
					throw new Exception($file.' is '.$file_mime.' which is not allowed');
				}
			}

			if($destination === null)
			{
				if($this->destination === null)
					throw new Exception('The destination is not defined either globally or locally');

				$destination=$this->destination;
			}

			if($file_name === null)
				$file_name=preg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', basename($_FILES[$file]['name']));

			if(file_exists($destination.$file_name))
				throw new Exception($destination.$file_name.' already exists');

			$result=copy($_FILES[$file]['tmp_name'], $destination.$file_name);
			if($result)
			{
				unlink($_FILES[$file]['tmp_name']);
				$this->moved_files[$file]=[
					'name'=>$_FILES[$file]['name'],
					'tmp_name'=>$_FILES[$file]['tmp_name'],
					'file_name'=>$file_name,
					'destination'=>$destination,
					'moved_file'=>realpath($destination.$file_name)
				];
				unset($_FILES[$file]);
			}

			return $result;
		}
	}
	class http_response
	{
		public const http_continue=100;
		public const http_switching_protocols=101;
		public const http_processing=102;
		public const http_early_hints=103;
		public const http_ok=200;
		public const http_created=201;
		public const http_accepted=202;
		public const http_non_authoritative_information=203;
		public const http_no_content=204;
		public const http_reset_content=205;
		public const http_partial_content=206;
		public const http_multi_status=207;
		public const http_already_reported=208;
		public const http_im_used=226;
		public const http_multiple_choices=300;
		public const http_moved_permanently=301;
		public const http_found=302;
		public const http_see_other=303;
		public const http_not_modified=304;
		public const http_use_proxy=305;
		public const http_reserved=306;
		public const http_temporary_redirect=307;
		public const http_permanently_redirect=308;
		public const http_bad_request=400;
		public const http_unauthorized=401;
		public const http_payment_required=402;
		public const http_forbidden=403;
		public const http_not_found=404;
		public const http_method_not_allowed=405;
		public const http_not_acceptable=406;
		public const http_proxy_authentication_required=407;
		public const http_request_timeout=408;
		public const http_conflict=409;
		public const http_gone=410;
		public const http_length_required=411;
		public const http_precondition_failed=412;
		public const http_request_entity_too_large=413;
		public const http_request_uri_too_long=414;
		public const http_unsupported_media_type=415;
		public const http_requested_range_not_satisfiable=416;
		public const http_expectation_failed=417;
		public const http_i_am_a_teapot=418;
		public const http_misdirected_request=421;
		public const http_unprocessable_entity=422;
		public const http_locked=423;
		public const http_failed_dependency=424;
		public const http_too_early=425;
		public const http_upgrade_required=426;
		public const http_precondition_required=428;
		public const http_too_many_requests=429;
		public const http_request_header_fields_too_large=431;
		public const http_unavailable_for_legal_reasons=451;
		public const http_internal_server_error=500;
		public const http_not_implemented=501;
		public const http_bad_gateway=502;
		public const http_service_unavailable=503;
		public const http_gateway_timeout=504;
		public const http_version_not_supported=505;
		public const http_variant_also_negotiates_experimental=506;
		public const http_insufficient_storage=507;
		public const http_loop_detected=508;
		public const http_not_extended=510;
		public const http_network_authentication_required=511;

		protected static $sent=false;

		protected $http_headers=[
			'Content-Type'=>'text/html',
			'X-Content-Type-Options'=>'nosniff',
			'X-Frame-Options'=>'SAMEORIGIN',
			'X-XSS-Protection'=>'0'
		];
		protected $http_status=200;
		protected $http_content_charset=null;
		protected $http_cookies=array();
		protected $response_content=null;

		public static function redirect(string $url, bool $exit=true)
		{
			if(headers_sent())
				throw new Exception('HTTP headers already sent');

			http_response_code(301);
			header('Location: '.$url);

			if($exit)
				exit();
		}

		public function __construct()
		{
			if(headers_sent())
				throw new Exception('HTTP headers already sent');
		}
		public function __destruct()
		{
			$this->send_response();
		}

		public function cookie(
			string $name,
			string $value,
			int $expires=0,
			string $path='',
			string $domain='',
			bool $secure=false,
			bool $httponly=false
		){
			$this->http_cookies[$name]=[$value, $expires, $path, $domain, $secure, $httponly];
			return $this;
		}
		public function etag(string $etag)
		{
			$this->http_headers['Pragma']='cache';
			$this->http_headers['ETag']='"'.$etag.'"';

			return $this;
		}
		public function expire(int $expire, bool $must_revalidate=true)
		{
			$this->http_headers['Pragma']='cache';
			$this->http_headers['Cache-Control']='max-age='.$expire;
			$this->http_headers['Expires']=gmdate('D, d M Y H:i:s', time()+$expire).' GMT';

			if($must_revalidate)
				$this->http_headers['Cache-Control'].=', must-revalidate';

			return $this;
		}
		public function no_cache()
		{
			$this->http_headers['Cache-Control']='no-cache';
			$this->http_headers['Expires']='-1';
			$this->http_headers['Pragma']='no-cache';
			unset($this->http_headers['ETag']);

			return $this;
		}
		public function charset(string $charset)
		{
			$this->http_content_charset=$charset;
			return $this;
		}
		public function content_type(string $content_type)
		{
			$this->http_headers['Content-Type']=$content_type;
			return $this;
		}
		public function header(string $header, string $value)
		{
			header($header.': '.$value);
			return $this;
		}
		public function response_content($content)
		{
			if($this->response_content === null)
				$this->response_content=$content;
			else
				$this->response_content.=$content;

			return $this;
		}
		public function status(int $status)
		{
			$this->http_status=$status;
			return $this;
		}

		public function send_response()
		{
			if(static::$sent === true)
				return false;

			if(headers_sent())
				throw new Exception('HTTP headers already sent');

			if($this->http_content_charset !== null)
				$this->http_headers['Content-Type'].=';charset='.$this->http_content_charset;

			if(isset($_SERVER['HTTP_TRANSFER_ENCODING']))
				unset($this->http_headers['Content-Length']);

			http_response_code($this->http_status);

			foreach($this->http_headers as $header_name=>$header_content)
				header($header_name.': '.$header_content);

			foreach($this->http_cookies as $cookie_name=>$cookie_params)
			{
				if(isset($_SERVER['HTTPS']))
					$cookie_params[4]=true;

				setcookie(
					$cookie_name,
					$cookie_params[0],
					$cookie_params[1],
					$cookie_params[2],
					$cookie_params[3],
					$cookie_params[4],
					$cookie_params[5]
				);
			}

			if($this->response_content !== null)
				echo $this->response_content;

			static::$sent=true;

			return true;
		}
		public function send_redirect(string $url, bool $exit=true)
		{
			static::redirect($url, $exit);
		}

		public function send_file(
			string $file_path,
			string $file_name,
			string $file_description,
			string $content_type='application/octet-stream'
		){
			if(!file_exists($file_path))
				return false;

			while(ob_get_level())
				ob_end_clean();

			$this
				->content_type($content_type)
				->header('Content-Length', filesize($file_path))
				->header('Content-Disposition', 'attachment; filename='.$file_name)
				->header('Content-Description', $file_description);

			if(!$this->send_response())
				return false;

			if(readfile($path) === false)
				return false;

			return true;
		}
		public function send_json($content)
		{
			if($this->response_content !== null)
				throw new Exception('response_content method was called before');

			$this->content_type('application/json');
			$this->response_content=json_encode($content, JSON_UNESCAPED_UNICODE);
			return $this->send_response();
		}
	}
?>