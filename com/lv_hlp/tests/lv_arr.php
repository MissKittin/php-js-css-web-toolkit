<?php
	/*
	 * Tests all lv_hlp collections' methods
	 */

	echo ' -> Including lv_hlp.php';
		try {
			if(@(include __DIR__.'/../lv_hlp.php') === false)
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

	echo ' -> Mocking lv_arr.php test';
		$lv_helpers_skip=true;
		$lv_collection_header='lv_hlp_collection';
		$lv_collect_function='lv_hlp_collect';
		$lv_lazy_collection_header='lv_hlp_lazy_collection';
		$lv_lazy_collect_function='lv_hlp_lazy_collect';
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Starting lv_arr.php library test';
		if(file_exists(__DIR__.'/../lib/tests/lv_arr.php'))
		{
			echo ' [ OK ]'.PHP_EOL;
			require __DIR__.'/../lib/tests/lv_arr.php';
		}
		else if(file_exists(__DIR__.'/../../../lib/tests/lv_arr.php'))
		{
			echo ' [ OK ]'.PHP_EOL;
			require __DIR__.'/../../../lib/tests/lv_arr.php';
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>