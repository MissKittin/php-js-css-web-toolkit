<?php
	/*
	 * PHP client for webdev.sh minifiers
	 *
	 * Warning:
	 *  curl extension is required
	 *
	 * Note:
	 *  throws an exception on an empty response from the server
	 *
	 * Usage:
	 *  webdevsh_css_minifier(file_get_contents('./style.css')) [returns string]
	 *  webdevsh_js_minifier(file_get_contents('./script.js')) [returns string]
	 */

	function webdevsh_css_minifier(string $input, bool $ignore_https=false)
	{
		if(!extension_loaded('curl'))
			throw new Exception('curl extension is not loaded');

		$handler=curl_init();

		curl_setopt_array($handler, [
			CURLOPT_URL=>'https://cssminifier.com/raw',
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_POST=>true,
			CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
			CURLOPT_POSTFIELDS=>http_build_query(['input'=>$input])
		]);

		if($ignore_https)
		{
			curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
		}

		$output=curl_exec($handler);
		curl_close($handler);

		if($output === false)
			throw new Exception('Server response is empty');

		return $output;
	}
	function webdevsh_js_minifier(string $input, bool $ignore_https=false)
	{
		if(!extension_loaded('curl'))
			throw new Exception('curl extension is not loaded');

		$handler=curl_init();

		curl_setopt_array($handler, [
			CURLOPT_URL=>'https://javascript-minifier.com/raw',
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_POST=>true,
			CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
			CURLOPT_POSTFIELDS=>http_build_query(['input'=>$input])
		]);

		if($ignore_https)
		{
			curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
		}

		$output=curl_exec($handler);
		curl_close($handler);

		if($output === false)
			throw new Exception('Server response is empty');

		return $output;
	}
?>