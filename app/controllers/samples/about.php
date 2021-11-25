<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/ob_cache.php';
	if(ob_file_cache('./tmp/cache_'.str_replace('/', '___', strtok($_SERVER['REQUEST_URI'], '?')), 0) === 0)
		exit();

	$view['lang']='en';
	$view['title']='About';
	include './app/models/samples/about.php';
	include './app/views/samples/default/default.php';
?>