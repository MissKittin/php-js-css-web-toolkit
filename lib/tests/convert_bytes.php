<?php
	/*
	 * convert_bytes.php library test
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

	$failed=false;

	echo ' -> Testing library'.PHP_EOL;
		$value=512;
		foreach(['B', 'kB', 'MB', 'GB', 'TB', 'PB', '?B'] as $unit)
		{
			echo '  -> '.$unit;

			if(convert_bytes($value) === '512'.$unit)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

			$value*=1024;
		}

	if($failed)
		exit(1);
?>