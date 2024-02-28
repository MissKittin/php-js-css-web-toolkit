<?php
	/*
	 * A toy that removes
	 * comments and whitespace from php files
	 */

	if(
		isset($argv[1]) &&
		(
			($argv[1] === '-h') ||
			($argv[1] === '--help')
		)
	){
		echo 'Usage: '.$argv[0].' path/to/directory'.PHP_EOL;
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