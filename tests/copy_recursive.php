<?php
	/*
	 * copy_recursive.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	echo ' -> Including rmdir_recursive.php';
		if(@(include __DIR__.'/../lib/rmdir_recursive.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		foreach([
			'copy_recursive.src',
			'copy_recursive.dest'
		] as $file)
			rmdir_recursive(__DIR__.'/tmp/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	chdir(__DIR__.'/tmp');

	echo ' -> Creating test directory';
		mkdir('./copy_recursive.src');
		file_put_contents('./copy_recursive.src/1', '');
		mkdir('./copy_recursive.src/A');
		file_put_contents('./copy_recursive.src/A/1', '');
		file_put_contents('./copy_recursive.src/A/2', '');
		file_put_contents('./copy_recursive.src/A/3', '');
		mkdir('./copy_recursive.src/B');
		file_put_contents('./copy_recursive.src/B/1', '');
		file_put_contents('./copy_recursive.src/B/2', '');
		file_put_contents('./copy_recursive.src/B/3', '');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Copying';
		if(copy_recursive('./copy_recursive.src', './copy_recursive.dest'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

	$failed=false;

	echo ' -> Checking destination directory';
		$result=[];
		$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./copy_recursive.dest'));
		foreach($iterator as $item)
			$result[strtr($iterator->getSubPathName(), '\\', '/')]=true;

		foreach(['..', '1', 'A/3', 'A/..', 'A/1', 'A/2', 'A/.', 'B/3', 'B/..', 'B/1', 'B/2', 'B/.', '.'] as $item)
			if(!isset($result[$item]))
				$failed=true;

		if($failed)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
		else
			echo ' [ OK ]'.PHP_EOL;
?>