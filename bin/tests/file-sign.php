<?php
	/*
	 * file-sign.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 */

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/file-sign');
		@unlink(__DIR__.'/tmp/file-sign/private-key.pem');
		@unlink(__DIR__.'/tmp/file-sign/public-key.pem');
		@unlink(__DIR__.'/tmp/file-sign/file.sig');
		file_put_contents(__DIR__.'/tmp/file-sign/file.txt', 'good file');
		file_put_contents(__DIR__.'/tmp/file-sign/file-b.txt', 'bad file');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--private "'.__DIR__.'/tmp/file-sign/private-key.pem" '
		.	'--public "'.__DIR__.'/tmp/file-sign/public-key.pem"'
		);
		system('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--private "'.__DIR__.'/tmp/file-sign/private-key.pem" '
		.	'--public "'.__DIR__.'/tmp/file-sign/public-key.pem" '
		.	'--file "'.__DIR__.'/tmp/file-sign/file.txt" '
		.	'--sig "'.__DIR__.'/tmp/file-sign/file.sig"'
		);
	echo PHP_EOL;

	$failed=false;

	echo ' -> Testing good signature: ';
		$exit_code=-1;
		system('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--public "'.__DIR__.'/tmp/file-sign/public-key.pem" '
		.	'--verify '
		.	'--file "'.__DIR__.'/tmp/file-sign/file.txt" '
		.	'--sig "'.__DIR__.'/tmp/file-sign/file.sig"'
		, $exit_code);
		if($exit_code === 0)
			echo '  <- Testing good signature [ OK ]'.PHP_EOL;
		else
		{
			echo '  <- Testing good signature [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing bad signature: ';
		$exit_code=-1;
		system('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--public "'.__DIR__.'/tmp/file-sign/public-key.pem" '
		.	'--verify '
		.	'--file "'.__DIR__.'/tmp/file-sign/file-b.txt" '
		.	'--sig "'.__DIR__.'/tmp/file-sign/file.sig"'
		, $exit_code);
		if($exit_code === 0)
		{
			echo '  <- Testing bad signature [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo '  <- Testing bad signature [ OK ]'.PHP_EOL;

	if($failed)
		exit(1);
?>