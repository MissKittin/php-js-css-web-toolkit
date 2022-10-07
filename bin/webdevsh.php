<?php
	/*
	 * webdev.sh client
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  webdevsh.php library is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

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
		load_library([
			'check_var.php',
			'webdevsh.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$input_directory=check_argv_next_param('--dir');
	$minify_styles=true;
	if(check_argv('--no-css'))
		$minify_styles=false;
	$minify_scripts=true;
	if(check_argv('--no-js'))
		$minify_scripts=false;
	$ignore_https=false;
	if(check_argv('--no-check-certificate'))
		$ignore_https=true;

	if(($input_directory === null) || check_argv('--help') || check_argv('-h'))
	{
		echo 'Usage: --dir ./public/assets [--no-css] [--no-js] [--no-check-certificate]'.PHP_EOL;
		echo 'where ./public/assets is a directory'.PHP_EOL;
		echo ' --no-css disables CSS minification'.PHP_EOL;
		echo ' --no-js disables Javascript minification'.PHP_EOL;
		echo ' --no-check-certificate disables HTTP certificate check in the curl'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($input_directory))
	{
		echo $input_directory.' is not a directory'.PHP_EOL;
		exit(1);
	}

	foreach(new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($input_directory, RecursiveDirectoryIterator::SKIP_DOTS)
	) as $asset)
		switch(pathinfo($asset, PATHINFO_EXTENSION))
		{
			case 'css':
				if($minify_styles)
				{
					echo 'Processing '.$asset.PHP_EOL;

					try {
						if(file_put_contents(
							$asset,
							webdevsh_css_minifier(
								file_get_contents($asset),
								$ignore_https
							)
						) === false)
							echo ' failed: file cannot be saved'.PHP_EOL;
					} catch(Exception $error) {
						echo ' failed: '.$error->getMessage().PHP_EOL;
					}
				}
			break;
			case 'js':
				if($minify_scripts)
				{
					echo 'Processing '.$asset.PHP_EOL;

					try {
						if(file_put_contents(
							$asset,
							webdevsh_js_minifier(
								file_get_contents($asset),
								$ignore_https
							)
						) === false)
							echo ' failed: file cannot be saved'.PHP_EOL;
					} catch(Exception $error) {
						echo ' failed: '.$error->getMessage().PHP_EOL;
					}
				}
		}
?>