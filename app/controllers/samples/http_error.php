<?php
	function http_error($error_code)
	{
		include './app/shared/samples/default_http_headers.php';
		http_response_code($error_code);

		$lang='en';
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			foreach(['pl', $lang] as $lang)
				if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, strlen($lang)) === $lang)
					break;

		if(!isset($_SERVER['HTTP_HOST']))
			$_SERVER['HTTP_HOST']='';

		// this cookie is from app/templates/samples/default/assets/default.js/darkTheme.js
		$theme='bright';
		if(
			isset($_COOKIE['app_dark_theme']) &&
			($_COOKIE['app_dark_theme'] === 'true')
		)
			$theme='dark';

		include './app/shared/samples/ob_adapter.php';
		ob_adapter
			::add(new ob_adapter_obminifier())
			->add(new ob_adapter_gzip())
			->add(new ob_adapter_filecache_mod('http_error_'.$error_code.'_'.$theme.'_'.$lang.'.cache'))
			->add(new ob_adapter_gunzip())
			->start();

		include './app/views/samples/http_error/'.$lang.'/'.$theme.'/'.$error_code.'.php';
	}
?>