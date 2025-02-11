<?php
	/*
	 * check-easter-mkcache.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 */

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	echo ' -> Testing syntax'.PHP_EOL.PHP_EOL;
		$exit_code=-1;
		system(
			'"'.PHP_BINARY.'" -l "'.__DIR__.'/../'.basename(__FILE__).'"',
			$exit_code
		);
	echo PHP_EOL;

	echo ' <- Testing syntax';
		if($exit_code === 0)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>