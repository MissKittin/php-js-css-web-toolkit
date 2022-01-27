<?php
	include './app/shared/samples/default_http_headers.php';

	include './app/shared/samples/ob_cache.php';
	ob_cache(ob_url2file(), 0);

	include './app/templates/samples/default/default_template.php';
	default_template::quick_view('./app/views/samples/about');
?>