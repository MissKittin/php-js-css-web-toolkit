<?php
	/*
	 * copy_recursive.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../lib/rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/copy_recursive');
		foreach(['src', 'dest'] as $file)
			rmdir_recursive(__DIR__.'/tmp/copy_recursive/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	chdir(__DIR__.'/tmp/copy_recursive');

	echo ' -> Creating test directory';
		mkdir('./src');
		file_put_contents('./src/1', '');
		mkdir('./src/A');
		file_put_contents('./src/A/1', '');
		file_put_contents('./src/A/2', '');
		file_put_contents('./src/A/3', '');
		mkdir('./src/B');
		file_put_contents('./src/B/1', '');
		file_put_contents('./src/B/2', '');
		file_put_contents('./src/B/3', '');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Copying';
		if(copy_recursive('./src', './dest'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

	$failed=false;

	echo ' -> Checking destination directory';
		$result=[];
		$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./dest'));
		foreach($iterator as $item)
			$result[strtr($iterator->getSubPathName(), '\\', '/')]=true;

		foreach(['..', '1', 'A/3', 'A/..', 'A/1', 'A/2', 'A/.', 'B/3', 'B/..', 'B/1', 'B/2', 'B/.', '.'] as $item)
			if(isset($result[$item]))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
				break;
			}
			echo PHP_EOL;

	if($failed)
		exit(1);
?>