<?php
	/*
	 * cli_prompt.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */


	if(!isset($argv[1]))
	{
		echo ' This is not an automatic test'.PHP_EOL;
		echo ' Run this test with a force argument'.PHP_EOL;
		exit(1);
	}
	if($argv[1] !== 'force')
	{
		echo ' This is not an automatic test'.PHP_EOL;
		echo ' Run this test with a force argument'.PHP_EOL;
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

	echo ' -> Type text and press <ENTER>'.PHP_EOL;

	try {
		echo ' cli_getstr -> ';
		$output=cli_getstr();
		echo '  Output: "'.$output.'"'.PHP_EOL;

		echo ' cli_gethstr -> ';
		$output=cli_gethstr();
		echo PHP_EOL.'  Output: "'.$output.'"'.PHP_EOL;

		echo ' cli_getch -> ';
		$output=cli_getch();
		if($output === PHP_EOL)
			echo '  Output: PHP_EOL'.PHP_EOL;
		else
			echo PHP_EOL.'  Output: "'.$output.'"'.PHP_EOL;
	} catch(Throwable $error) {
		echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>