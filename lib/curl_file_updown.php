<?php
	/*
	 * curl wrappers for quick file download and upload
	 *
	 * Functions:
	 *  curl_file_upload
	 *  curl_file_download
	 *  curl_json_upload
	 */

	class curl_file_exception extends Exception {}

	if(function_exists('curl_init'))
	{
		function curl_file_upload(
			string $url,
			string $source,
			array $params=[],
			array $curl_opts=[]
		){
			/*
			 * Quickly upload file
			 * Supported protocols: FTP FTPS HTTP HTTPS SCP SFTP
			 *
			 * Note:
			 *  throws an curl_file_exception on error
			 *
			 * Error handling: add to parameters (3rd arg array)
				'on_error'=>function($error)
				{
					my_log_function(__FILE__.' curl_file_upload: '.$error);
				}
			 *
			 * Usage:
				curl_file_upload(
					'http://127.0.0.1:8080/upload.php',
					'./image.png',
					[
						'post_field_name'=>'fileToUpload' // required
						//,'credentials'=>'username:password' // http login prompt
					]
					//,[CURLOPT_VERBOSE=>true]
				)
				curl_file_upload(
					'ftp://127.0.0.1:2121',
					'./image.png'
					//,['credentials'=>'username:password']
					//,[CURLOPT_VERBOSE=>true]
				)
				curl_file_upload(
					'sftp://127.0.0.1:22/tmp',
					'./image.png',
					[
						'credentials'=>'username:password'
						//,'private_key'=>'path/to/private_key'
						//,'public_key'=>'path/to/public_key'
					]
					//,[CURLOPT_VERBOSE=>true]
				)
			 */

			if(!file_exists($source))
				return false;

			foreach([
				CURLOPT_TIMEOUT=>20,
				CURLOPT_SSL_VERIFYPEER=>true,
				CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1_2,
				CURLOPT_FAILONERROR=>true,
				CURLOPT_RETURNTRANSFER=>true
			] as $curl_opt_name=>$curl_opt_value)
				if(!isset($curl_opts[$curl_opt_name]))
					$curl_opts[$curl_opt_name]=$curl_opt_value;

			if(isset($params['credentials']))
				$curl_opts[CURLOPT_USERPWD]=$params['credentials'];

			$curl_opts[CURLOPT_URL]=$url;

			switch(strtok($url, ':'))
			{
				case 'ftp':
				case 'ftps':
					$file_handle=fopen($source, 'r');

					$curl_opts[CURLOPT_URL].='/'.basename($source);
					$curl_opts[CURLOPT_UPLOAD]=true;
					$curl_opts[CURLOPT_INFILE]=$file_handle;
					$curl_opts[CURLOPT_INFILESIZE]=filesize($source);
				break;
				case 'http':
				case 'https':
					if((!isset($params['post_field_name'])) && isset($params['on_error']))
						$params['on_error']('curl_file_updown.php: you requested file_upload() via http but not defined the post_field_name parameter');

					if(!isset($curl_opts[CURLOPT_TCP_FASTOPEN]))
						$curl_opts[CURLOPT_TCP_FASTOPEN]=true; // conflict with ftp and sftp

					$curl_opts[CURLOPT_HTTPAUTH]=CURLAUTH_BASIC;
					$curl_opts[CURLOPT_POST]=true;
					$curl_opts[CURLOPT_POSTFIELDS]=[
						$params['post_field_name']=>curl_file_create($source)
					];
					$curl_opts[CURLOPT_HEADER]=true;
					$curl_opts[CURLOPT_HTTPHEADER]=['Content-Type: multipart/form-data'];
				break;
				case 'scp':
				case 'sftp':
					if(isset($params['private_key']))
						$curl_opts[CURLOPT_SSH_PRIVATE_KEYFILE]=$params['private_key'];

					if(isset($params['public_key']))
						$curl_opts[CURLOPT_SSH_PUBLIC_KEYFILE]=$params['public_key'];

					$file_handle=fopen($source, 'r');

					$curl_opts[CURLOPT_URL].='/'.basename($source);
					$curl_opts[CURLOPT_UPLOAD]=true;
					$curl_opts[CURLOPT_INFILE]=$file_handle;
					$curl_opts[CURLOPT_INFILESIZE]=filesize($source);
			}

			$curl_handle=curl_init();

			foreach($curl_opts as $option=>$value)
				curl_setopt($curl_handle, $option, $value);

			$response=curl_exec($curl_handle);

			if(
				isset($params['on_error']) &&
				curl_errno($curl_handle)
			)
				$params['on_error'](
					curl_error($curl_handle)
				);

			curl_close($curl_handle);

			return $response;
		}
		function curl_file_download(
			string $url,
			?string $destination=null,
			array $params=[],
			array $curl_opts=[]
		){
			/*
			 * Quickly download file and print it or save
			 *
			 * Note:
			 *  throws an curl_file_exception on error
			 *
			 * Error handling: add to parameters (3rd arg array)
				'on_error'=>function($error)
				{
					my_log_function(__FILE__.' curl_file_download: '.$error);
				}
			 *
			 * Usage:
				curl_file_download(
					'https://example.com/file.png'
					//,'./downloaded-file.png'
					//,['credentials'=>'username:password']
					//,[CURLOPT_VERBOSE=>true]
				)
				curl_file_download(
					'sftp://127.0.0.1:22/path/to/file.png',
					null,
					[
						'credentials'=>'username:password'
						//,'private_key'=>'path/to/private_key'
						//,'public_key'=>'path/to/public_key'
					]
					//,[CURLOPT_VERBOSE=>true]
				)
			 * where output file is not specified, this function returns content.
			 */

			foreach([
				CURLOPT_TIMEOUT=>10,
				CURLOPT_SSL_VERIFYPEER=>true,
				CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1_2,
				CURLOPT_FAILONERROR=>true,
				CURLOPT_RETURNTRANSFER=>true
			] as $curl_opt_name=>$curl_opt_value)
				if(!isset($curl_opts[$curl_opt_name]))
					$curl_opts[$curl_opt_name]=$curl_opt_value;

			if(isset($params['credentials']))
				$curl_opts[CURLOPT_USERPWD]=$params['credentials'];

			$curl_opts[CURLOPT_URL]=$url;

			switch(strtok($url, ':'))
			{
				case 'http':
				case 'https':
					if(!isset($curl_opts[CURLOPT_TCP_FASTOPEN]))
						$curl_opts[CURLOPT_TCP_FASTOPEN]=true; // conflict with ftp and sftp

					$curl_opts[CURLOPT_HTTPAUTH]=CURLAUTH_BASIC;
				break;
				case 'scp':
				case 'sftp':
					if(isset($params['private_key']))
						$curl_opts[CURLOPT_SSH_PRIVATE_KEYFILE]=$params['private_key'];

					if(isset($params['public_key']))
						$curl_opts[CURLOPT_SSH_PUBLIC_KEYFILE]=$params['public_key'];
			}

			if($destination !== null)
			{
				$file_handle=fopen($destination, 'w');
				$curl_opts[CURLOPT_FILE]=$file_handle;
			}

			$curl_handle=curl_init();

			foreach($curl_opts as $option=>$value)
				curl_setopt($curl_handle, $option, $value);

			if($destination === null)
				$response=curl_exec($curl_handle);
			else
				curl_exec($curl_handle);

			if(
				isset($params['on_error']) &&
				curl_errno($curl_handle)
			)
				$params['on_error'](
					curl_error($curl_handle)
				);

			curl_close($curl_handle);

			if($destination === null)
				return $response;

			fclose($file_handle);

			if(file_exists($destination))
				return true;

			return false;
		}
		function curl_json_upload(
			string $url,
			string $content='',
			string $method='GET',
			array $params=[],
			array $curl_opts=[]
		){
			/*
			 * Quickly send raw JSON/XML/ETC
			 * Supported HTTP methods: GET POST PUT DELETE PATCH HEAD OPTIONS TRACE CONNECT
			 *
			 * Note:
			 *  returns array('http_reponse_headers', 'http_response_content')
			 *  throws an curl_file_exception on error
			 *
			 * Error handling: add to parameters (3rd arg array)
				'on_error'=>function($error)
				{
					my_log_function(__FILE__.' curl_file_upload: '.$error);
				}
			 *
			 * Usage:
				// get data from api
				curl_json_upload(
					'https://example.com/api'
				)
				curl_json_upload(
					'https://example.com/api', '', 'GET'
					//,['credentials'=>'username:password']
					//,[CURLOPT_VERBOSE=>true]
				)
				// send POST JSON
				curl_json_upload(
					'https://example.com/api',
					json_encode(['argument'=>'value'], JSON_UNESCAPED_UNICODE),
					'POST'
					//,['credentials'=>'username:password']
					//,[CURLOPT_VERBOSE=>true]
				)
			 */

			switch($method)
			{
				case 'POST':
					$curl_opts[CURLOPT_POST]=true;
				break;
				case 'GET':
				case 'PUT':
				case 'DELETE':
				case 'PATCH':
				case 'HEAD':
				case 'OPTIONS':
				case 'TRACE':
				case 'CONNECT':
					$curl_opts[CURLOPT_CUSTOMREQUEST]=$method;
				break;
				default:
					throw new curl_file_exception('Invalid HTTP method specified');
			}

			foreach([
				CURLOPT_TIMEOUT=>20,
				CURLOPT_SSL_VERIFYPEER=>true,
				CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1_2,
				CURLOPT_FAILONERROR=>true,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_TCP_FASTOPEN=>true,
				CURLOPT_HTTPAUTH=>CURLAUTH_BASIC
			] as $curl_opt_name=>$curl_opt_value)
				if(!isset($curl_opts[$curl_opt_name]))
					$curl_opts[$curl_opt_name]=$curl_opt_value;

			if(isset($params['credentials']))
				$curl_opts[CURLOPT_USERPWD]=$params['credentials'];

			$curl_opts[CURLOPT_URL]=$url;

			if($content !== '')
				foreach([
					CURLOPT_POSTFIELDS=>$content,
					CURLOPT_HEADER=>true,
					CURLOPT_HTTPHEADER=>['Content-Type: application/json']
				] as $curl_opt_name=>$curl_opt_value)
					if(!isset($curl_opts[$curl_opt_name]))
						$curl_opts[$curl_opt_name]=$curl_opt_value;

			$curl_handle=curl_init();

			foreach($curl_opts as $option=>$value)
				curl_setopt($curl_handle, $option, $value);

			$response=curl_exec($curl_handle);

			if(
				isset($params['on_error']) &&
				curl_errno($curl_handle)
			)
				$params['on_error'](
					curl_error($curl_handle)
				);

			$header_size=curl_getinfo($curl_handle, CURLINFO_HEADER_SIZE);

			curl_close($curl_handle);

			return [
				substr($response, 0, $header_size),
				substr($response, $header_size)
			];
		}
	}
	else
	{
		function curl_file_upload()
		{
			throw new curl_file_exception(
				'curl extension is not loaded'
			);
		}
		function curl_file_download()
		{
			throw new curl_file_exception(
				'curl extension is not loaded'
			);
		}
		function curl_json_upload()
		{
			throw new curl_file_exception(
				'curl extension is not loaded'
			);
		}
	}
?>