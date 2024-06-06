<?php
	/*
	 * Tests all lv_hlp_ingable methods
	 */

	echo ' -> Including main.php';
		try {
			if(@(include __DIR__.'/../main.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'
				.PHP_EOL.PHP_EOL
				.'Caught: '.$error->getMessage()
				.PHP_EOL;

			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Mocking lv_str.php test';
		$lv_helpers_skip=true;
		$lv_stringable_header='lv_hlp_ingable';
		$lv_stringable_function='lv_hlp_of';
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Starting lv_str.php library test';
		if(file_exists(__DIR__.'/../lib/tests/lv_str.php'))
		{
			echo ' [ OK ]'.PHP_EOL;
			require __DIR__.'/../lib/tests/lv_str.php';
		}
		else if(file_exists(__DIR__.'/../../../lib/tests/lv_str.php'))
		{
			echo ' [ OK ]'.PHP_EOL;
			require __DIR__.'/../../../lib/tests/lv_str.php';
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>