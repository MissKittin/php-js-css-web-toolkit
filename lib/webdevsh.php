<?php
	/*
	 * PHP client for Toptal minifiers
	 *
	 * Warning:
	 *  curl extension is required
	 *
	 * Note:
	 *  throws an webdevsh_exception on an empty response from the server
	 *
	 * Usage:
	 *  webdevsh_css_minifier(file_get_contents('./style.css')) [returns string]
	 *  webdevsh_js_minifier(file_get_contents('./script.js')) [returns string]
	 */

	class webdevsh_exception extends Exception {}

	if(extension_loaded('curl'))
	{
		function webdevsh_css_minifier(string $input, bool $ignore_https=false)
		{
			$curl_handler=curl_init();

			foreach([
				CURLOPT_URL=>'https://www.toptal.com/developers/cssminifier/api/raw',
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_POST=>true,
				CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
				CURLOPT_POSTFIELDS=>http_build_query(['input'=>$input])
			] as $option=>$value)
				curl_setopt($curl_handler, $option, $value);

			if($ignore_https)
			{
				curl_setopt($curl_handler, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, 0);
			}

			$output=curl_exec($curl_handler);
			$error=curl_error($curl_handler);

			curl_close($curl_handler);

			if($output === false)
				throw new webdevsh_exception('Server response is empty ('.$error.')');

			return $output;
		}
		function webdevsh_js_minifier(string $input, bool $ignore_https=false)
		{
			$curl_handler=curl_init();

			foreach([
				CURLOPT_URL=>'https://www.toptal.com/developers/javascript-minifier/api/raw',
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_POST=>true,
				CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
				CURLOPT_POSTFIELDS=>http_build_query(['input'=>$input])
			] as $option=>$value)
				curl_setopt($curl_handler, $option, $value);

			if($ignore_https)
			{
				curl_setopt($curl_handler, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, 0);
			}

			$output=curl_exec($curl_handler);
			$error=curl_error($curl_handler);

			curl_close($curl_handler);

			if($output === false)
				throw new webdevsh_exception('Server response is empty ('.$error.')');

			return $output;
		}
	}
	else
	{
		function webdevsh_css_minifier()
		{
			throw new webdevsh_exception('curl extension is not loaded');
		}
		function webdevsh_js_minifier()
		{
			throw new webdevsh_exception('curl extension is not loaded');
		}
	}
?>