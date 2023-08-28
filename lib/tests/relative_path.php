<?php
	/*
	 * relative_path.php library test
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

	$failed=false;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		rmdir_recursive(__DIR__.'/tmp/relative_path');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/relative_path');
		mkdir(__DIR__.'/tmp/relative_path/apache');
		mkdir(__DIR__.'/tmp/relative_path/apache/a');
		file_put_contents(__DIR__.'/tmp/relative_path/apache/a/a.php', '');
		mkdir(__DIR__.'/tmp/relative_path/root');
		mkdir(__DIR__.'/tmp/relative_path/root/b');
		file_put_contents(__DIR__.'/tmp/relative_path/root/b/b.php', '');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing library';
		chdir(__DIR__.'/tmp/relative_path');
		if(relative_path('./apache/a/a.php', './root/b/b.php') === '../../root/b/b.php')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(relative_path('./apache/a/a.php', './root/nonexistent') === false)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		chdir(__DIR__.'/tmp/relative_path/apache');
		if(relative_path('./a/a.php', '../root/b/b.php') === '../../root/b/b.php')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>