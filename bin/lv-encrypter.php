<?php
	/*
	 * Interface for sec_lv_encrypter.php library
	 *
	 * Warning:
	 * 	check_var.php library is required
	 *  sec_lv_encrypter.php library is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
		{
			if(file_exists(__DIR__.'/lib/'.$library))
			{
				require __DIR__.'/lib/'.$library;
				continue;
			}

			if(file_exists(__DIR__.'/../lib/'.$library))
			{
				require __DIR__.'/../lib/'.$library;
				continue;
			}

			if($required)
				throw new Exception($library.' library not found');
		}
	}

	try {
		load_library([
			'check_var.php',
			'sec_lv_encrypter.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(
		($argc === 1) ||
		(check_argv('--help')) || (check_argv('-h'))
	){
		echo 'Usage:'.PHP_EOL;
		echo ' key generation: '.$argv[0].' --generate-key [--no-eol]'.PHP_EOL;
		echo ' encryption: '.$argv[0].' --encrypt --key KEY [--cipher CIPHER] --content CONTENT [--no-serialize] [--no-eol]'.PHP_EOL;
		echo ' decryption: '.$argv[0].' --decrypt --key KEY [--cipher CIPHER] --content PAYLOAD [--no-serialize] [--no-eol]'.PHP_EOL;
		echo PHP_EOL;
		echo 'Available ciphers:'.PHP_EOL;

		foreach(
			lv_encrypter::supported_ciphers()
			as $cipher=>$a
		){
			echo ' '.$cipher;

			if($cipher === 'aes-128-cbc')
				echo ' (default)';

			echo PHP_EOL;
		}

		exit(1);
	}

	$add_eol=true;
	$cipher=check_argv_next_param('--cipher');
	$key=check_argv_next_param('--key');
	$content=check_argv_next_param('--content');
	$do_serialization=true;

	if($cipher === null)
		$cipher='aes-128-cbc';

	if(check_argv('--generate-key'))
	{
		echo lv_encrypter::generate_key($cipher);
		exit();
	}

	if(check_argv('--encrypt'))
		$action='encrypt';

	if(check_argv('--decrypt'))
		$action='decrypt';

	if(check_argv('--no-serialize'))
		$do_serialization=false;

	if(($key === null) || ($content === null))
	{
		echo 'Error: no key or content/payload specified'.PHP_EOL;
		exit(1);
	}

	try {
		echo (new lv_encrypter($key, $cipher))
		->	$action($content, $do_serialization);

		if(check_argv('--no-eol'))
			echo PHP_EOL;
	} catch(Throwable $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
	}
?>