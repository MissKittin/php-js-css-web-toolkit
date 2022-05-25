<?php
	class file_http_request
	{
		/*
		 * file_get_contents() wrapper for http streams
		 *
		 * Note:
		 *  if you can use curl, use curl
		 *  allow_url_fopen must be enabled
		 *  default request method is GET
		 *  get methods require a send method to be run before use
		 *
		 * Methods:
		 *  __construct([
		 *   'url'=>string,
		 *   'method'=>string,
		 *   'request_content'=>string
		 *  ])
		 *   note: input array is optional
		 *  set_url(string_url) [returns self]
		 *  set_method(string_method) [returns self]
		 *  set_header(string_header, string_value) [returns self]
		 *  set_content(string_content, bool_append=false) [returns self]
		 *  send() [returns bool]
		 *  get_response_headers()
		 *   [returns array(
		 *    'Header-Name'=>array(
		 *     int_number=>string_header_value
		 *    )
		 *   )]
		 *  get_response_protocol() [returns string]
		 *  get_response_status() [returns string]
		 *  get_response_cookie(string_name, string_default_value=null)
		 *   returns array with 'value' and other parameters
		 *  get_response_content() [returns string]
		 */

		protected $url=null;
		protected $request_method='GET';
		protected $request_headers=[];
		protected $request_content=null;
		protected $request_context;
		protected $response_protocol;
		protected $response_status;
		protected $response_headers;
		protected $parsed_response_headers;
		protected $response_content;
		protected $response_cookies;

		public function __construct(array $params=[])
		{
			foreach(['url', 'method', 'request_content'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->reset_fields();
		}

		protected function reset_fields($reset_context=true)
		{
			if($reset_context)
				$this->request_context=null;

			$this->response_protocol=null;
			$this->response_status=null;
			$this->response_headers=null;
			$this->parsed_response_headers=null;
			$this->response_content=null;
			$this->response_cookies=[];
		}

		public function set_url(string $url)
		{
			$this->reset_fields(false);

			$this->url=$url;
			return $this;
		}
		public function set_method(string $method)
		{
			$this->reset_fields();

			$this->request_method=$method;
			return $this;
		}
		public function set_header(string $header, string $value)
		{
			$this->reset_fields();

			$this->request_headers[$header]=$value;
			return $this;
		}
		public function set_content(string $content, bool $append=false)
		{
			$this->reset_fields();

			if($append && ($this->request_content !== null))
				$this->request_content.=$content;
			else
				$this->request_content=$content;

			return $this;
		}

		public function send()
		{
			if($this->url === null)
				throw new Exception('URL is not defined');

			if($this->request_context === null)
			{
				$request_context=[
					'method'=>$this->request_method,
					'ignore_errors'=>true
				];

				if(!empty($this->request_headers))
				{
					$request_headers=[];
					foreach($this->request_headers as $header_name=>$header_value)
						$request_headers[]=$header_name.': '.$header_value;

					$request_context['header']=implode("\r\n", $request_headers);
				}

				if($this->request_content !== null)
					$request_context['content']=$this->request_content;

				$this->request_context=stream_context_create(['http'=>$request_context]);
			}
			else
				$this->reset_fields(false);

			$response=file_get_contents($this->url, false, $this->request_context);
			if($response === false)
			{
				$this->reset_fields();
				return false;
			}

			$this->response_headers=$http_response_header;
			$this->response_content=$response;

			return true;
		}

		public function get_response_headers()
		{
			if($this->request_context === null)
				throw new Exception('The request was not sent or failed');

			if($this->parsed_response_headers !== null)
				return $this->parsed_response_headers;

			$i=0;
			foreach($this->response_headers as $response_header)
			{
				$strpos=strpos($response_header, ':');
				if($strpos === false)
				{
					$this->parsed_response_headers=[];

					$response_status=explode(' ', $this->response_headers[$i]);
					$this->response_protocol=$response_status[0];
					$this->response_status=$response_status[1];

					unset($this->response_headers[$i]);
				}
				else
					$this->parsed_response_headers[trim(substr($response_header, 0, $strpos))][]=trim(substr($response_header, $strpos+1));

				++$i;
			}

			$this->response_headers=null;

			return $this->parsed_response_headers;
		}
		public function get_response_protocol()
		{
			if($this->request_context === null)
				throw new Exception('The request was not sent or failed');

			$this->get_response_headers();

			return $this->response_protocol;
		}
		public function get_response_status()
		{
			if($this->request_context === null)
				throw new Exception('The request was not sent or failed');

			$this->get_response_headers();

			return $this->response_status;
		}
		public function get_response_cookie(string $name, string $default_value=null)
		{
			if($this->request_context === null)
				throw new Exception('The request was not sent or failed');

			if(isset($this->response_cookies[$name]))
			{
				if($this->response_cookies[$name] === false)
					return $default_value;

				return $this->response_cookies[$name];
			}

			$this->get_response_headers();

			if(isset($this->parsed_response_headers['Set-Cookie']))
				foreach($this->parsed_response_headers['Set-Cookie'] as $cookie)
				{
					$cookie=explode(';', $cookie);

					$strpos=strpos($cookie[0], '=');
					$cookie_name=trim(substr($cookie[0], 0, $strpos));
					$this->response_cookies[$cookie_name]['value']=substr($cookie[0], $strpos+1);
					unset($cookie[0]);

					foreach($cookie as $cookie_param)
					{
						$strpos=strpos($cookie_param, '=');
						if($strpos === false)
							$this->response_cookies[$cookie_name][trim($cookie_param)]=true;
						else
							$this->response_cookies[$cookie_name][trim(substr($cookie_param, 0, $strpos))]=trim(substr($cookie_param, $strpos+1));
					}
				}

			if(isset($this->response_cookies[$name]))
				return $this->response_cookies[$name];
			else
				$this->response_cookies[$name]=false;

			return $default_value;
		}
		public function get_response_content()
		{
			if($this->request_context === null)
				throw new Exception('The request was not sent or failed');

			return $this->response_content;
		}
	}
?>