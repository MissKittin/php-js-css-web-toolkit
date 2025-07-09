<?php
	/*
	 * OOP overlay for standard request-response handling
	 *
	 * Note:
	 *  throws an http_request_response_exception on error
	 *
	 * Classes:
	 *  http_input_stream (php://input streamer)
	 *   optional constructor arguments:
	 *    string_or_resource $input='php://input'
	 *    string $mode='r' // used only if $input is a string
	 *   note: this class has __toString() method
	 *   warning: throws an http_request_response_exception on error
	 *  http_uri (URI parser)
	 *   constructor argument: string $uri
	 *   note: this class has __toString() method
	 *   warning: throws an http_request_response_exception if URI is invalid
	 *  http_request (getters)
	 *  http_session (overlay)
	 *   optional constructor argument: string $subkey=null
	 *   if the argument is defined, manipulates the subarray with the given key name
	 *  http_files (manager)
	 *   constructor params (optional): destination=>string, max_file_size=>int_bytes, allowed_mimes=>['mime1', 'mime2']
	 *   warning: throws an http_request_response_exception if the request method is not POST
	 *  http_response (setters and senders)
	 *   note: you can only send a response once
	 *
	 * Input stream:
	 *  [static] from_scalar(scalar_input) [returns http_input_stream instance]
	 *   new instance for php://temp
	 *  detach() [returns resource]
	 *   take the stream out of the streamer
	 *  close()
	 *   close the stream
	 *  eof() [returns bool]
	 *   check if the end of the stream has been reached
	 *  read(int_length) [returns string]
	 *  rewind()
	 *   to the beginning
	 *  seek(int_offset, int_whence=SEEK_SET)
	 *   rewind to position int_offset
	 *  tell() [returns int]
	 *   current stream position
	 *  write(string) [returns int]
	 *  is_readable() [returns bool]
	 *  is_seekable() [returns bool]
	 *  is_writable() [returns bool]
	 *  get_contents() [returns string]
	 *   rewind to start and return contents
	 *  get_metadata(string_key=null) [returns array or mixed]
	 *   see stream_get_meta_data PHP function
	 *   if string_key is not null, returns the key value or null if the key is not defined
	 *  get_size() [returns int]
	 *   read the stream size
	 *
	 * URI:
	 *  scheme() [returns string]
	 *   'http' or 'https'
	 *  authority() [returns string]
	 *   [user[:pass]@]host[:port]
	 *  user_info() [returns string]
	 *   user:password
	 *  host() [returns string]
	 *   host name or IP
	 *  port() [returns int]
	 *   server port
	 *  path() [returns string]
	 *   request path
	 *  query() [returns string]
	 *   raw GET string (without ? at the beginning)
	 *  query_array() [returns array]
	 *   parsed GET string
	 *  fragment() [returns string]
	 *   hash string (without # at the beginning)
	 *  set_scheme(string_scheme) [returns new self]
	 *   new instance with changed URI scheme
	 *  set_user_info(string_username, string_password=null) [returns new self]
	 *   new instance with changed credentials
	 *  set_host(string_hostname) [returns new self]
	 *   new instance with changed host
	 *  set_port(int_port) [returns new self]
	 *   new instance with changed port
	 *  set_path(string_path) [returns new self]
	 *   new instance with changed path
	 *  set_query(string_or_array_query) [returns new self]
	 *   new instance with changed GET parameters
	 *   note: string is raw GET and array is key=>value string array
	 *  set_fragment(string_id) [returns new self]
	 *   new instance with changed hash string
	 *
	 * Request:
	 *  accept() [returns array(mime=>quality)]
	 *  auth_user() [returns string or false]
	 *   HTTP basic auth
	 *  auth_password() [returns string or false]
	 *   HTTP basic auth
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
	 *  request_uri(bool_with_get_params=false) [returns string or false]
	 *   if false, it will strip the GET parameters from URI
	 *  user_agent() [returns header value]
	 *  upgrade_insecure_request() [returns bool]
	 *   checks if Upgrade-Insecure-Request header is present
	 *  uri() [returns http_uri instance]
	 *   with current URL
	 *  input_stream() [returns http_input_stream instance]
	 *   returns an input streamer instance for php://input
	 *  json() [returns decoded data]
	 *   requires Content-Type: application/json HTTP header
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
	 *  [static] middleware(closure_callback, bool_before=false) [returns self]
	 *   register new middleware function
	 *   before sending headers (via PHP header() function) and body (true)
	 *   or after sending headers and before sending body (false, default)
	 *  [static] middleware_arg(string_arg_name, value) [returns self]
	 *   register middleware argument (will be passed to middleware closures)
	 *   note: value can be anything
	 *   warning: string_arg_name is useful for overriding
	 *    what really matters is the order in which the new arguments are registered
	 *  [static] redirect(string_url, bool_exit=true)
	 *   sends http_moved_permanently
	 *  cookie(string_name, string_value, int_expires=0, string_path='', string_domain='', bool_secure=false, bool_httponly=false) [returns self]
	 *   if https is used, sets secure to true
	 *  get_cookie(string_name=null) [returns array or false]
	 *   if a non-existent cookie name is given, it will return null
	 *  cookie_remove(string_name) [returns self]
	 *   remove previously defined cookie
	 *  cookie_expire(string_name, string_path='', string_domain='', bool_secure=false, bool_httponly=false) [returns self]
	 *   send cookie with expiration date -1
	 *  etag(string_etag) [returns self]
	 *  expire(int_expire, bool_must_revalidate=true) [returns self]
	 *  no_cache() [returns self]
	 *  charset(string_charset) [returns self]
	 *   for Content-Type header
	 *  content_type(string_content_type) [returns self]
	 *  has_header(string_header) [returns bool]
	 *  get_header(string_header=null) [returns array or string or false]
	 *   get header(s) defined by header() method
	 *   or false if the header is not defined
	 *   note: if string_header is null, will return all headers in the key=>value array
	 *  header(string_header, string_value) [returns self]
	 *   for other headers
	 *  header_append(string_header, string_value, char_delimiter=',') [returns self]
	 *   append to existing header or add new one
	 *  header_remove(string_header) [returns self]
	 *  response_content(content, bool_append=true) [returns self]
	 *   output string
	 *  get_response_content() [returns mixed]
	 *  get_response_stream() [returns http_input_stream instance]
	 *   get_response_content wrapped in http_input_steam object
	 *  status(int_status) [returns self]
	 *   set response code
	 *  get_status() [returns array(int_status_code, string_status_text)]
	 *  send_response() [returns bool]
	 *   optional, will be executed by destructor
	 *  send_redirect(string_url, bool_exit=true)
	 *   shortcut for static method
	 *  send_file(string_file_path, string_file_name, string_file_description, string_content_type) [returns bool]
	 *  send_json(content) [returns bool]
	 *   note: response_content must not be called
	 *
	 * Response code constants in http_response:
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
	 * Response middleware:
		// at the very beginning you need to initialize the library components
		$request=new http_request();
		$response=new http_response();

		if(session_status() === PHP_SESSION_ACTIVE)
			$session=new http_session();

		if($request->method() === 'POST')
			$files=new http_files();

		// then register the arguments (the order matters here)
		http_response
		::	middleware_arg('request', $request) // 1st arg
		::	middleware_arg('response', $response) // 2nd arg
		::	middleware_arg('session', null) // 3rd arg, reserve
		::	middleware_arg('files', null) // 4th arg, reserve
		::	middleware_arg('customarg', null); // 5th arg

		if(session_status() === PHP_SESSION_ACTIVE)
			http_response::middleware_arg('session', $session); // overwrite 3rd arg

		if($request->method() === 'POST')
			http_response::middleware_arg('files', $files); // overwrite 4th arg

		// this is what the middleware looks like before setting the HTTP headers
		http_response::middleware(function($request, $response, $session, $files, $myarg){
			// $myarg === null and is from middleware_arg('customarg', null)

			if(!$request->is_https())
			{
				// redirect to HTTPS

				$response->send_redirect(
					$request->uri()->set_scheme('https'),
					false
				);

				// stop sending response immediately
				return false;
			}

			// add HTTP header
			$response->header(
				'X-Custom-Header',
				'customvalue'
			);
		}, true);

		// and middleware just before sending the body
		http_response::middleware(function($request, $response, $session, $files, $myarg){
			if($response->get_response_content() === null) // the response body is empty - the skin hunters were here
				return false; // stop sending response immediately

			// do plastic surgery of the body
			$response->response_content(
				str_replace(
					'curse',
					'*****',
					$response->get_response_content()
				),
				false // overwrite; do not append
			);
		});
	 *
	 * Sources:
	 *  https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/Response.php
	 *  https://github.com/guzzle/psr7/blob/2.7/src/Stream.php
	 */

	class http_request_response_exception extends Exception {}
	class http_input_stream
	{
		protected $stream=null;

		public static function from_scalar($input)
		{
			$class=__CLASS__;

			if(!is_scalar($input))
				throw new http_request_response_exception(
					'$input is not scalar'
				);

			$http_input_stream=new $class(fopen(
				'php://temp',
				'r+'
			));

			$http_input_stream->write(
				(string)$input
			);

			$http_input_stream->rewind();

			return $http_input_stream;
		}

		public function __construct(
			$input='php://input',
			string $mode='r'
		){
			if(is_string($input))
				$this->stream=fopen(
					$input,
					$mode
				);
			else if(is_resource($input))
				$this->stream=$input;
			else
				throw new http_request_response_exception(
					'$input must be a string or a resource'
				);

			if($this->stream === false)
				throw new http_request_response_exception(
					'Cannot open stream'
				);
		}
		public function __toString()
		{
			if($this->is_seekable())
				$this->rewind();

			return $this->get_contents();
		}

		public function detach()
		{
			$stream=$this->stream;
			$this->stream=null;

			return $stream;
		}
		public function close()
		{
			if(is_resource(
				$this->stream
			))
				fclose(
					$this->stream
				);

			$this->stream=null;
		}
		public function eof()
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			return feof(
				$this->stream
			);
		}
		public function read(int $length)
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			if(!$this->is_readable())
				throw new http_request_response_exception(
					'Stream is unreadable'
				);

			if($length < 1)
				return '';

			$output=fread(
				$this->stream,
				$length
			);

			if($output === false)
				throw new http_request_response_exception(
					'Reading from stream failed'
				);

			return $output;
		}
		public function rewind()
		{
			$this->seek(0);
		}
		public function seek(
			int $offset,
			int $whence=SEEK_SET
		){
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			if(!$this->is_seekable())
				throw new http_request_response_exception(
					'Stream is unseekable'
				);

			if(fseek(
				$this->stream,
				$offset,
				$whence
			) === -1)
				throw new http_request_response_exception(
					'Stream seek failed'
				);
		}
		public function tell()
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			$tell=ftell(
				$this->stream
			);

			if($tell === false)
				throw new http_request_response_exception(
					'Stream tell failed'
				);

			return $tell;
		}
		public function write(string $string)
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			if(!$this->is_writable())
				throw new http_request_response_exception(
					'Stream is not writable'
				);

			$output=fwrite(
				$this->stream,
				$string
			);

			if($output === false)
				throw new http_request_response_exception(
					'Writing to stream failed'
				);

			return $output;
		}

		public function is_readable()
		{
			$mode=$this->get_metadata('mode');

			if($mode === null)
				throw new http_request_response_exception(
					'Failed to read "mode" metadata'
				);

			return (bool)preg_match(
				'/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/',
				$mode
			);
		}
		public function is_seekable()
		{
			$seekable=$this->get_metadata(
				'seekable'
			);

			if($seekable === null)
				throw new http_request_response_exception(
					'Failed to read "seekable" metadata'
				);

			return $seekable;
		}
		public function is_writable()
		{
			$mode=$this->get_metadata('mode');

			if($mode === null)
				throw new http_request_response_exception(
					'Failed to read "mode" metadata'
				);

			return (bool)preg_match(
				'/a|w|r\+|rb\+|rw|x|c/',
				$mode
			);
		}

		public function get_contents()
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			if(!$this->is_readable())
				throw new http_request_response_exception(
					'Stream is unreadable'
				);

			$output=stream_get_contents(
				$this->stream
			);

			if($output === false)
				throw new http_request_response_exception(
					'Could not read the stream content'
				);

			return $output;
		}
		public function get_metadata(?string $key=null)
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			$metadata=stream_get_meta_data(
				$this->stream
			);

			if($key === null)
				return $metadata;

			if(isset($metadata[
				$key
			]))
				return $metadata[
					$key
				];

			return null;
		}
		public function get_size()
		{
			if($this->stream === null)
				throw new http_request_response_exception(
					'Stream is detached'
				);

			$stats=fstat(
				$this->stream
			);

			if(isset($stats['size']))
				return $stats['size'];

			return null;
		}
	}
	class http_uri
	{
		protected $scheme='';
		protected $authority='';
		protected $user_info='';
		protected $host='';
		protected $port='';
		protected $path='';
		protected $query='';
		protected $query_array=null;
		protected $fragment='';

		public function __construct(string $uri)
		{
			$elements=parse_url($uri);

			if($elements === false)
				throw new http_request_response_exception(
					'The specified URI is invalid'
				);

			foreach([
				'scheme',
				'host',
				'port',
				'path',
				'query',
				'fragment'
			] as $element)
				if(isset(
					$elements[$element]
				))
					$this->$element=$elements[$element];

			if(isset($elements['user']))
			{
				$this->user_info=$elements['user'];

				if(isset($elements['pass']))
					$this->user_info.=':'.$elements['pass'];
			}
		}
		public function __toString()
		{
			return $this->compose(
				$this->scheme,
				$this->user_info,
				$this->host,
				$this->port,
				$this->path,
				$this->query,
				$this->fragment
			);
		}

		protected function compose_authority(
			$user_info,
			$host,
			$port
		){
			$authority=$host;

			if($user_info !== '')
				$authority=''
				.	$user_info
				.	'@'
				.	$authority;

			if($port !== '')
				$authority.=':'.$port;

			return $authority;
		}
		protected function compose(
			$scheme,
			$user_info,
			$host,
			$port,
			$path,
			$query,
			$fragment
		){
			$authority=$this->compose_authority(
				$user_info,
				$host,
				$port
			);

			if($scheme !== '')
				$scheme.=':';

			if(
				($authority !== '') ||
				($scheme !== 'file:')
			)
				$scheme.='//';

			if($path === '')
				$path='/'.$path;

			if($query !== '')
				$query='?'.$query;

			if($fragment !== '')
				$fragment='#'.$fragment;

			return ''
			.	$scheme
			.	$authority
			.	$path
			.	$query
			.	$fragment;
		}

		public function scheme()
		{
			return $this->scheme;
		}
		public function authority()
		{
			if($this->authority === '')
				$this->authority=$this->compose_authority(
					$this->user_info,
					$this->host,
					$this->port
				);

			return $this->authority;
		}
		public function user_info()
		{
			return $this->user_info;
		}
		public function host()
		{
			return $this->host;
		}
		public function port()
		{
			return $this->port;
		}
		public function path()
		{
			return $this->path;
		}
		public function query()
		{
			return $this->query;
		}
		public function query_array()
		{
			if($this->query_array === null)
				parse_str(
					$this->query,
					$this->query_array
				);

			return $this->query_array;
		}
		public function fragment()
		{
			return $this->fragment;
		}
		public function set_scheme(string $scheme)
		{
			return new static($this->compose(
				$scheme,
				$this->user_info,
				$this->host,
				$this->port,
				$this->path,
				$this->query,
				$this->fragment
			));
		}
		public function set_user_info(
			string $user,
			?string $password=null
		){
			if($password !== null)
				$user.=':'.$password;

			return new static($this->compose(
				$this->scheme,
				$user,
				$this->host,
				$this->port,
				$this->path,
				$this->query,
				$this->fragment
			));
		}
		public function set_host(string $host)
		{
			return new static($this->compose(
				$this->scheme,
				$this->user_info,
				$host,
				$this->port,
				$this->path,
				$this->query,
				$this->fragment
			));
		}
		public function set_port(int $port)
		{
			return new static($this->compose(
				$this->scheme,
				$this->user_info,
				$this->host,
				$port,
				$this->path,
				$this->query,
				$this->fragment
			));
		}
		public function set_path(string $path)
		{
			return new static($this->compose(
				$this->scheme,
				$this->user_info,
				$this->host,
				$this->port,
				$path,
				$this->query,
				$this->fragment
			));
		}
		public function set_query($query)
		{
			if(is_iterable($query))
				$query=http_build_query($query);
			else if(!is_string($query))
				throw new http_request_response_exception(
					'$query must be array or string'
				);

			return new static($this->compose(
				$this->scheme,
				$this->user_info,
				$this->host,
				$this->port,
				$this->path,
				$query,
				$this->fragment
			));
		}
		public function set_fragment(string $fragment)
		{
			return new static($this->compose(
				$this->scheme,
				$this->user_info,
				$this->host,
				$this->port,
				$this->path,
				$this->query,
				$fragment
			));
		}
	}
	class http_request
	{
		protected $cache_get=[];
		protected $cache_http_headers=[];
		protected $cache_parsed_http_headers=[];
		protected $cache_post=[];
		protected $cache_request_uri=null;

		protected function get_http_header(
			$header,
			$cache=true
		){
			if(isset($this->cache_http_headers[
				$header
			]))
				return $this->cache_http_headers[
					$header
				];

			$variable_name=''
			.	'HTTP_'
			.	strtr(strtoupper($header), '-', '_');

			if(isset($_SERVER[
				$variable_name
			])){
				if($cache)
					$this->cache_http_headers[
						$header
					]=$_SERVER[
						$variable_name
					];

				return $_SERVER[
					$variable_name
				];
			}

			if($cache)
				$this->cache_http_headers[
					$header
				]=false;

			return false;
		}
		protected function process_quality_http_header($header)
		{
			if(isset($this->cache_parsed_http_headers[
				$header
			]))
				return $this->cache_parsed_http_headers[
					$header
				];

			$params=$this->get_http_header(
				$header,
				false
			);

			if($params === false)
			{
				$this->cache_parsed_http_headers[
					$header
				]=false;

				return false;
			}

			$params_sorted=[];

			foreach(
				explode(',', $params)
				as $param
			){
				if(strpos($param, ';') === false)
				{
					$params_sorted[
						trim($param)
					]=1;

					continue;
				}

				$params_sorted[trim(substr(
					$param, 0, strpos($param, ';')
				))]=trim(substr(
					$param, strrpos($param, '=')+1
				));
			}

			krsort($params_sorted);
			$this->cache_parsed_http_headers[
				$header
			]=$params_sorted;

			return $params_sorted;
		}

		public function accept()
		{
			return $this->process_quality_http_header(
				'Accept'
			);
		}
		public function auth_user()
		{
			if(isset($_SERVER['PHP_AUTH_USER']))
				return $_SERVER['PHP_AUTH_USER'];

			return false;
		}
		public function auth_password()
		{
			if(isset($_SERVER['PHP_AUTH_PW']))
				return $_SERVER['PHP_AUTH_PW'];

			return false;
		}
		public function cache_control()
		{
			if(isset($this->cache_parsed_http_headers[
				'Cache-Control'
			]))
				return $this->cache_parsed_http_headers[
					'Cache-Control'
				];

			$params=$this->get_http_header(
				'Cache-Control',
				false
			);

			if($params === false)
			{
				$this->cache_parsed_http_headers[
					'Cache-Control'
				]=false;

				return false;
			}

			$params_sorted=[];

			foreach(
				explode(',', $params)
				as $param
			){
				$param_delimiter=strpos($param, '=');

				if($param_delimiter === false)
				{
					$params_sorted[
						trim($param)
					]=true;

					continue;
				}

				$params_sorted[
					trim(substr(
						$param,
						0,
						$param_delimiter
					))
				]=trim(substr(
					$param,
					$param_delimiter+1
				));
			}

			krsort($params_sorted);
			$this->cache_parsed_http_headers[
				'Cache-Control'
			]=$params_sorted;

			return $params_sorted;
		}
		public function content_length()
		{
			return $this->get_http_header(
				'Content-Length'
			);
		}
		public function content_type()
		{
			return $this->get_http_header(
				'Content-Type'
			);
		}
		public function cookie(
			string $key,
			$default_value=null
		){
			if(isset($_COOKIE[
				$key
			]))
				return $_COOKIE[
					$key
				];

			return $default_value;
		}
		public function charset()
		{
			return $this->process_quality_http_header(
				'Accept-Charset'
			);
		}
		public function date()
		{
			return $this->get_http_header(
				'Date'
			);
		}
		public function do_not_track()
		{
			if(isset($this->cache_parsed_http_headers[
				'DNT'
			]))
				return $this->cache_parsed_http_headers[
					'DNT'
				];

			if($this->get_http_header(
				'DNT',
				false
			) === false){
				$this->cache_parsed_http_headers[
					'DNT'
				]=false;

				return false;
			}

			$this->cache_parsed_http_headers[
				'DNT'
			]=true;

			return true;
		}
		public function encoding()
		{
			return $this->process_quality_http_header(
				'Accept-Encoding'
			);
		}
		public function get(
			string $key,
			$default_value=null
		){
			if(isset($this->cache_get[
				$key
			]))
				return $this->cache_get[
					$key
				];

			if(isset($_GET[
				$key
			])){
				$this->cache_get[
					$key
				]=filter_input(
					INPUT_GET,
					$key,
					FILTER_SANITIZE_SPECIAL_CHARS
				);

				return $this->cache_get[
					$key
				];
			}

			return $default_value;
		}
		public function http_host()
		{
			return $this->get_http_header(
				'Host'
			);
		}
		public function is_https()
		{
			return (
				isset($_SERVER['HTTPS']) &&
				($_SERVER['HTTPS'] === 'on')
			);
		}
		public function language()
		{
			return $this->process_quality_http_header(
				'Accept-Language'
			);
		}
		public function method()
		{
			if(!isset($_SERVER['REQUEST_METHOD']))
				return false;

			return $_SERVER['REQUEST_METHOD'];
		}
		public function post(
			string $key,
			$default_value=null
		){
			if(isset($this->cache_post[
				$key
			]))
				return $this->cache_post[
					$key
				];

			if(isset($_POST[
				$key
			])){
				$this->cache_post[
					$key
				]=filter_input(
					INPUT_POST,
					$key,
					FILTER_SANITIZE_SPECIAL_CHARS
				);

				return $this->cache_post[
					$key
				];
			}

			return $default_value;
		}
		public function pragma()
		{
			return $this->get_http_header(
				'Pragma'
			);
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
		public function request_uri(bool $with_get=false)
		{
			if(!isset($_SERVER['REQUEST_URI']))
				return false;

			if($with_get)
				return $_SERVER['REQUEST_URI'];

			if($this->cache_request_uri === null)
				$this->cache_request_uri=strtok(
					$_SERVER['REQUEST_URI'],
					'?'
				);

			return $this->cache_request_uri;
		}
		public function user_agent()
		{
			return $this->get_http_header(
				'User-Agent'
			);
		}
		public function upgrade_insecure_request()
		{
			if($this->get_http_header(
				'Upgrade-Insecure-Request'
			) === false)
				return false;

			return true;
		}
		public function uri()
		{
			$protocol='http';
			$auth='';

			if($this->is_https())
				$protocol='https';

			if($this->auth_user() !== false)
			{
				$auth=$this->auth_user();

				if($this->auth_password() !== false)
					$auth.=':'.$this->auth_password();

				$auth.='@';
			}

			return new http_uri(''
			.	$protocol.'://'
			.	$auth
			.	$this->http_host()
			.	$this->request_uri(true)
			);
		}

		public function input_stream()
		{
			return new http_input_stream();
		}
		public function json()
		{
			if($this->content_type() !== 'application/json')
				return false;

			$content=file_get_contents(
				'php://input'
			);

			if(!is_string($content))
				return false;

			return json_decode(trim(
				$content
			));
		}
	}
	class http_session
	{
		protected $subkey;

		public function __construct(?string $subkey=null)
		{
			if(session_status() !== PHP_SESSION_ACTIVE)
				throw new http_request_response_exception(
					'Session is not started'
				);

			$this->subkey=$subkey;
		}
		public function __call($key, $value)
		{
			switch(substr($key, 0, 4))
			{
				case 'get_':
					$key=substr($key, 4);

					if($this->subkey === null)
					{
						if(isset($_SESSION[
							$key
						]))
							return $_SESSION[
								$key
							];
					}
					else if(isset($_SESSION[
						$this->subkey
					][
						$key
					]))
						return $_SESSION[
							$this->subkey
						][
							$key
						];

					if(isset($value[0]))
						return $value[0];

					return null;
				case 'set_':
					$key=substr($key, 4);

					if($this->subkey === null)
					{
						if(isset($value[0]))
						{
							$_SESSION[
								$key
							]=$value[0];

							return $this;
						}

						unset($_SESSION[
							$key
						]);

						return $this;
					}

					if(isset($value[0]))
					{
						$_SESSION[
							$this->subkey
						][
							$key
						]=$value[0];

						return $this;
					}

					unset($_SESSION[
						$this->subkey
					][
						$key
					]);

					return $this;
				default:
					throw new http_request_response_exception(
						'No get_ or set_ prefix'
					);
			}
		}
	}
	class http_files
	{
		protected $destination=null;
		protected $max_file_size=null;
		protected $allowed_mimes=[];
		protected $moved_files=[];

		public function __construct(array $params=[])
		{
			if($_SERVER['REQUEST_METHOD'] !== 'POST')
				throw new http_request_response_exception(
					'Only POST method is supported'
				);

			foreach([
				'destination',
				'max_file_size',
				'allowed_mimes'
			] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];
		}

		public function list_uploaded_files()
		{
			$result=[];

			foreach($_FILES as $file_name=>$file_array)
				if($file_array['error'] === UPLOAD_ERR_OK)
					$result[]=$file_name;

			return $result;
		}
		public function list_moved_files()
		{
			return $this->moved_files;
		}
		public function move_uploaded_file(
			string $file,
			?string $destination=null,
			?string $file_name=null
		){
			if(!isset($_FILES[$file]))
				throw new http_request_response_exception(
					$file.' does not exist'
				);

			if($_FILES[$file]['error'] !== UPLOAD_ERR_OK)
				throw new http_request_response_exception(
					$file.' was not uploaded correctly'
				);

			$file_size=filesize(
				$_FILES[$file]['tmp_name']
			);

			if($file_size === 0)
			{
				unlink($_FILES[$file]['tmp_name']);
				unset($_FILES[$file]);

				throw new http_request_response_exception(
					$file.' is empty'
				);
			}

			if(
				($this->max_file_size !== null) &&
				($file_size > $this->max_file_size)
			){
				unlink($_FILES[$file]['tmp_name']);
				unset($_FILES[$file]);

				throw new http_request_response_exception(
					$file.' has exceeded max size'
				);
			}

			if(!empty(
				$this->allowed_mimes
			)){
				$file_mime=mime_content_type(
					$_FILES[$file]['tmp_name']
				);

				if(!in_array(
					$file_mime,
					$this->allowed_mimes
				)){
					unlink($_FILES[$file]['tmp_name']);
					unset($_FILES[$file]);

					throw new http_request_response_exception(
						$file.' is '.$file_mime.' which is not allowed'
					);
				}
			}

			if($destination === null)
			{
				if($this->destination === null)
					throw new http_request_response_exception(
						'The destination is not defined either globally or locally'
					);

				$destination=$this->destination;
			}

			if($file_name === null)
				$file_name=preg_replace(
					'([^\w\s\d\-_~,;\[\]\(\).])',
					'',
					basename(
						$_FILES[$file]['name']
					)
				);

			if(file_exists(''
			.	$destination
			.	$file_name
			))
				throw new http_request_response_exception(
					$destination.$file_name.' already exists'
				);

			$result=copy(
				$_FILES[$file]['tmp_name'],
				$destination.$file_name
			);

			if($result)
			{
				unlink(
					$_FILES[$file]['tmp_name']
				);

				$this->moved_files[$file]=[
					'name'=>$_FILES[$file]['name'],
					'tmp_name'=>$_FILES[$file]['tmp_name'],
					'file_name'=>$file_name,
					'destination'=>$destination,
					'moved_file'=>realpath(
						$destination.$file_name
					)
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
		protected static $middleware=[
			'before'=>[],
			'after'=>[],
			'arguments'=>[]
		];

		protected $http_headers=[
			'Content-Type'=>'text/html'
		];
		protected $http_status=200;
		protected $http_content_charset=null;
		protected $http_cookies=[];
		protected $response_content=null;

		public static function middleware(
			callable $callback,
			bool $before=false
		){
			if($before)
			{
				static::$middleware['before'][]=$callback;
				return static::class;
			}

			static::$middleware['after'][]=$callback;

			return static::class;
		}
		public static function middleware_arg(
			string $arg,
			$value
		){
			static::$middleware['arguments'][
				$arg
			]=$value;

			return static::class;
		}
		public static function redirect(
			string $url,
			bool $exit=true
		){
			if(headers_sent())
				throw new http_request_response_exception(
					'HTTP headers already sent'
				);

			http_response_code(301);
			header('Location: '.$url);

			if($exit)
				exit();
		}

		public function __construct()
		{
			if(headers_sent())
				throw new http_request_response_exception(
					'HTTP headers already sent'
				);

			foreach(headers_list() as $header)
			{
				$header=explode(':', $header);

				if(!isset($header[1]))
					$header[1]='';

				$this->header(
					trim($header[0]),
					trim($header[1])
				);
			}
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
			$this->http_cookies[$name]=[
				$value,
				$expires,
				$path,
				$domain,
				$secure,
				$httponly
			];

			return $this;
		}
		public function get_cookie(?string $name=null)
		{
			if($name === null)
				return $this->http_cookies;

			if(isset($this->http_cookies[
				$name
			]))
				return $this->http_cookies[
					$name
				];

			return false;
		}
		public function cookie_remove(string $name)
		{
			if(isset($this->http_cookies[
				$name
			]))
				unset($this->http_cookies[
					$name
				]);

			return $this;
		}
		public function cookie_expire(
			string $name,
			string $path='',
			string $domain='',
			bool $secure=false,
			bool $httponly=false
		){
			return $this->cookie(
				$name,
				'',
				-1,
				$path,
				$domain,
				$secure,
				$httponly
			);
		}
		public function etag(string $etag)
		{
			$this->http_headers['Pragma']='cache';
			$this->http_headers['ETag']='"'.$etag.'"';

			return $this;
		}
		public function expire(
			int $expire,
			bool $must_revalidate=true
		){
			$this->http_headers['Pragma']='cache';
			$this->http_headers['Cache-Control']='max-age='.$expire;
			$this->http_headers['Expires']=gmdate(
				'D, d M Y H:i:s',
				time()+$expire
			).' GMT';

			if($must_revalidate)
				$this->http_headers['Cache-Control'].=', must-revalidate';

			return $this;
		}
		public function no_cache()
		{
			$this->http_headers['Cache-Control']='no-cache';
			$this->http_headers['Expires']='-1';
			$this->http_headers['Pragma']='no-cache';

			unset(
				$this->http_headers['ETag']
			);

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
		public function has_header(string $header)
		{
			return isset($this->http_headers[
				$header
			]);
		}
		public function get_header(?string $header=null)
		{
			if($header === null)
				return $this->http_headers;

			if($this->has_header($header))
				return $this->http_headers[
					$header
				];

			return false;
		}
		public function header(
			string $header,
			string $value
		){
			$this->http_headers[
				$header
			]=$value;

			return $this;
		}
		public function header_append(
			string $header,
			string $value,
			string $delimiter=','
		){
			if(strlen($delimiter) > 1)
				throw new http_request_response_exception(
					'$delimiter must be one character long'
				);

			if(!$this->has_header($header))
				return $this->header(
					$header,
					$value
				);

			$this->http_headers[
				$header
			].=$delimiter.$value;

			return $this;
		}
		public function header_remove(string $header)
		{
			if($this->has_header($header))
				unset($this->http_headers[
					$header
				]);

			return $this;
		}
		public function response_content(
			$content,
			bool $append=true
		){
			if(
				(!$append) ||
				($this->response_content === null)
			){
				$this->response_content=$content;
				return $this;
			}

			$this->response_content.=$content;

			return $this;
		}
		public function get_response_content()
		{
			return $this->response_content;
		}
		public function get_response_stream()
		{
			if($this->response_content === null)
				return http_input_stream::from_scalar(
					''
				);

			return http_input_stream::from_scalar(
				$this->response_content
			);
		}
		public function status(int $status)
		{
			$this->http_status=$status;
			return $this;
		}
		public function get_status()
		{
			$status_text=null;
			$status_texts=[
				100=>'Continue',
				101=>'Switching Protocols',
				102=>'Processing',
				103=>'Early Hints',
				200=>'OK',
				201=>'Created',
				202=>'Accepted',
				203=>'Non-Authoritative Information',
				204=>'No Content',
				205=>'Reset Content',
				206=>'Partial Content',
				207=>'Multi-Status',
				208=>'Already Reported',
				226=>'IM Used',
				300=>'Multiple Choices',
				301=>'Moved Permanently',
				302=>'Found',
				303=>'See Other',
				304=>'Not Modified',
				305=>'Use Proxy',
				307=>'Temporary Redirect',
				308=>'Permanent Redirect',
				400=>'Bad Request',
				401=>'Unauthorized',
				402=>'Payment Required',
				403=>'Forbidden',
				404=>'Not Found',
				405=>'Method Not Allowed',
				406=>'Not Acceptable',
				407=>'Proxy Authentication Required',
				408=>'Request Timeout',
				409=>'Conflict',
				410=>'Gone',
				411=>'Length Required',
				412=>'Precondition Failed',
				413=>'Content Too Large',
				414=>'URI Too Long',
				415=>'Unsupported Media Type',
				416=>'Range Not Satisfiable',
				417=>'Expectation Failed',
				418=>'I\'m a teapot',
				421=>'Misdirected Request',
				422=>'Unprocessable Content',
				423=>'Locked',
				424=>'Failed Dependency',
				425=>'Too Early',
				426=>'Upgrade Required',
				428=>'Precondition Required',
				429=>'Too Many Requests',
				431=>'Request Header Fields Too Large',
				451=>'Unavailable For Legal Reasons',
				500=>'Internal Server Error',
				501=>'Not Implemented',
				502=>'Bad Gateway',
				503=>'Service Unavailable',
				504=>'Gateway Timeout',
				505=>'HTTP Version Not Supported',
				506=>'Variant Also Negotiates',
				507=>'Insufficient Storage',
				508=>'Loop Detected',
				510=>'Not Extended',
				511=>'Network Authentication Required'
			];

			if(isset($status_texts[
				$this->http_status
			]))
				$status_text=$status_texts[
					$this->http_status
				];

			return [
				$this->http_status,
				$status_text
			];
		}

		public function send_response()
		{
			if(static::$sent === true)
				return false;

			if(headers_sent())
				throw new http_request_response_exception(
					'HTTP headers already sent'
				);

			foreach(
				static::$middleware['before']
				as $middleware
			)
				if($middleware(...array_values(
					static::$middleware['arguments']
				)) === false)
					return true;

			if($this->http_content_charset !== null)
				$this->http_headers['Content-Type'].=''
				.	';charset='
				.	$this->http_content_charset;

			if(isset($_SERVER[
				'HTTP_TRANSFER_ENCODING'
			]))
				unset(
					$this->http_headers['Content-Length']
				);

			http_response_code(
				$this->http_status
			);

			foreach(
				$this->http_headers
				as $header_name=>$header_content
			)
				header(''
				.	$header_name.': '
				.	$header_content
				);

			foreach(
				$this->http_cookies
				as $cookie_name=>$cookie_params
			){
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

			foreach(
				static::$middleware['after']
				as $middleware
			)
				if($middleware(...array_values(
					static::$middleware['arguments']
				)) === false)
					return true;

			if($this->response_content !== null)
				echo $this->response_content;

			static::$sent=true;

			return true;
		}
		public function send_redirect(
			string $url,
			bool $exit=true
		){
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
			->	content_type($content_type)
			->	header(
					'Content-Length',
					filesize($file_path)
				)
			->	header(
					'Content-Disposition',
					'attachment'
				)
			->	header_append(
					'Content-Disposition',
					'filename='.$file_name,
					';'
				)
			->	header(
					'Content-Description',
					$file_description
				);

			if(!$this->send_response())
				return false;

			if(readfile($path) === false)
				return false;

			return true;
		}
		public function send_json($content)
		{
			if($this->response_content !== null)
				throw new http_request_response_exception(
					'response_content method was called before'
				);

			return $this
			->	content_type(
					'application/json'
				)
			->	response_content(json_encode(
					$content,
					JSON_UNESCAPED_UNICODE
				))
			->	send_response();
		}
	}
?>