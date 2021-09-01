<?php
	// app setup
	chdir(__DIR__ . '/..');
	$APP_ROUTER=explode('/', strtok($_SERVER['REQUEST_URI'], '?'));

	// app url routing
	switch($APP_ROUTER[1])
	{
		case '': include './app/controllers/samples/home.php'; break;

		case 'about': include './app/controllers/samples/about.php'; break;
		case 'check-date': include './app/controllers/samples/check-date.php'; break;
		case 'database-test': include './app/controllers/samples/database-test.php'; break;
		case 'obsfucate-html': include './app/controllers/samples/obsfucate-html.php'; break;
		case 'login-library-test': include './app/controllers/samples/login-library-test.php'; break;
		case 'login-component-test': include './app/controllers/samples/login-component-test.php'; break;
		case 'preprocessing-test': include './app/controllers/samples/preprocessing-test.php'; break;

		default: include './app/controllers/samples/404.php'; break;
	}
?>