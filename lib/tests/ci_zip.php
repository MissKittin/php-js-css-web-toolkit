<?php
	/*
	 * ci_zip.php library test
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
		@unlink(__DIR__.'/tmp/ci_zip/archive.zip');
		@unlink(__DIR__.'/tmp/ci_zip/zip.zip');
		@unlink(__DIR__.'/tmp/ci_zip/srcdir/test.txt');
		@rmdir(__DIR__.'/tmp/ci_zip/srcdir');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/ci_zip');
		file_put_contents(__DIR__.'/tmp/ci_zip/src.txt', 'SRCTXT');
		mkdir(__DIR__.'/tmp/ci_zip/srcdir');
		file_put_contents(__DIR__.'/tmp/ci_zip/srcdir/test.txt', 'TEST');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing save';
		file_put_contents(__DIR__.'/tmp/ci_zip/zip.zip', (new ci_zip())
		->	set_compression(3)
		->	add_data('file1.txt', 'File 1')
		->	add_data('file2/file1.txt', 'File 2')
		->	add_data([
				'file3/file1.txt'=>'File 3',
				'file3/file2.txt'=>'File 4'
			])
		->	get_zip());
		if(is_file(__DIR__.'/tmp/ci_zip/zip.zip'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing read';
		if(file_get_contents('phar://'.__DIR__.'/tmp/ci_zip/zip.zip/file1.txt') === 'File 1')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents('phar://'.__DIR__.'/tmp/ci_zip/zip.zip/file2/file1.txt') === 'File 2')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents('phar://'.__DIR__.'/tmp/ci_zip/zip.zip/file3/file1.txt') === 'File 3')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents('phar://'.__DIR__.'/tmp/ci_zip/zip.zip/file3/file2.txt') === 'File 4')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing read_file/read_dir/archive';
		$zip=new ci_zip();
		$zip->set_compression(3);
		$zip->read_file(__DIR__.'/tmp/ci_zip/src.txt', 'dest.txt');
		$zip->read_dir(__DIR__.'/tmp/ci_zip/srcdir', false, strtr(__DIR__, '\\', '/').'/tmp/ci_zip/');
		$zip->archive(__DIR__.'/tmp/ci_zip/archive.zip');
		if(is_file(__DIR__.'/tmp/ci_zip/archive.zip'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents('phar://'.__DIR__.'/tmp/ci_zip/archive.zip/dest.txt') === 'SRCTXT')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents('phar://'.__DIR__.'/tmp/ci_zip/archive.zip/srcdir/test.txt') === 'TEST')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>