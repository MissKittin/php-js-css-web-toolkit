<?php
	/*
	 * sec_file_sign.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  openssl extension is required
	 */

	if(!extension_loaded('openssl'))
	{
		echo 'openssl extension is not loaded'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Removing keys';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/sec_file_sign-private.pem');
		@unlink(__DIR__.'/tmp/sec_file_sign-public.pem');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Generating keys';
		file_sign::generate_keys([
			'private_key'=>__DIR__.'/tmp/sec_file_sign-private.pem',
			'public_key'=>__DIR__.'/tmp/sec_file_sign-public.pem'
		]);
	echo ' [ OK ]'.PHP_EOL;

	$filesign=new file_sign([
		'private_key'=>__DIR__.'/tmp/sec_file_sign-private.pem',
		'public_key'=>__DIR__.'/tmp/sec_file_sign-public.pem'
	]);

	echo ' -> Testing input'.PHP_EOL;
	echo '  -> return true';
		$signature=$filesign->generate_input_signature('Message');
		if($filesign->verify_input_signature('Message', $signature))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo '  -> return false';
		$signature.='bad';
		if(!$filesign->verify_input_signature('Message', $signature))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing file'.PHP_EOL;
	echo '  -> return true';
		$signature=$filesign->generate_file_signature(__FILE__);
		if($filesign->verify_file_signature(__FILE__, $signature))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo '  -> return false';
		$signature.='bad';
		if(!$filesign->verify_file_signature(__FILE__, $signature))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing encrypt/decrypt'.PHP_EOL;
	echo '  -> return true';
		$encrypted=$filesign->encrypt_data('Message');
		if($filesign->decrypt_data($encrypted) === 'Message')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo '  -> return false';
		$encrypted.='bad';
		if($filesign->decrypt_data($encrypted) === false)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>