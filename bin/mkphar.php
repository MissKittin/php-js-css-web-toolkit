<?php
	/*
	 * A tool to quickly build a PHP archive
	 *
	 * Warning:
	 *  check_var.php library is required
	 */

	function get_regex($item, $pathname)
	{
		if($item[1])
			return preg_match(
				'/'.$item[0].'/',
				strtr($pathname, '\\', '/')
			);

		return (strpos(
			strtr($pathname, '\\', '/'),
			strtr($item[0], '\\', '/')
		) !== false);
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

	if(check_argv('-h') || check_argv('--help'))
	{
		echo 'Usage: '.$argv[0].' [--compress=gz|bz2] [[--stub=path/to/main.php] [--shebang[=#!/path/to/env php-bin-name]]] --source=dir1 [--source=dir2] [--ignore=filename] [--ignore=dirname/] [--ignore=dir/file] [--include=/file.ext] --output=path/to/archive.phar'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --compress -> if not defined, no compression applied'.PHP_EOL;
		echo ' --stub -> app entrypoint (will be added to the root directory)'.PHP_EOL;
		echo ' --shebang -> add #!/usr/bin/env php (stub must be defined)'.PHP_EOL;
		echo '  you can also use: "--shebang=#!/path/to/env php-bin-name"'.PHP_EOL;
		echo ' --source -> path for addFile() (to file or directory)'.PHP_EOL;
		echo '  you can also use: --source=.'.PHP_EOL;
		echo ' --ignore -> do not add file/directory with name'.PHP_EOL;
		echo ' --include -> force add file that ends with name (has priority over --ignore)'.PHP_EOL;
		echo ' --ignore-regex -> do not add file/directory with name'.PHP_EOL;
		echo ' --include-regex -> force add file that ends with name (has priority over --ignore)'.PHP_EOL;
		echo '  note:'.PHP_EOL;
		echo '   it doesn\'t matter if there is a slash or a backslash in the path'.PHP_EOL;
		echo '   parent and absolute paths are forbidden'.PHP_EOL;
		echo ' --output -> path to the output file'.PHP_EOL;
		exit();
	}

	$compress=check_argv_param('--compress', '=');
	$shebang='';
	$custom_shebang=check_argv_param('--shebang', '=');
	$stub=check_argv_param('--stub', '=');
	$sources=check_argv_param_many('--source', '=');
	$ignores=check_argv_param_many('--ignore-regex', '=');
	$includes=check_argv_param_many('--include-regex', '=');
	$ignores_std=check_argv_param_many('--ignore', '=');
	$includes_std=check_argv_param_many('--include', '=');
	$output=check_argv_param('--output', '=');

	if(!Phar::canWrite())
	{
		echo 'Add "-d phar.readonly=0" before "'.$argv[0].'"'.PHP_EOL;
		exit(1);
	}

	switch($compress)
	{
		case 'gz':
			if(!extension_loaded('zlib'))
			{
				echo 'zlib extension is not loaded'.PHP_EOL;
				exit(1);
			}

			echo ' -> Using compression: gz'.PHP_EOL;

			$compress=Phar::GZ;
		break;
		case 'bz2':
			if(!extension_loaded('bz2'))
			{
				echo 'bz2 extension is not loaded'.PHP_EOL;
				exit(1);
			}

			echo ' -> Using compression: bz2'.PHP_EOL;

			$compress=Phar::BZ2;
		break;
		case null:
			echo ' -> Without using compression'.PHP_EOL;
		break;
		default:
			echo '--compress=gz or --compress=bz2'.PHP_EOL;
			exit(1);
	}

	if($custom_shebang !== null)
		$shebang=$custom_shebang."\n";
	else if(check_argv('--shebang'))
		$shebang="#!/usr/bin/env php\n";

	if(
		($stub !== null) &&
		(!is_file($stub))
	){
		echo 'Stub '.$stub.' is not a file'.PHP_EOL;
		exit(1);
	}

	if($sources === null)
	{
		echo 'No --source defined'.PHP_EOL;
		exit(1);
	}

	if($ignores === null)
		$ignores=[];
	else
	{
		$new_ignores=[];

		foreach($ignores as $file)
			$new_ignores[]=[$file, true];

		$ignores=$new_ignores;
		unset($new_ignores);
	}

	if($includes === null)
		$includes=[];
	else
	{
		$new_includes=[];

		foreach($includes as $file)
			$new_includes[]=[$file, true];

		$includes=$new_includes;
		unset($new_includes);
	}

	if($ignores_std !== null)
		foreach($ignores_std as $file)
			$ignores[]=[$file, false];

	if($includes_std !== null)
		foreach($includes_std as $file)
			$includes[]=[$file, false];

	if(file_exists($output))
	{
		echo $output.' already exists'.PHP_EOL;
		exit(1);
	}

	foreach($sources as &$source)
	{
		if(!file_exists($source))
		{
			echo $source.' does not exist'.PHP_EOL;
			exit(1);
		}

		if(
			(substr($source, 0, 1) === '/') ||
			(substr($source, 1, 2) === ':\\')
		){
			echo $source.': absolute paths are forbidden'.PHP_EOL;
			exit(1);
		}

		if(
			(substr($source, 0, 3) === '../') ||
			(substr($source, 0, 3) === '..\\')
		){
			echo $source.': parent paths are forbidden'.PHP_EOL;
			exit(1);
		}

		if(
			($source === '.') ||
			($source === './') ||
			($source === '.\\')
		)
			continue;

		if(
			(substr($source, 0, 2) === './') ||
			(substr($source, 0, 2) === '.\\')
		)
			$source=substr($source, 2);
	}

	try {
		$phar=new Phar($output);
		$phar->startBuffering();

		echo ' -> Adding files'.PHP_EOL;

		foreach($sources as &$source)
		{
			if(is_file($source))
			{
				echo '[ADD] '.$source.PHP_EOL;

				$phar->addFile(
					$source,
					strtr($source, '\\', '/')
				);

				continue;
			}

			foreach(
				new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$source,
						RecursiveDirectoryIterator::SKIP_DOTS
					)
				)
				as $file
			){
				foreach($ignores as $ignore)
				{
					if(get_regex(
						$ignore,
						$file->getPathname()
					)){
						foreach($includes as $include)
							if(get_regex(
								$include,
								$file->getPathname()
							))
								break 2;

						echo '[IGN] '.$file->getPathname().PHP_EOL;

						continue 2;
					}
				}

				echo '[ADD] '.$file->getPathname().PHP_EOL;

				$phar->addFile(
					$file->getPathname(),
					strtr($file->getPathname(), '\\', '/')
				);
			}
		}

		if($stub === null)
			$phar->setStub(''
			.	'<?php '
			.		'echo \'This Phar has no stub\'.PHP_EOL;'
			.		'exit(1);'
			.		'__HALT_COMPILER(); '
			.	'?>'
			);
		else
		{
			echo ' -> Adding stub';

			if($shebang !== '')
				echo ' with shebang ('.trim($shebang).')';

			echo PHP_EOL;
			echo '  -> '.$stub.' => __'.basename($stub).PHP_EOL;

			$phar->addFile(
				$stub,
				'__'.basename($stub)
			);

			$phar->setStub(''
			.	$shebang
			.	$phar->createDefaultStub(
					'__'.basename($stub)
				)
			);
		}

		$phar->stopBuffering();

		if($compress !== null)
			$phar->compressFiles($compress);
	} catch(Throwable $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>