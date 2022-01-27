<?php
	include './app/shared/samples/default_http_headers.php';
	http_response_code(404);

	$lang='en';
	if(
		(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) &&
		(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'pl')
	)
		$lang='pl';

	include './app/shared/samples/ob_adapter.php';
	ob_adapter::add(new ob_adapter_obminifier());
	ob_adapter::add(new ob_adapter_gzip());
	ob_adapter::add(new ob_adapter_filecache_mod('404_'.$lang.'.cache'));
	ob_adapter::add(new ob_adapter_gunzip());
	ob_adapter::start();

	include './app/views/samples/404/404_'.$lang.'.html';
?>