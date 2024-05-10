<?php
	/*
	 * matthiasmullie-minify.php tool test
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
		@rmdir_recursive(__DIR__.'/tmp/matthiasmullie-minify');
		mkdir(__DIR__.'/tmp/matthiasmullie-minify');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/matthiasmullie-minify/lib');
		copy(__DIR__.'/../../lib/check_var.php', __DIR__.'/tmp/matthiasmullie-minify/lib/check_var.php');
		copy(__DIR__.'/../../lib/curl_file_updown.php', __DIR__.'/tmp/matthiasmullie-minify/lib/curl_file_updown.php');
		copy(__DIR__.'/../get-composer.php', __DIR__.'/tmp/matthiasmullie-minify/get-composer.php');
		copy(__DIR__.'/../matthiasmullie-minify.php', __DIR__.'/tmp/matthiasmullie-minify/matthiasmullie-minify.php');

		mkdir(__DIR__.'/tmp/matthiasmullie-minify/assets');
		file_put_contents(__DIR__.'/tmp/matthiasmullie-minify/assets/test.css', ''
			.'body {'."\n"
			.'	color: #ffffff;'."\n"
			.'	background-color: #000000;'."\n"
			.'}'
		);
		file_put_contents(__DIR__.'/tmp/matthiasmullie-minify/assets/test.js', ''
			.'document.addEventListener("DOMContentLoaded", function(){'."\n"
			.'	var test=1;'."\n"
			.'	var testb=2;'."\n"
			.'	console.log(test);'."\n"
			.'	console.log(testb);'."\n"
			.'}, true);'
		);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Downloading composer'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" '.__DIR__.'/tmp/matthiasmullie-minify/get-composer.php');

		if(!file_exists(__DIR__.'/tmp/matthiasmullie-minify/composer.phar'))
		{
			echo PHP_EOL;
			exit(1);
		}
	echo PHP_EOL;

	echo ' -> Downloading matthiasmullie/minify package'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" '.__DIR__.'/tmp/matthiasmullie-minify/composer.phar --optimize-autoloader --no-cache --working-dir='.__DIR__.'/tmp/matthiasmullie-minify require matthiasmullie/minify');

		if(!file_exists(__DIR__.'/tmp/matthiasmullie-minify/vendor/matthiasmullie/minify'))
		{
			echo PHP_EOL;
			exit(1);
		}
	echo PHP_EOL;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" '.__DIR__.'/tmp/matthiasmullie-minify/matthiasmullie-minify.php --dir '.__DIR__.'/tmp/matthiasmullie-minify/assets');
	echo PHP_EOL;

	echo ' -> Testing output files';
		if(file_get_contents(__DIR__.'/tmp/matthiasmullie-minify/assets/test.css') === 'body{color:#fff;background-color:#000}')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents(__DIR__.'/tmp/matthiasmullie-minify/assets/test.js') === 'document.addEventListener("DOMContentLoaded",function(){var test=1;var testb=2;console.log(test);console.log(testb)},!0)')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>