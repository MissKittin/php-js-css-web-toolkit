<?php
	/*
	 * Interface for the matthiasmullie/minify package
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  matthiasmullie/minify package is required
	 *
	 * Composer directory path:
	 *  __DIR__/composer/vendor
	 *  __DIR__/vendor
	 *  __DIR__/../composer/vendor
	 *  __DIR__/../vendor
	 *  getenv(TK_COMPOSER)
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

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
	function find_composer_autoloader()
	{
		foreach([
			'composer/vendor/autoload.php',
			'vendor/autoload.php',
			'../composer/vendor/autoload.php',
			'../vendor/autoload.php'
		] as $composer_path)
			if(is_file(__DIR__.'/'.$composer_path))
				return __DIR__.'/'.$composer_path;

		$TK_COMPOSER=getenv('TK_COMPOSER');

		if(
			($TK_COMPOSER !== false) &&
			is_file($TK_COMPOSER.'/autoload.php')
		)
			return $TK_COMPOSER.'/autoload.php';

		throw new Exception('Composer autoloader not found');
	}

	try {
		load_library(['check_var.php']);
		require find_composer_autoloader();

		if(!class_exists('\MatthiasMullie\Minify\CSS'))
			throw new Exception('matthiasmullie/minify package not installed');
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$input_directory=check_argv_next_param('--dir');
	$minify_styles=true;
	$minify_scripts=true;

	if(check_argv('--no-css'))
		$minify_styles=false;

	if(check_argv('--no-js'))
		$minify_scripts=false;

	if(
		($input_directory === null) ||
		check_argv('--help') || check_argv('-h')
	){
		echo 'Usage: '.$argv[0].' --dir ./public/assets [--no-css] [--no-js]'.PHP_EOL;
		echo 'where ./public/assets is a directory'.PHP_EOL;
		echo ' --no-css disables CSS minification'.PHP_EOL;
		echo ' --no-js disables Javascript minification'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($input_directory))
	{
		echo $input_directory.' is not a directory'.PHP_EOL;
		exit(1);
	}

	foreach(new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
			$input_directory,
			RecursiveDirectoryIterator::SKIP_DOTS
		)
	) as $asset)
		try {
			switch(pathinfo($asset, PATHINFO_EXTENSION))
			{
				case 'css':
					if($minify_styles)
					{
						echo 'Processing '.$asset.PHP_EOL;

						(new MatthiasMullie\Minify\CSS($asset))
						->	minify($asset);
					}
				break;
				case 'js':
					if($minify_scripts)
					{
						echo 'Processing '.$asset.PHP_EOL;

						(new MatthiasMullie\Minify\JS($asset))
						->	minify($asset);
					}
			}
		} catch(Throwable $error) {
			echo ' failed: '.$error->getMessage().PHP_EOL;
		}
?>