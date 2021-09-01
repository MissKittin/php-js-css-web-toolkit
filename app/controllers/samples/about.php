<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/file_cache.php';
	if(file_cache(array(
		'cache_file_url'=>$APP_ROUTER[1]
	))['status'] === 0)
		exit();

	$view['lang']='en';
	$view['title']='About';
	include './app/models/samples/about.php';
	include './app/views/samples/default/default.php';
?>