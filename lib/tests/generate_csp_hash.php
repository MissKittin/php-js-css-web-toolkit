<?php
	/*
	 * generate_csp_hash.php library test
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
		@mkdir(__DIR__.'/tmp/generate_csp_hash');
		@unlink(__DIR__.'/tmp/generate_csp_hash/script.js');
		file_put_contents(__DIR__.'/tmp/generate_csp_hash/script.js', 'alert("ok");');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing generate_csp_hash';
		//echo ' ('.generate_csp_hash('alert("ok");').')';
		if(
			generate_csp_hash('alert("ok");')
			===
			"'sha256-b5MrM3Soe+7WjjhljpCoJRRrV7sMXjrv/Nk+MNnQkC4='"
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing generate_csp_hash_file';
		try {
			//echo ' ('.generate_csp_hash_file(__DIR__.'/tmp/generate_csp_hash/script.js').')';
			if(
				generate_csp_hash_file(__DIR__.'/tmp/generate_csp_hash/script.js')
				===
				"'sha256-b5MrM3Soe+7WjjhljpCoJRRrV7sMXjrv/Nk+MNnQkC4='"
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		} catch(generate_csp_hash_exception $error) {
			echo ' [FAIL] (caught: '.$error->getMessage().')'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>