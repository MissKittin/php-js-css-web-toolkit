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
			if(file_exists(__DIR__.'/lib/'.$library))
				require __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				require __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
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

	if(($argc === 1) || (check_argv('--help')) || (check_argv('-h')))
	{
		echo 'Usage:'.PHP_EOL;
		echo ' key generation: --generate-key [--no-eol]'.PHP_EOL;
		echo ' encryption: --encrypt --key KEY [--cipher CIPHER] --content CONTENT [--no-serialize] [--no-eol]'.PHP_EOL;
		echo ' decryption: --decrypt --key KEY [--cipher CIPHER] --content PAYLOAD [--no-serialize] [--no-eol]'.PHP_EOL;
		echo PHP_EOL;
		echo 'Available ciphers: aes-128-cbc aes-256-cbc aes-128-gcm aes-256-gcm'.PHP_EOL;
		echo ' default cipher: aes-128-cbc'.PHP_EOL;
		exit(1);
	}

	$add_eol=true;
	if(check_argv('--no-eol'))
		$add_eol=false;
	register_shutdown_function(function(){
		global $add_eol;
		if($add_eol)
			echo PHP_EOL;
	});

	$cipher=check_argv_next_param('--cipher');
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

	$key=check_argv_next_param('--key');

	$content=check_argv_next_param('--content');

	$do_serialization=true;
	if(check_argv('--no-serialize'))
		$do_serialization=false;

	if(($key === null) || ($content === null))
	{
		echo 'Error: no key or content/payload specified';
		exit(1);
	}

	try {
		$encrypter=new lv_encrypter($key, $cipher);
		echo $encrypter->$action($content, $do_serialization);
	} catch(Throwable $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
	}
?>