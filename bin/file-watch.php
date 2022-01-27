<?php
	/*
	 * File watcher - run the command after modifying the file(s)
	 *
	 * Warning:
	 *  shell_exec() function must be allowed
	 *
	 * Example: run watcher with the assets compiler
	 *  php ./bin/file-watch.php "php ./bin/assets-compiler.php ./app/assets ./public/assets" ./app/assets
	 */

	if(!isset($argv[2]))
	{
		echo 'Usage:'.PHP_EOL;
		echo ' file-watch.php "command" path/to/file1 [path/to/dirN]'.PHP_EOL;
		echo PHP_EOL;
		echo 'You can also set the FILE_WATCH_INTERVAL environment variable'.PHP_EOL;
		echo ' in microseconds to modify the default interval (500000).'.PHP_EOL;
		exit(1);
	}

	$watch_interval=500000;
	if(isset($_SERVER['FILE_WATCH_INTERVAL']) && is_numeric($_SERVER['FILE_WATCH_INTERVAL']))
		$watch_interval=$_SERVER['FILE_WATCH_INTERVAL'];

	$files=array();
	$removed_files=array();
	foreach(array_slice($argv, 2) as $file)
		if(file_exists($file))
		{
			if(is_dir($file))
				foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS)) as $directory_name=>$directory_iterator)
				{
					echo '[+] '.$directory_name.PHP_EOL;
					$files[$directory_name]=$directory_iterator->getMTime();
				}
			else
			{
				echo '[+] '.$file.PHP_EOL;
				$files[$file]=filemtime($file);
			}
		}
		else
			echo '[!] '.$file.' not exists'.PHP_EOL;

	if(empty($files))
	{
		echo PHP_EOL.'No files to watch'.PHP_EOL;
		exit(1);
	}

	echo PHP_EOL.'I\'m watching you...'.PHP_EOL;
	while(true)
	{
		clearstatcache();

		foreach($removed_files as $file_name=>$a)
			if(file_exists($file_name))
			{
				echo '[+] '.$file_name.PHP_EOL;
				$files[$file_name]=0;
				unset($removed_files[$file_name]);
			}

		foreach($files as $file_name=>$file_mtime)
			if(!file_exists($file_name))
			{
				echo '[-] ['.date('Y.m.d h:m:s').'] '.$file_name.PHP_EOL;
				echo shell_exec($argv[1]);
				$removed_files[$file_name]=0;
				unset($files[$file_name]);
			}
			else
				if(filemtime($file_name) !== $file_mtime)
				{
					echo '[M] ['.date('Y.m.d h:m:s').'] '.$file_name.PHP_EOL;
					echo shell_exec($argv[1]);
					$files[$file_name]=filemtime($file_name);
				}

		usleep($watch_interval);
	}
?>