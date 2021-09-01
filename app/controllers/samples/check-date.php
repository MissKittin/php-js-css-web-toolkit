<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/file_cache.php';
	if(file_cache(array(
		'cache_file_url'=>$APP_ROUTER[1]
	))['status'] === 0)
		exit();

	include './lib/check_date.php';

	$view['first-question']=false;
	$view['second-question']=false;
	if(check_date(23,6, 12,8))
		$view['first-question']=true;
	if(check_date(14,9, 23,4))
		$view['second-question']=true;

	$view['lang']='en';
	$view['title']='Check date test';
	include './app/models/samples/check-date.php';
	include './app/views/samples/default/default.php';
?>