<?php
	/*
	 * curl wrappers for quick file download and upload
	 *
	 * Functions:
	 *  curl_file_upload
	 *  curl_file_download
	 */

	class curl_file_exception extends Exception {}

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
		 *  'on_error'=>function($error){ error_log(__FILE__.' curl_file_upload: '.$error); }
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

		if(!extension_loaded('curl'))
			throw new curl_file_exception('curl extension is not loaded');

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
				$file_handler=fopen($source, 'r');

				$curl_opts[CURLOPT_URL].='/'.basename($source);
				$curl_opts[CURLOPT_UPLOAD]=true;
				$curl_opts[CURLOPT_INFILE]=$file_handler;
				$curl_opts[CURLOPT_INFILESIZE]=filesize($source);
			break;
			case 'http':
			case 'https':
				if((!isset($params['post_field_name'])) && isset($params['on_error']))
					$params['on_error']('curl_file_updown.php: you requested file_upload() via http but not define the post_field_name parameter');

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

				$file_handler=fopen($source, 'r');

				$curl_opts[CURLOPT_URL].='/'.basename($source);
				$curl_opts[CURLOPT_UPLOAD]=true;
				$curl_opts[CURLOPT_INFILE]=$file_handler;
				$curl_opts[CURLOPT_INFILESIZE]=filesize($source);
		}

		$curl_handler=curl_init();

		foreach($curl_opts as $option=>$value)
			curl_setopt($curl_handler, $option, $value);

		$output=curl_exec($curl_handler);

		if(isset($params['on_error']) && curl_errno($curl_handler))
			$params['on_error'](curl_error($curl_handler));

		curl_close($curl_handler);

		return $output;
	}
	function curl_file_download(
		string $url,
		string $destination=null,
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
		 *  'on_error'=>function($error){ error_log(__FILE__.' curl_file_download: '.$error); }
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

		if(!extension_loaded('curl'))
			throw new curl_file_exception('curl extension is not loaded');

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
			$file_handler=fopen($destination, 'w');
			$curl_opts[CURLOPT_FILE]=$file_handler;
		}

		$curl_handler=curl_init();

		foreach($curl_opts as $option=>$value)
			curl_setopt($curl_handler, $option, $value);

		if($destination === null)
			$output=curl_exec($curl_handler);
		else
			curl_exec($curl_handler);

		if(isset($params['on_error']) && curl_errno($curl_handler))
			$params['on_error'](curl_error($curl_handler));

		curl_close($curl_handler);

		if($destination === null)
			return $output;
		else
		{
			fclose($file_handler);

			if(file_exists($destination))
				return true;

			return false;
		}
	}
?>