<?php
	/*
	 * lv-encrypter.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 */

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	$failed=false;

	echo ' -> Generating key';
		$key=shell_exec('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--generate-key '
		.	'--no-eol'
		);
		if(strlen($key) === 24)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing tool';
		$payload=shell_exec('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--encrypt '
		.	'--key "'.$key.'" '
		.	'--content "TEST TEXT" '
		.	'--no-eol'
		);
		$message=shell_exec('"'.PHP_BINARY.'" "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--decrypt '
		.	'--key "'.$key.'" '
		.	'--content "'.$payload.'" '
		.	'--no-eol'
		);
		if(trim($message) === 'TEST TEXT')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>