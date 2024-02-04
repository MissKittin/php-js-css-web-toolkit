<?php
	/*
	 * zip.php library test
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
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/zip.zip');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing save';
		$zip=new ZipFile();
		$zip->addFile('File 1', 'file1.txt');
		$zip->addFile('File 2', 'file2/file1.txt');
		file_put_contents(__DIR__.'/tmp/zip.zip', $zip->file());
		if(is_file(__DIR__.'/tmp/zip.zip'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing read';
		if(file_get_contents('phar://'.__DIR__.'/tmp/zip.zip/file1.txt') === 'File 1')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents('phar://'.__DIR__.'/tmp/zip.zip/file2/file1.txt') === 'File 2')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>