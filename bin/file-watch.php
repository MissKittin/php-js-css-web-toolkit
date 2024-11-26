<?php
	/*
	 * File watcher
	 * run the command after modifying the file(s)
	 *
	 * Example: run watcher with the assets compiler
	 *  php ./bin/file-watch.php "php ./bin/assets-compiler.php ./app/assets ./public/assets" ./app/assets
	 *  php ./bin/file-watch.php "php ./bin/assets-compiler.php ./app/assets ./public/assets" ./app/assets --extended
	 */

	if(!isset($argv[2]))
	{
		echo 'Usage:'.PHP_EOL;
		echo ' '.$argv[0].' "command" path/to/file1 [path/to/dirN] [--extended]'.PHP_EOL;
		echo PHP_EOL;
		echo 'You can also set the FILE_WATCH_INTERVAL environment variable'.PHP_EOL;
		echo ' in microseconds to modify the default interval (500000) [0.5s].'.PHP_EOL;
		echo PHP_EOL;
		echo 'In standard mode, the program indexes all files at startup.'.PHP_EOL;
		echo 'New files not belonging to the index will be ignored.'.PHP_EOL;
		echo 'By adding the --extended parameter at the end of the argument list,'.PHP_EOL;
		echo 'the program looks for new files each time.'.PHP_EOL;
		exit(1);
	}

	$watch_interval=500000; // 0.5s
	$standard_mode=true;
	$files=[];
	$removed_files=[];
	$_argv=$argv;

	if(is_numeric(getenv('FILE_WATCH_INTERVAL')))
		$watch_interval=(int)getenv('FILE_WATCH_INTERVAL');

	if(end($_argv) === '--extended')
	{
		echo 'Extended mode enabled'.PHP_EOL.PHP_EOL;

		$standard_mode=false;
		array_pop($argv);
	}

	foreach(array_slice($argv, 2) as $file)
	{
		if(file_exists($file))
		{
			if(is_dir($file))
			{
				foreach(new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$file,
						RecursiveDirectoryIterator::SKIP_DOTS
					)
				) as $directory_name=>$directory_iterator){
					echo '[+] '.$directory_name.PHP_EOL;
					$files[$directory_name]=$directory_iterator->getMTime();
				}

				continue;
			}

			echo '[+] '.$file.PHP_EOL;
			$files[$file]=filemtime($file);

			continue;
		}

		echo '[!] '.$file.' not exists'.PHP_EOL;
	}

	if(empty($files))
	{
		echo PHP_EOL.'No files to watch'.PHP_EOL;
		exit(1);
	}

	echo PHP_EOL.'I\'m watching you...'.PHP_EOL;

	if($standard_mode)
		while(true)
		{
			clearstatcache();

			$execute_prog=false;

			foreach($removed_files as $file_name=>$a)
				if(file_exists($file_name))
				{
					echo '[+] '.$file_name.PHP_EOL;

					$files[$file_name]=0;
					unset($removed_files[$file_name]);
				}

			foreach($files as $file_name=>$file_mtime)
			{
				if(!file_exists($file_name))
				{
					echo '[-] ['.date('Y.m.d h:m:s').'] '.$file_name.PHP_EOL;

					$execute_prog=true;
					$removed_files[$file_name]=0;
					unset($files[$file_name]);

					continue;
				}

				if(filemtime($file_name) !== $file_mtime)
				{
					echo '[M] ['.date('Y.m.d h:m:s').'] '.$file_name.PHP_EOL;

					$execute_prog=true;
					$files[$file_name]=filemtime($file_name);
				}
			}

			if($execute_prog)
				echo shell_exec($argv[1]);

			usleep($watch_interval);
		}

	while(true)
	{
		clearstatcache();

		$execute_prog=false;

		foreach(array_slice($argv, 2) as $file)
			if(file_exists($file))
			{
				if(is_dir($file))
				{
					foreach(new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator(
							$file,
							RecursiveDirectoryIterator::SKIP_DOTS
						)
					) as $directory_name=>$directory_iterator){
						if(isset($files[$directory_name]))
						{
							if(filemtime($directory_name) !== $files[$directory_name])
							{
								echo '[M] ['.date('Y.m.d h:m:s').'] '.$directory_name.PHP_EOL;

								$execute_prog=true;
								$files[$directory_name]=filemtime($directory_name);
							}

							continue;
						}

						echo '[+] ['.date('Y.m.d h:m:s').'] '.$directory_name.PHP_EOL;

						$execute_prog=true;
						$files[$directory_name]=filemtime($directory_name);
					}

					continue;
				}

				if(isset($files[$file]))
				{
					if(filemtime($file) !== $files[$file])
					{
						echo '[M] ['.date('Y.m.d h:m:s').'] '.$file.PHP_EOL;

						$execute_prog=true;
						$files[$file]=filemtime($file);
					}

					continue;
				}

				echo '[+] ['.date('Y.m.d h:m:s').'] '.$file.PHP_EOL;

				$execute_prog=true;
				$files[$file]=filemtime($file);
			}

		foreach($files as $file_name=>$file_mtime)
			if(!file_exists($file_name))
			{
				echo '[-] ['.date('Y.m.d h:m:s').'] '.$file_name.PHP_EOL;

				$execute_prog=true;
				unset($files[$file_name]);
			}

		if($execute_prog)
			echo shell_exec($argv[1]);

		usleep($watch_interval);
	}
?>