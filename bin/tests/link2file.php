<?php
	/*
	 * link2file.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../../lib/rmdir_recursive.php') === false)
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
	$test_link2file=true;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@rmdir_recursive(__DIR__.'/tmp/link2file');
		mkdir(__DIR__.'/tmp/link2file');
		mkdir(__DIR__.'/tmp/link2file/src');
		file_put_contents(__DIR__.'/tmp/link2file/src/file', 'srcfile');
		mkdir(__DIR__.'/tmp/link2file/src/dir');
		file_put_contents(__DIR__.'/tmp/link2file/src/dir/file', 'srcdirfile');
		mkdir(__DIR__.'/tmp/link2file/dest');
		file_put_contents(__DIR__.'/tmp/link2file/dest/file', 'destfile');
		mkdir(__DIR__.'/tmp/link2file/dest/dir');
		file_put_contents(__DIR__.'/tmp/link2file/dest/dir/file', 'destdirfile');

		if(!@symlink('../src/file', __DIR__.'/tmp/link2file/dest/lfile'))
			$test_link2file=false;
		if(!@symlink('../src/dir', __DIR__.'/tmp/link2file/dest/ldir'))
			$test_link2file=false;
	echo ' [ OK ]'.PHP_EOL;

	if(!$test_link2file)
	{
		echo ' -> Testing tool [SKIP]'.PHP_EOL;
		exit();
	}

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'"'.__DIR__.'/tmp/link2file/dest"'
		);
	echo PHP_EOL;

	echo ' -> Testing tool';
		if(is_link(__DIR__.'/tmp/link2file/dest/ldir'))
		{
			echo ' [FAIL]';
			$failed=true;
		}
		else
			echo ' [ OK ]';
		if(is_link(__DIR__.'/tmp/link2file/dest/lfile'))
		{
			echo ' [FAIL]';
			$failed=true;
		}
		else
			echo ' [ OK ]';
		if(file_get_contents(__DIR__.'/tmp/link2file/dest/lfile') === 'srcfile')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents(__DIR__.'/tmp/link2file/dest/ldir/file') === 'srcdirfile')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>