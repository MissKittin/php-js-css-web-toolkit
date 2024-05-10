<?php
	/*
	 * cli_prompt.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	if(!isset($argv[1]))
	{
		if(!function_exists('proc_open'))
		{
			echo 'proc_open function is not available';
			exit(1);
		}

		echo ' -> Starting automatic test'.PHP_EOL;

		$process_pipes=null;
		$process_handler=proc_open(
			'"'.PHP_BINARY.'" '.$argv[0].' force',
			[
				0=>['pty'],
				1=>['pty'],
				2=>['pty']
			],
			$process_pipes,
			getcwd(),
			getenv()
		);

		sleep(1);

		if(!is_resource($process_handler))
		{
			echo 'Error: Process cannot be started'.PHP_EOL;
			echo ' Run this test with a force argument'.PHP_EOL;
			exit(1);
		}

		foreach($process_pipes as $pipe)
			stream_set_blocking($pipe, false);

		$failed=false;

		echo ' -> Writing for cli_getstr'.PHP_EOL;
			@fwrite($process_pipes[0], 'test string'.PHP_EOL);
			sleep(1);

		echo ' -> Writing for cli_gethstr'.PHP_EOL;
			@fwrite($process_pipes[0], 'test hidden string'.PHP_EOL);
			sleep(1);

		echo ' -> Writing for cli_getch'.PHP_EOL;
			@fwrite($process_pipes[0], 'c'.PHP_EOL);
			sleep(1);

		fclose($process_pipes[0]);

		for($i=0; $i<=1; ++$i)
			@fgets($process_pipes[1]);

		echo ' -> Testing cli_getstr';
			@fgets($process_pipes[1]);
			if(trim(fgets($process_pipes[1])) === 'Output: "test string"')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing cli_gethstr';
			@fgets($process_pipes[1]);
			if(trim(fgets($process_pipes[1])) === 'Output: "test hidden string"')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing cli_getch';
			@fgets($process_pipes[1]);
			if(trim(fgets($process_pipes[1])) === 'Output: "c"')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Stopping test server'.PHP_EOL;
		fclose($process_pipes[1]);
		fclose($process_pipes[2]);
		proc_terminate($process_handler);
		proc_close($process_handler);

		if($failed)
			exit(1);

		exit();
	}
	if($argv[1] !== 'force')
	{
		echo ' This is not an automatic test'.PHP_EOL;
		echo ' Run this test with a force argument'.PHP_EOL;
		exit(1);
	}

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

	echo ' -> Type text and press <ENTER>'.PHP_EOL;

	try {
		echo ' cli_getstr -> ';
		$output=cli_getstr();
		echo '  Output: "'.$output.'"'.PHP_EOL;

		echo ' cli_gethstr -> ';
		$output=cli_gethstr();
		echo PHP_EOL.'  Output: "'.$output.'"'.PHP_EOL;

		echo ' cli_getch -> ';
		$output=cli_getch();
		if($output === PHP_EOL)
			echo '  Output: PHP_EOL'.PHP_EOL;
		else
			echo PHP_EOL.'  Output: "'.$output.'"'.PHP_EOL;
	} catch(Throwable $error) {
		echo PHP_EOL.'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>