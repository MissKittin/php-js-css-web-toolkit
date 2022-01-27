<?php
	include './app/shared/samples/default_http_headers.php';

	include './app/shared/samples/ob_adapter.php';
	ob_adapter::add(new ob_adapter_obminifier());
	ob_adapter::add(new ob_adapter_gzip());
	ob_adapter::add(new ob_adapter_filecache_mod('home.cache'));
	ob_adapter::add(new ob_adapter_gunzip());
	ob_adapter::start();

	include './app/templates/samples/default/default_template.php';
	default_template::quick_view('./app/views/samples/home');
?>