<?php
	/*
	 * curl_file_updown.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  curl extension is required
	 *  rmdir_recursive.php library is required
	 */

	if(!extension_loaded('curl'))
	{
		echo 'curl extension is not loaded'.PHP_EOL;
		exit(1);
	}

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

	if(isset($argv[1]) && ($argv[1] === 'serve'))
	{
		echo ' -> Removing temporary files';
			rmdir_recursive(__DIR__.'/tmp/curl_file_updown');
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Creating server test directory';
			@mkdir(__DIR__.'/tmp');
			mkdir(__DIR__.'/tmp/curl_file_updown');
			mkdir(__DIR__.'/tmp/curl_file_updown/server');
			file_put_contents(__DIR__.'/tmp/curl_file_updown/server/file-to-be-downloaded.txt', 'download me');
			file_put_contents(
				__DIR__.'/tmp/curl_file_updown/server/upload.php',
				'<?php move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], __DIR__."/".$_FILES["fileToUpload"]["name"]); ?>'
			);
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Starting PHP server...'.PHP_EOL.PHP_EOL;
		chdir(__DIR__.'/tmp/curl_file_updown/server');
		system(PHP_BINARY.' -S 127.0.0.1:8080');

		exit();
	}

	if(!file_exists(__DIR__.'/tmp/curl_file_updown'))
	{
		echo 'Run tests/curl_file_updown.php serve'.PHP_EOL;
		exit(1);
	}

	echo ' -> Creating client test directory';
			@mkdir(__DIR__.'/tmp/curl_file_updown/client');
			file_put_contents(__DIR__.'/tmp/curl_file_updown/client/file-to-be-uploaded.txt', 'upload me');
		echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing http curl_file_upload';
		curl_file_upload(
			'http://127.0.0.1:8080/upload.php',
			__DIR__.'/tmp/curl_file_updown/client/file-to-be-uploaded.txt',
			['post_field_name'=>'fileToUpload']
		);
		if(
			file_exists(__DIR__.'/tmp/curl_file_updown/server/file-to-be-uploaded.txt') &&
			(file_get_contents(__DIR__.'/tmp/curl_file_updown/server/file-to-be-uploaded.txt') === 'upload me')
		)
		{
			echo ' [ OK ]'.PHP_EOL;
			unlink(__DIR__.'/tmp/curl_file_updown/server/file-to-be-uploaded.txt');
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing http curl_file_download';
		curl_file_download(
			'http://127.0.0.1:8080/file-to-be-downloaded.txt',
			__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt'
		);
		if(
			file_exists(__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt') &&
			(file_get_contents(__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt') === 'download me')
		){
			echo ' [ OK ]'.PHP_EOL;
			unlink(__DIR__.'/tmp/curl_file_updown/client/file-to-be-downloaded.txt');
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>