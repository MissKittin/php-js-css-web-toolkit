<?php
	/*
	 * rmdir_recursive.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	if(file_exists(__DIR__.'/tmp/rmdir_recursive'))
	{
		echo 'Remove tmp/rmdir_recursive first'.PHP_EOL;
		exit(1);
	}

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

	echo ' -> Creating test directory';
		@mkdir(__DIR__.'/tmp');
		mkdir(__DIR__.'/tmp/rmdir_recursive');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/1', '');
		mkdir(__DIR__.'/tmp/rmdir_recursive/A');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/A/1', '');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/A/2', '');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/A/3', '');
		mkdir(__DIR__.'/tmp/rmdir_recursive/B');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/B/1', '');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/B/2', '');
		file_put_contents(__DIR__.'/tmp/rmdir_recursive/B/3', '');

		if(is_dir(__DIR__.'/tmp/rmdir_recursive'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

	echo ' -> Testing library';
		if(rmdir_recursive(__DIR__.'/tmp/rmdir_recursive'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_exists(__DIR__.'/tmp/rmdir_recursive'))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;

	if($failed)
		exit(1);
?>