<?php
	/*
	 * PHP client for webdev.sh minifiers
	 * curl required
	 */

	function css_minifier_com($input, $ignore_https)
	{
		$handler=curl_init();

		curl_setopt_array($handler, [
			CURLOPT_URL => 'https://cssminifier.com/raw',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
			CURLOPT_POSTFIELDS => http_build_query(['input' => $input])
		]);

		if($ignore_https)
		{
			curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
		}

		$output=curl_exec($handler);
		curl_close($handler);

		if($output === false)
		{
			echo ' ! css_minifier_com(): output is empty' . PHP_EOL;
			return $input;
		}

		return $output;
	}
	function javascript_minifier_com($input, $ignore_https)
	{
		$handler=curl_init();

		curl_setopt_array($handler, [
			CURLOPT_URL => 'https://javascript-minifier.com/raw',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
			CURLOPT_POSTFIELDS => http_build_query(['input' => $input])
		]);

		if($ignore_https)
		{
			curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
		}

		$output=curl_exec($handler);
		curl_close($handler);

		if($output === false)
		{
			echo ' ! css_minifier_com(): output is empty' . PHP_EOL;
			return $input;
		}

		return $output;
	}
?>