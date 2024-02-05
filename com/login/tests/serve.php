<?php
	/*
	 * add ?theme=dark in url to apply middleware_form_dark.css
	 */

	if(php_sapi_name() === 'cli-server')
	{
		error_log('Request '.$_SERVER['REQUEST_URI']);

		if(
			($_SERVER['REQUEST_URI'] === '/assets/login_bright.css') ||
			($_SERVER['REQUEST_URI'] === '/assets/login_dark.css')
		){
			error_log(' -> Compiling '.$_SERVER['REQUEST_URI']);

			header('Content-Type: text/css');

			foreach(array_slice(scandir(__DIR__.'/../'.$_SERVER['REQUEST_URI']), 2) as $file)
				readfile(__DIR__.'/../'.$_SERVER['REQUEST_URI'].'/'.$file);

			exit();
		}

		session_start();

		if(
			isset($_GET['theme']) &&
			($_GET['theme'] === 'dark')
		)
			$GLOBALS['_login']['view']['login_style']='login_dark.css';

		include __DIR__.'/../login.php';

		exit();
	}

	if(!isset($argv[1]))
	{
		echo 'Use "serve.php serve" to start built-in server'.PHP_EOL;
		exit();
	}
	if($argv[1] !== 'serve')
	{
		echo 'Use "serve.php serve" to start built-in server'.PHP_EOL;
		exit();
	}

	echo 'Starting PHP server...'.PHP_EOL.PHP_EOL;
	system(PHP_BINARY.' -S 127.0.0.1:8080  '.__FILE__);
?>