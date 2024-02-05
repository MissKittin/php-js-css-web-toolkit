<?php
	/*
	 * Run PHP components tests in batch mode
	 * Looks for files in ../com/${component}/tests directory
	 */

	if(!is_dir(__DIR__.'/../com'))
	{
		echo __DIR__.'/../com directory not found'.PHP_EOL;
		exit(1);
	}

	$failed_tests=[];

	foreach(array_slice(scandir(__DIR__.'/../com'), 2) as $component)
		if(is_dir(__DIR__.'/../com/'.$component.'/tests'))
			foreach(array_slice(scandir(__DIR__.'/../com/'.$component.'/tests'), 2) as $test)
				if(substr($test, strrpos($test, '.')) === '.php')
				{
					echo '-> Running '.$component.'/'.$test.PHP_EOL;

					system(PHP_BINARY.' '.__DIR__.'/../com/'.$component.'/tests/'.$test, $test_result);

					if($test_result !== 0)
						$failed_tests[]=$component.'/'.$test;

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