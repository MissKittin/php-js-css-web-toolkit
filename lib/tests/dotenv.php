<?php
	/*
	 * dotenv.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	function test_variable($env, $variable, $value, &$failed, $server_fail=false)
	{
		echo '   -> $env->getenv()';
			if($env->getenv($variable, 'fail') === $value)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo '   -> getenv()';
			if(getenv($variable) === $value)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo '   -> $_ENV';
			if(
				isset($_ENV[$variable]) &&
				($_ENV[$variable] === $value)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo '   -> $_SERVER';
			if($server_fail)
			{
				if(
					isset($_SERVER[$variable]) &&
					($_SERVER[$variable] === $value)
				){
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;

				return;
			}
			if(
				isset($_SERVER[$variable]) &&
				($_SERVER[$variable] === $value)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
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

	echo ' -> Creating dotenv.env';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/dotenv');
		file_put_contents(__DIR__.'/tmp/dotenv/dotenv.env', ''
		.	'TESTVARA="ok ok"'."\n"
		.	'TESTVARB = "ok ok"'."\n"
		.	'TESTVARC=\'ok ok\''."\n"
		.	'TESTVARD=\'${TESTVARA}\''."\n"
		.	'TESTVARE="${TESTVARA}"'."\n"
		.	'TESTVARF=${TESTVARA}'."\n"
		.	'HTTP_TESTVAR=\'failed\''."\n"
		.	'HTTPS=\'on\''."\n"
		.	'PHP_SELF=\'ok ok\''."\n"
		);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;
	$env=new dotenv(
		__DIR__.'/tmp/dotenv/dotenv.env',
		[
			'call_getenv'=>false,
			'substitute_variables'=>true,
			'seed_putenv'=>true,
			'seed_env'=>true,
			'seed_server'=>true,
			'override_server'=>false
		]
	);

	echo ' -> Testing library'.PHP_EOL;
		echo '  -> VAR = "val"'.PHP_EOL;
			test_variable($env, 'TESTVARA', 'ok ok', $failed);
		echo '  -> VAR="val"'.PHP_EOL;
			test_variable($env, 'TESTVARB', 'ok ok', $failed);
		echo '  -> VAR=\'val\''.PHP_EOL;
			test_variable($env, 'TESTVARC', 'ok ok', $failed);
		echo '  -> VAR=\'${VARB}\''.PHP_EOL;
			test_variable($env, 'TESTVARD', '${TESTVARA}', $failed);
		echo '  -> VAR="${VARB}"'.PHP_EOL;
			test_variable($env, 'TESTVARE', 'ok ok', $failed);
		echo '  -> VAR=${VARB}'.PHP_EOL;
			test_variable($env, 'TESTVARF', 'ok ok', $failed);
		echo '  -> HTTP_VAR=\'val\''.PHP_EOL;
			test_variable($env, 'HTTP_TESTVAR', 'failed', $failed, true);
		echo '  -> HTTPS=\'on\''.PHP_EOL;
			test_variable($env, 'HTTPS', 'on', $failed, true);
		echo '  -> nonexistent variable'.PHP_EOL;
			$nonexistent='3hjtrb89tgj7fxuzx8az516c6gbw4b';
			echo '   -> $env->getenv()';
				if($env->getenv($nonexistent, 'fail') === 'fail')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '   -> getenv';
				if(getenv($nonexistent) === false)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '   -> $_ENV';
				if(isset($_ENV[$nonexistent]))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '   -> $_SERVER';
				if(isset($_SERVER[$nonexistent]))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
		echo '  -> call_getenv false';
			if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				$path_env='Path';
			else
				$path_env='PATH';
			if($env->getenv($path_env, 'fail') === 'fail')
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> call_getenv true';
			putenv('TEST_CALL_GETENV=ok');
			$env=new dotenv(
				__DIR__.'/tmp/dotenv/dotenv.env',
				[
					'call_getenv'=>true,
					'substitute_variables'=>true,
					'seed_putenv'=>true,
					'seed_env'=>true,
					'seed_server'=>true,
					'override_server'=>false
				]
			);
			if($env->getenv('TEST_CALL_GETENV', 'fail') === 'ok')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> override_server false';
			if($_SERVER['PHP_SELF'] === 'ok ok')
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> override_server true';
			$env=new dotenv(
				__DIR__.'/tmp/dotenv/dotenv.env',
				[
					'call_getenv'=>false,
					'substitute_variables'=>true,
					'seed_putenv'=>true,
					'seed_env'=>true,
					'seed_server'=>true,
					'override_server'=>true
				]
			);
			if($env->getenv('PHP_SELF', 'fail') === 'ok ok')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>