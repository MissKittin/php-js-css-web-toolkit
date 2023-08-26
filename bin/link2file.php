<?php
	/*
	 * Recursively convert all symbolic links to files
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
			if(file_exists(__DIR__.'/lib/'.$library))
				require __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				require __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
	}

	try {
		load_library(['copy_recursive.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(!isset($argv[1]))
	{
		echo 'link2file.php path/to/directory'.PHP_EOL;
		exit(1);
	}
	if(!is_dir($argv[1]))
	{
		echo $argv[1].'is not a directory'.PHP_EOL;
		exit(1);
	}

	$cwd=getcwd();

	try {
		foreach(new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$argv[1],
				RecursiveDirectoryIterator::SKIP_DOTS
			)
		) as $file)
			if(is_link($file))
			{
				$link_destination=readlink($file);

				chdir($argv[1]);
				$link_destination=realpath($link_destination);
				chdir($cwd);

				$link_destination=realpath(readlink($file));

				if($link_destination !== false)
				{
					echo $link_destination.' => '.$file.PHP_EOL;

					if(unlink($file) === false)
						throw new Exception('The link cannot be removed');

					if(copy_recursive($link_destination, $file) === false)
						throw new Exception('File cannot be copied');
				}
			}
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>