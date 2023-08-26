<?php
	/*
	 * Tool for generating key pairs and (verifying) file signatures
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  sec_file_sign.php library is required
	 *  openssl extension is required
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
			'sec_file_sign.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$private_key=check_argv_next_param('--private');
	$public_key=check_argv_next_param('--public');
	$key_bits=check_argv_next_param('--key-bits');
	$input_file=check_argv_next_param('--file');
	$signature_algorithm=check_argv_next_param('--algorithm');

	switch(check_argv_next_param('--key-type'))
	{
		case 'dsa':
			$key_type=OPENSSL_KEYTYPE_DSA;
		break;
		case 'dh':
			$key_type=OPENSSL_KEYTYPE_DH;
		break;
		case 'ec':
			$key_type=OPENSSL_KEYTYPE_EC;
		break;
		case 'rsa':
		default:
			$key_type=OPENSSL_KEYTYPE_RSA;
	}
	if($key_bits === null)
		$key_bits=2048;
	if($signature_algorithm === null)
		$signature_algorithm='sha256WithRSAEncryption';

	if(
		($private_key === null) ||
		($public_key === null) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Generate key pair:'.PHP_EOL;
		echo ' --private path/to/private-key.pem --public path/to/public-key.pem [--key-bits=2048] [--key-type rsa]'.PHP_EOL;
		echo ' available key types: dsa dh rsa ec'.PHP_EOL;
		echo PHP_EOL;
		echo 'Generate signature:'.PHP_EOL;
		echo ' --private path/to/private-key.pem --public path/to/public-key.pem --file path/to/file [--algorithm sha256WithRSAEncryption]'.PHP_EOL;
		echo ' the generated signature will be printed on the stdout'.PHP_EOL;
		echo PHP_EOL;
		echo 'Verify signature:'.PHP_EOL;
		echo ' --private path/to/private-key.pem --public path/to/public-key.pem --verify --file path/to/file [--algorithm sha256WithRSAEncryption]'.PHP_EOL;
		echo ' expects a signature on stdin'.PHP_EOL;
		echo ' also exits with code 1 if the signature is bad'.PHP_EOL;
		exit(1);
	}

	try
	{
		if(check_argv('--verify'))
		{
			if($input_file === null)
				throw new Exception('No file name given');

			if(!
				(new file_sign([
					'private_key'=>$private_key,
					'public_key'=>$public_key,
					'signature_algorithm'=>$signature_algorithm
				]))->verify_file_signature($input_file, file_get_contents('php://stdin'))
			){
				echo 'Bad signature'.PHP_EOL;
				exit(1);
			}

			echo 'Good signature'.PHP_EOL;
		}
		else if($input_file !== null)
		{
			echo (new file_sign([
				'private_key'=>$private_key,
				'public_key'=>$public_key,
				'signature_algorithm'=>$signature_algorithm
			]))->generate_file_signature($input_file);
		}
		else
			file_sign::generate_keys([
				'private_key'=>$private_key,
				'public_key'=>$public_key,
				'key_bits'=>$key_bits,
				'key_type'=>$key_type
			]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage();
		exit(1);
	}
?>