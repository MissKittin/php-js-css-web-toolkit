<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/ob_sfucator.php';
	ob_start('ob_sfucator');

	$view['lang']='en';
	$view['title']='HTML obsfucator';
	
	include './app/models/samples/obsfucate-html.php';
	include './app/views/samples/default/default.php';
?>