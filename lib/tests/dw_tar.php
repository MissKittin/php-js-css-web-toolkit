<?php
	/*
	 * dw_tar.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

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
		@unlink(__DIR__.'/tmp/dw_tar/archive.tar');
		@unlink(__DIR__.'/tmp/dw_tar/tar.tar');
		@unlink(__DIR__.'/tmp/dw_tar/srcdir/test.txt');
		@rmdir(__DIR__.'/tmp/dw_tar/srcdir');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/dw_tar');
		file_put_contents(__DIR__.'/tmp/dw_tar/src.txt', 'SRCTXT');
		mkdir(__DIR__.'/tmp/dw_tar/srcdir');
		file_put_contents(__DIR__.'/tmp/dw_tar/srcdir/test.txt', 'TEST');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing save';
		file_put_contents(__DIR__.'/tmp/dw_tar/tar.tar', (new dw_tar())
		->	add_data('file1.txt', 'File 1')
		->	add_data('file2/file1.txt', 'File 2')
		->	add_file(__DIR__.'/tmp/dw_tar/srcdir', 'destdir')
		->	get_tar());
		(new dw_tar())
		->	add_data('file1.txt', 'File 1')
		->	add_data('file2/file1.txt', 'File 2')
		->	add_file(__DIR__.'/tmp/dw_tar/srcdir', 'destdir')
		->	save_tar(__DIR__.'/tmp/dw_tar/archive.tar');
		if(is_file(__DIR__.'/tmp/dw_tar/tar.tar'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(is_file(__DIR__.'/tmp/dw_tar/archive.tar'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing read';
		foreach(['tar', 'archive'] as $archive)
		{
			if(file_get_contents('phar://'.__DIR__.'/tmp/dw_tar/'.$archive.'.tar/file1.txt') === 'File 1')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(file_get_contents('phar://'.__DIR__.'/tmp/dw_tar/'.$archive.'.tar/file2/file1.txt') === 'File 2')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(file_get_contents('phar://'.__DIR__.'/tmp/dw_tar/'.$archive.'.tar/destdir/test.txt') === 'TEST')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
		}
		echo PHP_EOL;

	if($failed)
		exit(1);
?>