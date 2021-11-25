<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/ob_cache.php';
	if(ob_file_cache('./tmp/cache_'.str_replace('/', '___', strtok($_SERVER['REQUEST_URI'], '?')), 0) === 0)
		exit();

	$view['lang']='en';
	$view['title']='Main page';
	$view['scripts']=['/assets/sendNotification.js'];
	$view['home_links']=array(
		'About toolkit' => '/about',
		'check_date() test' => '/check-date',
		'Database libraries test' => '/database-test',
		'HTML obsfucator test' => '/obsfucate-html',
		'Login library test' => '/login-library-test',
		'Login component test (login and password: test)' => '/login-component-test',
		'PHP preprocessing test' => '/preprocessing-test',
		'404 error' => '/nonexistent'
	);
	include './app/models/samples/home.php';
	include './app/views/samples/default/default.php';
?>