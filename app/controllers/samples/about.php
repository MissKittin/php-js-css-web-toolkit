<?php
	require './app/lib/samples/default_http_headers.php';

	require './app/lib/samples/ob_cache.php';
	ob_cache(ob_url2file(), 0);

	require './app/templates/samples/default/default_template.php';
	default_template::quick_view('./app/views/samples/about');
?>