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

		include './app/shared/samples/ob_adapter.php';
		ob_adapter::add(new ob_adapter_obminifier());
		ob_adapter::add(new ob_adapter_gzip());
		ob_adapter::add(new ob_adapter_filecache_mod('http_'.$error_code.'_'.$lang.'.cache'));
		ob_adapter::add(new ob_adapter_gunzip());
		ob_adapter::start();

		include './app/views/samples/http_error/'.$lang.'/'.$error_code.'.php';
	}
?>