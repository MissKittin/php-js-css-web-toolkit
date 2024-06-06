<?php
	if(!is_file(__DIR__.'/../main_original.php'))
	{
		echo 'Run mkcache.php tool first'.PHP_EOL;
		exit(1);
	}

	if(!isset($argv[1]))
	{
		echo basename(__FILE__).' --yes'.PHP_EOL;
		exit(1);
	}

	if($argv[1] !== '--yes')
	{
		echo basename(__FILE__).' --yes'.PHP_EOL;
		exit(1);
	}

	foreach(scandir(__DIR__.'/..') as $file)
		if(is_file(__DIR__.'/../'.$file))
		{
			echo '[RM] '.$file;

			if($file === 'main.php')
				echo ' [SKIP]'.PHP_EOL;
			else if(unlink(__DIR__.'/../'.$file))
				echo ' [ OK ]'.PHP_EOL;
			else
				echo ' [FAIL]'.PHP_EOL;
		}

	foreach(scandir(__DIR__) as $file)
		if(is_file(__DIR__.'/'.$file))
		{
			echo '[RM] bin/'.$file;

			if(unlink(__DIR__.'/'.$file))
				echo ' [ OK ]'.PHP_EOL;
			else
				echo ' [FAIL]'.PHP_EOL;
		}

	rmdir(__DIR__);
?>