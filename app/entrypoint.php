<?php
	chdir(__DIR__ . '/..');

	if(($_SERVER['REQUEST_METHOD'] !== 'GET') && ($_SERVER['REQUEST_METHOD'] !== 'POST'))
	{
		include './app/controllers/samples/http_error.php';
		http_error(400);
		exit();
	}

	switch(explode('/', strtok($_SERVER['REQUEST_URI'], '?'))[1])
	{
		case '': include './app/controllers/samples/home.php'; break;

		case 'about': include './app/controllers/samples/about.php'; break;
		case 'check-date': include './app/controllers/samples/check-date.php'; break;
		case 'database-test': include './app/controllers/samples/database-test.php'; break;
		case 'obsfucate-html': include './app/controllers/samples/obsfucate-html.php'; break;
		case 'login-library-test': include './app/controllers/samples/login-library-test.php'; break;
		case 'login-component-test': include './app/controllers/samples/login-component-test.php'; break;
		case 'preprocessing-test': include './app/controllers/samples/preprocessing-test.php'; break;

		case 'robots.txt': include './app/controllers/samples/robots-sitemap.php'; robots(); break;
		case 'sitemap.xml': include './app/controllers/samples/robots-sitemap.php'; sitemap(); break;

		default: include './app/controllers/samples/http_error.php'; http_error(404);
	}
?>