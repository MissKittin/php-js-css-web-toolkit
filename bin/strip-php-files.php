<?php
	/*
	 * A toy that removes
	 * comments and whitespace from php files
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
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
		load_library(['rmdir_recursive.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(
		isset($argv[1]) &&
		(
			($argv[1] === '-h') ||
			($argv[1] === '--help')
		)
	){
		echo 'Usage: '.$argv[0].' path/to/directory [--remove-tests]'.PHP_EOL;
		exit();
	}

	if(!isset($argv[1]))
	{
		echo 'No directory path given'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[1]))
	{
		echo $argv[1].' is not a directory'.PHP_EOL;
		exit(1);
	}

	if(isset($argv[2]) && ($argv[2] === '--remove-tests'))
	{
		foreach(iterator_to_array(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($argv[1])
			))
			as $directory
		)
			if($directory->isDir() && (substr(strtr($directory, '\\', '/'), -8) === '/tests/.'))
			{
				echo realpath($directory->getPathname());

				if(rmdir_recursive($directory->getPathname()))
					echo ' [ OK ]'.PHP_EOL;
				else
					echo ' [FAIL]'.PHP_EOL;
			}
	}

	foreach(iterator_to_array(
		new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($argv[1], RecursiveDirectoryIterator::SKIP_DOTS)
		))
		as $file
	){
		echo realpath($file->getPathname());

		if(substr($file->getPathname(), -4) !== '.php')
		{
			echo ' [IGNR]'.PHP_EOL;
			continue;
		}

		if(file_put_contents(
			$file->getPathname().'__STRIP_PHP_FILES__',
			php_strip_whitespace($file->getPathname())
		) === false){
			echo ' [FAIL]'.PHP_EOL;
			continue;
		}

		if(!unlink($file->getPathname()))
		{
			unlink($file->getPathname().'__STRIP_PHP_FILES__');
			echo ' [FAIL]'.PHP_EOL;
			continue;
		}

		if(!rename(
			$file->getPathname().'__STRIP_PHP_FILES__',
			$file->getPathname()
		)){
			echo ' [FAIL]'.PHP_EOL;
			continue;
		}

		echo ' [ OK ]'.PHP_EOL;
	}
?>