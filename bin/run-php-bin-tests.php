<?php
	/*
	 * Run PHP tool tests in batch mode
	 * Looks for files in $argv[1] directory
	 * Looks for files in ./tests directory
	 */

	if(isset($argv[1]))
	{
		if(($argv[1] === '-h') || ($argv[1] === '--help'))
		{
			echo $argv[0].' [path/to/tests-directory]'.PHP_EOL;
			exit();
		}

		if(!is_dir($argv[1]))
		{
			echo $argv[1].' is not a directory'.PHP_EOL;
			exit(1);
		}

		$tests_dir=$argv[1];
	}
	else
	{
		$tests_dir=__DIR__.'/tests';

		if(!is_dir($tests_dir))
		{
			echo $tests_dir.' directory not found'.PHP_EOL;
			exit(1);
		}
	}

	$failed_tests=[];

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