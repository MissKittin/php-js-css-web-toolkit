<?php
	/*
	 * sec_file_sign.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  openssl extension is required
	 */

	if(!function_exists('openssl_random_pseudo_bytes'))
	{
		echo 'openssl extension is not loaded'.PHP_EOL;
		exit(1);
	}

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

	$failed=false;

	echo ' -> Removing keys';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/sec_file_sign');
		@unlink(__DIR__.'/tmp/sec_file_sign/sec_file_sign-private.pem');
		@unlink(__DIR__.'/tmp/sec_file_sign/sec_file_sign-public.pem');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Generating keys';
		file_sign::generate_keys([
			'private_key'=>__DIR__.'/tmp/sec_file_sign/sec_file_sign-private.pem',
			'public_key'=>__DIR__.'/tmp/sec_file_sign/sec_file_sign-public.pem'
		]);
	echo ' [ OK ]'.PHP_EOL;

	$filesign=new file_sign([
		'private_key'=>__DIR__.'/tmp/sec_file_sign/sec_file_sign-private.pem',
		'public_key'=>__DIR__.'/tmp/sec_file_sign/sec_file_sign-public.pem'
	]);
	$filesign_verify=new file_sign([
		'public_key'=>__DIR__.'/tmp/sec_file_sign/sec_file_sign-public.pem'
	]);

	echo ' -> Testing input'.PHP_EOL;
		echo '  -> return true';
			$signature=$filesign->generate_input_signature('Message');
			if($filesign_verify->verify_input_signature('Message', $signature))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> return false';
			$signature.='bad';
			if(!$filesign_verify->verify_input_signature('Message', $signature))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing file'.PHP_EOL;
		echo '  -> return true';
			$signature=$filesign->generate_file_signature(__FILE__);
			if($filesign_verify->verify_file_signature(__FILE__, $signature))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> return false';
			$signature.='bad';
			if(!$filesign_verify->verify_file_signature(__FILE__, $signature))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing encrypt/decrypt'.PHP_EOL;
		echo '  -> return true';
			$encrypted=$filesign->encrypt_data('Message');
			if($filesign_verify->decrypt_data($encrypted) === 'Message')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> return false';
			$encrypted.='bad';
			if($filesign_verify->decrypt_data($encrypted) === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing verify-only exception';
		try {
			$filesign_verify->generate_file_signature(__FILE__);
			$exception_caught=false;
		} catch(file_sign_exception $error) {
			$exception_caught=true;
		}
		if($exception_caught === true)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		try {
			$filesign_verify->encrypt_data('Message');
			$exception_caught=false;
		} catch(file_sign_exception $error) {
			$exception_caught=true;
		}
		if($exception_caught === true)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>