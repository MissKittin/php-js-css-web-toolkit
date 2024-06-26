<?php
	/*
	 * Start HTTP server
	 * and run phtml files (tests for Js and CSS libraries)
	 *
	 * Looks for files in $argv[1] directory
	 * Looks for files in ../lib/tests directory
	 * Looks for files in ../tests directory
	 *
	 * Warning:
	 *  check_var.php library is required
	 */

	if(php_sapi_name() === 'cli-server')
	{
		if(
			(strtok($_SERVER['REQUEST_URI'], '?') !== '') &&
			(strtok($_SERVER['REQUEST_URI'], '?') !== '/')
		){
			if(substr(strrchr(strtok($_SERVER['REQUEST_URI'], '?'), '.'), 1) === 'phtml')
			{
				if(!is_file('./'.strtok($_SERVER['REQUEST_URI'], '?')))
					return false;

				error_log('Requested test '.$_SERVER['REQUEST_URI']);
				require('./'.strtok($_SERVER['REQUEST_URI'], '?'));

				return true;
			}

			return false;
		}

		error_log('Requested tests list');

		echo 'Directory: '.getcwd().'<br>'."\n";
		foreach(array_slice(scandir('.'), 2) as $file)
			if(substr(strrchr($file, '.'), 1) === 'phtml')
				echo '<a href="/'.$file.'">'.basename($file, '.phtml').'</a><br>'."\n";

		return true;
	}
	else if(php_sapi_name() === 'cli')
	{
		putenv('TK_BIN='.__DIR__);
		putenv('TK_COM='
		.	__DIR__.'/com'."\n"
		.	__DIR__.'/../com'
		);
		putenv('TK_LIB='
		.	__DIR__.'/lib'."\n"
		.	__DIR__.'/../lib'
		);

		if(isset($argv[1]))
		{
			if(!is_dir($argv[1]))
			{
				echo $argv[1].' is not a directory'.PHP_EOL;
				exit(1);
			}

			$tests_dir=$argv[1];
		}
		else
		{
			if(is_dir(__DIR__.'/../lib/tests'))
				$tests_dir=__DIR__.'/../lib/tests';
			else if(is_dir(__DIR__.'/../tests'))
				$tests_dir=__DIR__.'/../tests';
			else
			{
				echo __DIR__.'/../lib/tests directory not found'.PHP_EOL;
				echo __DIR__.'/../tests directory not found'.PHP_EOL;
				exit(1);
			}
		}

		/*
		 * This block comes from the serve.php tool
		 * with modified
		 *  chdir(__DIR__.'/../public');
		 * to
		 *  chdir($tests_dir);
		 */

		function load_library($libraries, $required=true)
		{
			foreach($libraries as $library)
				if(file_exists(__DIR__.'/lib/'.$library))
					require __DIR__.'/lib/'.$library;
				else if(file_exists(__DIR__.'/../lib/'.$library))
					require __DIR__.'/../lib/'.$library;
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
		){
			echo 'Usage:'.PHP_EOL;
			echo ' run-phtml-tests.php [path/to/tests-directory] [--ip 127.0.0.1] [--port 8080] [--docroot ../public] [--preload ./tmp/app-preload.php] [--threads 1]'.PHP_EOL;
			echo PHP_EOL;
			echo '--threads and --preload options requires'.PHP_EOL;
			echo ' PHP 7.4.0 or newer'.PHP_EOL;
			echo 'if the server exists without any error,'.PHP_EOL;
			echo ' it may be a preloader error'.PHP_EOL;
			exit();
		}

		if(!$serve_args=check_env('SERVE_ARGS'))
			$serve_args='';
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
			chdir($tests_dir);

		echo 'Starting PHP server ('.$php_http_addr.':'.$php_http_port.')'.PHP_EOL;
		echo ' in '.getcwd().PHP_EOL;
		if($serve_args !== '')
			echo 'PHP arguments: '.$serve_args.PHP_EOL;
		echo PHP_EOL;

		system('"'.PHP_BINARY.'" '.$serve_args.' '.$php_preload.' -S '.$php_http_addr.':'.$php_http_port .' '. __FILE__);
	}
	else
	{
		echo 'No php_sapi_name() cli or cli-server detected'.PHP_EOL;
		exit(1);
	}
?>