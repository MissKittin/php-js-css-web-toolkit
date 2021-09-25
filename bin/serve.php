<?php
	/*
	 * Start PHP development server
	 *
	 * Usage:
	 *  php serve.php [-ip 127.0.0.1] [-port 8080] [-docroot ../public] [-preload ../tmp/app-preload.php]
	 */

	if(php_sapi_name() === 'cli-server')
	{
		// router script
		if(
			(file_exists($_SERVER['SCRIPT_FILENAME'])) &&
			($_SERVER['SCRIPT_NAME'] !== '/.htaccess')
		)
			return false;

		include $_SERVER['DOCUMENT_ROOT'].'/index.php';
	}
	else if(php_sapi_name() === 'cli')
	{
		// run php dev server

		include __DIR__.'/../lib/check_var.php';

		if(!$php_http_addr=check_argv_next_param('-ip'))
			$php_http_addr='127.0.0.1';
		if(!$php_http_port=check_argv_next_param('-port'))
			$php_http_port='8080';
		$php_preload=''; if($php_preload=check_argv_next_param('-preload'))
			$php_preload='-d opcache.preload='.$php_preload;
		if($php_http_docroot=check_argv_next_param('-docroot'))
		{
			if(!chdir($php_http_docroot))
				die('Cannot chdir to the '.$php_http_docroot.PHP_EOL);
		}
		else
			chdir(__DIR__ . '/../public');

		echo 'Starting PHP server...' . PHP_EOL.PHP_EOL;
		system(PHP_BINARY.' ' . $php_preload . ' -S ' . $php_http_addr.':'.$php_http_port .' '. __FILE__);
	}
	else
		die('No php_sapi_name() cli or cli-server detected'.PHP_EOL);
?>