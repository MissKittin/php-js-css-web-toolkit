<?php
	/*
	 * Recursively convert all symbolic links to files
	 *
	 * Warning:
	 *  copy_recursive.php library is required
	 */

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

	load_library(['copy_recursive.php']);

	$cwd=getcwd();
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($argv[1], RecursiveDirectoryIterator::SKIP_DOTS)) as $file)
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
					throw new Exception('the link cannot be removed');
				if(copy_recursive($link_destination, $file) === false)
					throw new Exception('file cannot be copied');
			}
		}
?>