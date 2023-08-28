<?php
	/*
	 * Run PHP library tests in batch mode
	 * Looks for files in ../lib/tests directory
	 * Looks for files in ../tests directory
	 */

	if(is_dir(__DIR__.'/../lib/tests'))
		$tests_dir=__DIR__.'/../lib/tests';
	else if(is_dir(__DIR__.'/../tests'))
		$tests_dir=__DIR__.'/../tests';
	else
	{
		echo __DIR__.'/../lib/tests directory not found'.PHP_EOL;
		echo __DIR__.'/../tests directory not found'.PHP_EOL;
		exit(1);
	}

	$failed_tests=[];

	echo ' -> Directory: '.$tests_dir.PHP_EOL;
	foreach(array_slice(scandir($tests_dir), 2) as $test)
		if(substr($test, strrpos($test, '.')) === '.php')
		{
			echo '-> Running '.$test.PHP_EOL;

			system(PHP_BINARY.' '.$tests_dir.'/'.$test, $test_result);

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