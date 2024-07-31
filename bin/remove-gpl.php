<?php
	// Remove GPL-licensed libraries

	switch(true)
	{
		case (!isset($argv[1])):
		case ($argv[1] !== '--yes'):
			echo 'Refused!'.PHP_EOL;
			echo 'Use '.$argv[0].' --yes'.PHP_EOL;
			exit(1);
	}

	foreach([
		'mem_zip.php',
		'wp_is_serialized.php'
	] as $library){
		echo '[LIBR] '.$library;
			if(file_exists(__DIR__.'/../lib/'.$library))
			{
				if(unlink(__DIR__.'/../lib/'.$library))
					echo ' [ OK ]'.PHP_EOL;
				else
					echo ' [FAIL]'.PHP_EOL;
			}
			else
				echo ' [SKIP]'.PHP_EOL;

		echo '[TEST] '.$library;
			if(file_exists(__DIR__.'/../lib/tests/'.$library))
			{
				if(unlink(__DIR__.'/../lib/tests/'.$library))
					echo ' [ OK ]'.PHP_EOL;
				else
					echo ' [FAIL]'.PHP_EOL;
			}
			else if(file_exists(__DIR__.'/../tests/'.$library))
			{
				if(unlink(__DIR__.'/../tests/'.$library))
					echo ' [ OK ]'.PHP_EOL;
				else
					echo ' [FAIL]'.PHP_EOL;
			}
			else
				echo ' [SKIP]'.PHP_EOL;
	}
?>