<?php
	require './app/lib/samples/default_http_headers.php';

	require './app/lib/samples/ob_adapter.php';
	ob_adapter
		::add(new ob_adapter_obminifier())
		->add(new ob_adapter_gzip())
		->add(new ob_adapter_filecache_mod('home.cache'))
		->add(new ob_adapter_gunzip())
		->start();

	require './app/templates/samples/default/default_template.php';
	default_template::quick_view('./app/views/samples/home');
?>