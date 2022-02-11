<?php
	/*
	 * Start PHP development server
	 *
	 * Warning:
	 *  check_var.php library is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	if(php_sapi_name() === 'cli-server')
	{
		if(
			(file_exists($_SERVER['SCRIPT_FILENAME'])) &&
			($_SERVER['SCRIPT_FILENAME'] !== __FILE__) &&
			($_SERVER['SCRIPT_NAME'] !== '/.htaccess')
		)
			return false;

		include $_SERVER['DOCUMENT_ROOT'].'/index.php';
	}
	else if(php_sapi_name() === 'cli')
	{
		function load_library($libraries, $required=true)
		{
			foreach($libraries as $library)
				if(file_exists(__DIR__.'/lib/'.$library))
					include __DIR__.'/lib/'.$library;
				else if(file_exists(__DIR__.'/../lib/'.$library))
					include __DIR__.'/../lib/'.$library;
				else
					if($required)
						throw new Exception($library.' library not found');
		}

		try {
			load_library(['check_var.php']);
		} catch(Exception $error) {
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(
			check_argv('--help') ||
			check_argv('-h')
		)
		{
			echo 'Usage:'.PHP_EOL;
			echo ' serve.php [--ip 127.0.0.1] [--port 8080] [--docroot ../public] [--preload ./tmp/app-preload.php] [--threads 1]'.PHP_EOL;
			echo PHP_EOL;
			echo '--threads option requires PHP 7.4.0 or newer'.PHP_EOL;
			exit();
		}

		if(!$php_http_addr=check_argv_next_param('--ip'))
			$php_http_addr='127.0.0.1';
		if(!$php_http_port=check_argv_next_param('--port'))
			$php_http_port='8080';
		$php_preload=''; if($php_preload=check_argv_next_param('--preload'))
			$php_preload='-d opcache.preload='.realpath($php_preload);
		if($php_http_threads=check_argv_next_param('--threads'))
			if(is_numeric($php_http_threads) && ($php_http_threads > 1))
				putenv("PHP_CLI_SERVER_WORKERS=$php_http_threads");
		if($php_http_docroot=check_argv_next_param('--docroot'))
		{
			if(!chdir($php_http_docroot))
				die('Cannot chdir to the '.$php_http_docroot.PHP_EOL);
		}
		else
			chdir(__DIR__.'/../public');

		echo 'Starting PHP server...'.PHP_EOL.PHP_EOL;
		system(PHP_BINARY.' '.$php_preload.' -S '.$php_http_addr.':'.$php_http_port .' '. __FILE__);
	}
	else
		die('No php_sapi_name() cli or cli-server detected'.PHP_EOL);
?>