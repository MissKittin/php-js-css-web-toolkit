<?php
	/*
	 * dotenv.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

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

	echo ' -> Creating dotenv.env';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/dotenv');
		file_put_contents(__DIR__.'/tmp/dotenv/dotenv.env', 'TESTVARA="ok ok"'.PHP_EOL.'TESTVARB = "ok ok"'.PHP_EOL.'TESTVARC=\'ok ok\''.PHP_EOL);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;
	$env=new dotenv(__DIR__.'/tmp/dotenv/dotenv.env', false);

	echo ' -> Testing library';
		if($env->getenv('TESTVARA', 'fail') === 'ok ok')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}

		if($env->getenv('TESTVARB', 'fail') === 'ok ok')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}

		if($env->getenv('TESTVARC', 'fail') === 'ok ok')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}

		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$path_env='Path';
		else
			$path_env='PATH';
		if($env->getenv($path_env, 'fail') !== 'fail')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}

		if($env->getenv('NONEXISTENT', 'fail') === 'fail')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>