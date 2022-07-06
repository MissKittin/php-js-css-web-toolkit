<?php
	chdir(__DIR__.'/..');

	if(
		(!isset($_SERVER['REQUEST_URI'])) ||
		(!isset($_SERVER['REQUEST_METHOD']))
	){
		include './app/controllers/samples/http_error.php';
		http_error(400);
		exit();
	}

	if(
		($_SERVER['REQUEST_METHOD'] !== 'GET') &&
		($_SERVER['REQUEST_METHOD'] !== 'POST')
	){
		include './app/controllers/samples/http_error.php';
		http_error(400);
		exit();
	}

	/*
	 * The X-Forwarded-Proto header is ignored by public/.htaccess
	 * Edit public/.htaccess to get the following code to work
	 */
	if(
		isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	)
		$_SERVER['HTTPS']='on';

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

		case 'robots.txt':
			if(!isset($_SERVER['HTTP_HOST']))
			{
				include './app/controllers/samples/http_error.php';
				http_error(400);
				exit();
			}

			include './app/controllers/samples/robots-sitemap.php';
			robots();
		break;
		case 'sitemap.xml':
			if(!isset($_SERVER['HTTP_HOST']))
			{
				include './app/controllers/samples/http_error.php';
				http_error(400);
				exit();
			}

			include './app/controllers/samples/robots-sitemap.php';
			sitemap();
		break;

		default:
			include './app/controllers/samples/http_error.php';
			http_error(404);
	}
?>