<?php
	/*
	 * Recursively replace all symbolic links with files
	 *
	 * Warning:
	 *  copy_recursive.php library is required
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

	try {
		load_library(['copy_recursive.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(!isset($argv[1]))
	{
		echo $argv[0].' path/to/directory'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[1]))
	{
		echo $argv[1].'is not a directory'.PHP_EOL;
		exit(1);
	}

	try {
		foreach(new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$argv[1],
				RecursiveDirectoryIterator::SKIP_DOTS
			)
		) as $file){
			if(!is_link($file))
				continue;

			echo dirname($file).'/'.readlink($file).' => '.$file;

			if(link2file($file))
				echo ' [ OK ]'.PHP_EOL;
			else
				echo ' [FAIL]'.PHP_EOL;
		}
	} catch(Throwable $error) {
		echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>