<?php
	/*
	 * Run PHP components tests in batch mode
	 * Looks for files in ../components/${component}/tests directory
	 */

	if(!is_dir(__DIR__.'/../components'))
	{
		echo __DIR__.'/../components directory not found'.PHP_EOL;
		exit(1);
	}

	$failed_tests=[];

	foreach(array_slice(scandir(__DIR__.'/../components'), 2) as $component)
		if(is_dir(__DIR__.'/../components/'.$component.'/tests'))
			foreach(array_slice(scandir(__DIR__.'/../components/'.$component.'/tests'), 2) as $test)
				if(substr($test, strrpos($test, '.')) === '.php')
				{
					echo '-> Running '.$component.'/'.$test.PHP_EOL;

					system(PHP_BINARY.' '.__DIR__.'/../components/'.$component.'/tests/'.$test, $test_result);

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