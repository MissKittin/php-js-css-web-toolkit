<?php
	/*
	 * Run PHP library tests in batch mode
	 * Looks for files in ../tests directory
	 */

	if(!is_dir(__DIR__.'/../tests'))
	{
		echo __DIR__.'/../tests directory not found'.PHP_EOL;
		exit(1);
	}

	$failed_tests=array();

	foreach(array_slice(scandir(__DIR__.'/../tests'), 2) as $test)
		if(substr($test, strrpos($test, '.')) === '.php')
		{
			echo '-> Running '.$test.PHP_EOL;

			system(PHP_BINARY.' '.__DIR__.'/../tests/'.$test, $test_result);

			if($test_result !== 0)
				$failed_tests[]=$test;

			echo PHP_EOL;
		}

	if(!empty($failed_tests))
	{
		echo PHP_EOL;

		foreach($failed_tests as $test)
			echo 'Test '.$test.' failed'.PHP_EOL;

		exit(1);
	}
?>