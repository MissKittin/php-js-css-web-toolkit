<?php
	$view['csp_header']['script-src'][]='\'sha256-0kn4mQtwixDv4IoLMlEO/NXDDiTXzO3lCLIVLvy5Gh8=\'';
	$view['csp_header']['script-src'][]='\'sha256-5aemaHOjFawbHR/QA2t8+UI69Qm3iWMPpaWhIXTb/2c=\''; // minified

	$view['lang']='en';
	$view['title']='Main page';
	$view['meta_description']='PHP-JS-CSS web toolkit - a set of tools and libraries that you can use in your project';
	$view['meta_robots']='index,follow';
	$view['scripts']=['/assets/sendNotification.js'];
	$view['home_links']=[
		'About toolkit'=>'/about',
		'check_date() test'=>'/check-date',
		'Database libraries test'=>'/database-test',
		'HTML obsfucator test'=>'/obsfucate-html',
		'Login library test'=>'/login-library-test',
		'Login component test (login and password: test)'=>'/login-component-test',
		'PHP preprocessing test'=>'/preprocessing-test',
		'404 error'=>'/nonexistent',
		'HTTP errors'=>[
			'400'=>'/http_error_test',
			'401'=>'/http_error_test/401',
			'403'=>'/http_error_test/403',
			'410'=>'/http_error_test/410'
		]
	];
?>