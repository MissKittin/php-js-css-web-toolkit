<?php
	/*
	 * get-composer.php tool test
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

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@rmdir_recursive(__DIR__.'/tmp/get-composer');
		mkdir(__DIR__.'/tmp/get-composer');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/get-composer/lib');
		copy(__DIR__.'/../../lib/curl_file_updown.php', __DIR__.'/tmp/get-composer/lib/curl_file_updown.php');
		copy(__DIR__.'/../get-composer.php', __DIR__.'/tmp/get-composer/get-composer.php');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system(PHP_BINARY.' '.__DIR__.'/tmp/get-composer/'.basename(__FILE__));
	echo PHP_EOL;

	echo ' -> Testing composer.phar';
		if(is_file(__DIR__.'/tmp/get-composer/composer.phar'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>