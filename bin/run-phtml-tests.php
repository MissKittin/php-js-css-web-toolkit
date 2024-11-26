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

		foreach(array_diff(
			scandir('.'),
			['.', '..']
		) as $file)
			if(substr(strrchr($file, '.'), 1) === 'phtml')
				echo '<a href="/'.$file.'">'.basename($file, '.phtml').'</a><br>'."\n";

		return true;
	}

	if(php_sapi_name() === 'cli')
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

		switch(true)
		{
			case (isset($argv[1])):
				if(!is_dir($argv[1]))
				{
					echo $argv[1].' is not a directory'.PHP_EOL;
					exit(1);
				}

				$tests_dir=$argv[1];
			break;
			case (is_dir(__DIR__.'/../lib/tests')):
				$tests_dir=__DIR__.'/../lib/tests';
			break;
			case (is_dir(__DIR__.'/../tests')):
				$tests_dir=__DIR__.'/../tests';
			break;
			default:
				echo __DIR__.'/../lib/tests directory not found'.PHP_EOL;
				echo __DIR__.'/../tests directory not found'.PHP_EOL;
				exit(1);
		}

		function load_library($libraries, $required=true)
		{
			foreach($libraries as $library)
			{
				if(file_exists(__DIR__.'/lib/'.$library))
				{
					require __DIR__.'/lib/'.$library;
					continue;
				}

				if(file_exists(__DIR__.'/../lib/'.$library))
				{
					require __DIR__.'/../lib/'.$library;
					continue;
				}

				if($required)
					throw new Exception($library.' library not found');
			}
		}

		try {
			load_library(['check_var.php']);
		} catch(Exception $error) {
			echo 'Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(check_argv('--help') || check_argv('-h'))
		{
			echo 'Usage:'.PHP_EOL;
			echo ' '.$argv[0].' [path/to/tests-directory] [--ip 127.0.0.1] [--port 8080]'.PHP_EOL;
			exit();
		}

		$serve_args=check_env('SERVE_ARGS');
		$php_http_addr=check_argv_next_param('--ip');
		$php_http_port=check_argv_next_param('--port');

		if($serve_args === null)
			$serve_args='';

		if($php_http_addr === null)
			$php_http_addr='127.0.0.1';

		if($php_http_port === null)
			$php_http_port='8080';

		if(!chdir($tests_dir))
		{
			echo 'Cannot chdir to the '.$php_http_docroot.PHP_EOL;
			exit(1);
		}

		echo 'Starting PHP server ('.$php_http_addr.':'.$php_http_port.')'.PHP_EOL;
		echo ' in '.getcwd().PHP_EOL;

		if($serve_args !== '')
			echo 'PHP arguments: '.$serve_args.PHP_EOL;

		echo PHP_EOL;

		system('"'.PHP_BINARY.'" '
		.	$serve_args.' '
		.	' -S '.$php_http_addr.':'.$php_http_port.' '
		.	__FILE__
		);
	}

	echo 'No php_sapi_name() cli or cli-server detected'.PHP_EOL;
	exit(1);
?>