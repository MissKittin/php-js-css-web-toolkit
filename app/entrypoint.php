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
	 * The following headers are ignored by public/.htaccess
	 * Edit public/.htaccess to get the following code to work
	 */
	if(
		isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	)
		$_SERVER['HTTPS']='on';
	if(isset($_SERVER['HTTP_X_FORWARDED_HOST']))
		$_SERVER['HTTP_HOST']=$_SERVER['HTTP_X_FORWARDED_HOST'];
	if(isset($_SERVER['HTTP_X_FORWARDED_PORT']))
		$_SERVER['SERVER_PORT']=$_SERVER['HTTP_X_FORWARDED_PORT'];
	if(isset($_SERVER['HTTP_X_REAL_IP']))
		$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_REAL_IP'];

	register_shutdown_function(function(){
		$exec_time=microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'];
		error_log('Executed in '.$exec_time.' seconds, '.memory_get_peak_usage().' bytes used');
	});

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
		case 'http_error_test':
			include './app/controllers/samples/http_error.php';

			$error_code=explode('/', strtok($_SERVER['REQUEST_URI'], '?'));

			if(isset($error_code[2]))
				switch($error_code[2])
				{
					case '401': http_error(401); break;
					case '403': http_error(403); break;
					case '404': http_error(404); break;
					case '410': http_error(410); break;
					default: http_error(400);
				}
			else
				http_error(400);
		break;

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

			if(is_dir($_SERVER['DOCUMENT_ROOT'].strtok($_SERVER['REQUEST_URI'], '?')))
				http_error(403);
			else
				http_error(404);
	}
?>