<?php
	/*
	 * A tool to quickly build a PHP archive
	 *
	 * Warning:
	 *  check_var.php library is required
	 */

	if(file_exists(__DIR__.'/lib/check_var.php'))
		require __DIR__.'/lib/check_var.php';
	else if(file_exists(__DIR__.'/../lib/check_var.php'))
		require __DIR__.'/../lib/check_var.php';
	else
	{
		echo 'check_var.php library not found'.PHP_EOL;
		exit(1);
	}

	if(check_argv('-h') || check_argv('--help'))
	{
		echo 'Usage: [--compress=gz|bz2] [[--stub=path/to/main.php] [--shebang]] --source=dir1 [--source=dir2] --output=path/to/archive.phar'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --compress -> if not defined, no compression applied'.PHP_EOL;
		echo ' --stub -> app entrypoint (will be added to the root directory)'.PHP_EOL;
		echo ' --shebang -> add #!/usr/bin/env php (stub must be defined)'.PHP_EOL;
		echo ' --source -> path for buildFromDirectory()'.PHP_EOL;
		echo ' --output -> path to the output file'.PHP_EOL;
		exit();
	}

	$compress=check_argv_param('--compress', '=');
	$shebang='';
	$stub=check_argv_param('--stub', '=');
	$sources=check_argv_param_many('--source', '=');
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

	if(check_argv('--shebang'))
		$shebang="#!/usr/bin/env php\n";

	if(($stub !== null) && (!is_file($stub)))
	{
		echo 'Stub '.$stub.' is not a file'.PHP_EOL;
		exit(1);
	}

	if($sources === null)
	{
		echo 'No --source defined'.PHP_EOL;
		exit(1);
	}

	if(file_exists($output))
	{
		echo __DIR__.'/lib.phar already exists.PHP_EOL';
		exit(1);
	}

	foreach($sources as $source)
		if(!is_dir($source))
		{
			echo $source.' is not a directory'.PHP_EOL;
			exit(1);
		}

	try {
		$phar=new Phar($output);
		$phar->startBuffering();

		echo ' -> Adding files'.PHP_EOL;
		foreach($sources as $source)
			foreach($phar->buildFromDirectory($source) as $file_destination=>$file_source)
				echo '  -> '.$file_source.' => '.$file_destination.PHP_EOL;

		if($stub === null)
			$phar->setStub('<?php echo \'This Phar has no stub\'.PHP_EOL; exit(1); __HALT_COMPILER(); ?>');
		else
		{
			echo ' -> Adding stub';
			if($shebang !== '')
				echo ' with shebang'.PHP_EOL;
			echo PHP_EOL;

			echo '  -> '.$stub.' => '.basename($stub).PHP_EOL;
			$phar->addFile($stub, basename($stub));

			$phar->setStub($shebang.$phar->createDefaultStub(basename($stub)));
		}

		$phar->stopBuffering();

		if($compress !== null)
			$phar->compressFiles($compress);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>