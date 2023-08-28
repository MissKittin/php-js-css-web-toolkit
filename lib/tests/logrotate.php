<?php
	/*
	 * logrotate.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  rmdir_recursive.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		echo ' -> Mocking functions';
			$GLOBALS['file_exists']=null;
			function file_exists($file)
			{
				if($GLOBALS['file_exists'] === null)
					return \file_exists($file);

				return $GLOBALS['file_exists'];
			}
			$GLOBALS['time']=null;
			function time()
			{
				if($GLOBALS['time'] === null)
					return \time();

				return $GLOBALS['time'];
			}
			$GLOBALS['filemtime']=null;
			function filemtime($file)
			{
				if($GLOBALS['filemtime'] === null)
					return \filemtime($file);

				return $GLOBALS['filemtime'];
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php',
			'rmdir_recursive.php'
		] as $library){
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
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
		}

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
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

		echo ' -> Removing temporary files';
			@mkdir(__DIR__.'/tmp');
			rmdir_recursive(__DIR__.'/tmp/logrotate');
		echo ' [ OK ]'.PHP_EOL;

		function print_log($priority, $message)
		{
			$GLOBALS['print_log_message'][]=$message;
		}
		$failed=false;

		echo ' -> Testing bad configuration output_file';
			$GLOBALS['print_log_message']=[];
			logrotate([
				'/log/testlog.txt'=>[]
			], 'Test\print_log');
			if(
				isset($GLOBALS['print_log_message'][1]) &&
				($GLOBALS['print_log_message'][1] === 'Bad configuration for /log/testlog.txt (output_file) - skipping')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing bad configuration rotate_every[2]';
			$GLOBALS['print_log_message']=[];
			logrotate([
				'/log/testlog.txt'=>[
					'output_file'=>'/log/testlog-rotated.txt',
					'rotate_every'=>[1, 'hours']
				]
			], 'Test\print_log');
			if(
				isset($GLOBALS['print_log_message'][1]) &&
				($GLOBALS['print_log_message'][1] === 'Bad configuration for /log/testlog.txt (rotate_every[2] undefined) - skipping')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing file not exists';
			$GLOBALS['print_log_message']=[];
			$GLOBALS['file_exists']=false;
			logrotate([
				'/log/testlog.txt'=>[
					'output_file'=>'/log/testlog-rotated.txt'
				]
			], 'Test\print_log');
			if(
				isset($GLOBALS['print_log_message'][1]) &&
				($GLOBALS['print_log_message'][1] === '/log/testlog.txt does not exists')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			$GLOBALS['file_exists']=null;

		echo ' -> Testing output_file exists';
			$GLOBALS['file_exists']=true;
			$GLOBALS['print_log_message']=[];
			logrotate([
				'/log/testlog.txt'=>[
					'output_file'=>'/log/testlog-rotated.txt'
				]
			], 'Test\print_log');
			if(
				isset($GLOBALS['print_log_message'][1]) &&
				($GLOBALS['print_log_message'][1] === '/log/testlog-rotated.txt exists - skipping')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			$GLOBALS['file_exists']=null;

		echo ' -> Creating test files';
			mkdir(__DIR__.'/tmp/logrotate');
			mkdir(__DIR__.'/tmp/logrotate/log');
			file_put_contents(__DIR__.'/tmp/logrotate/log/testlog.txt', '0');
			file_put_contents(__DIR__.'/tmp/logrotate/log/testlog-rotated.timestamp', '');
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing min_size not exceeded';
			$GLOBALS['print_log_message']=[];
			logrotate([
				__DIR__.'/tmp/logrotate/log/testlog.txt'=>[
					'output_file'=>__DIR__.'/tmp/logrotate/log/testlog-rotated.txt',
					'min_size'=>[2, 'k']
				]
			], 'Test\print_log');
			if(
				isset($GLOBALS['print_log_message'][1]) &&
				($GLOBALS['print_log_message'][1] === __DIR__.'/tmp/logrotate/log/testlog.txt min_size 2048B not exceeded - skipping')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing rotate_every not exceeded';
			$GLOBALS['time']=1;
			$GLOBALS['filemtime']=2;
			$GLOBALS['print_log_message']=[];
			logrotate([
				__DIR__.'/tmp/logrotate/log/testlog.txt'=>[
					'output_file'=>__DIR__.'/tmp/logrotate/log/testlog-rotated.txt',
					'rotate_every'=>[
						1, 'hours',
						__DIR__.'/tmp/logrotate/log/testlog-rotated.timestamp'
					]
				]
			], 'Test\print_log');
			if(
				isset($GLOBALS['print_log_message'][1]) &&
				($GLOBALS['print_log_message'][1] === __DIR__.'/tmp/logrotate/log/testlog-rotated.timestamp rotate_every not exceeded ([current] 1 < 3602 [timestamp])  - skipping')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			$GLOBALS['time']=null;
			$GLOBALS['filemtime']=null;

		echo ' -> Testing file rotation and compression';
			$GLOBALS['print_log_message']=[];
			logrotate([
				__DIR__.'/tmp/logrotate/log/testlog.txt'=>[
					'output_file'=>__DIR__.'/tmp/logrotate/log/testlog-rotated.txt',
					'gzip'=>'w9'
				]
			], 'Test\print_log');
			foreach([
				1=>'Rotating '.__DIR__.'/tmp/logrotate/log/testlog.txt',
				2=>'Cleaning '.__DIR__.'/tmp/logrotate/log/testlog.txt',
				3=>'Compressing '.__DIR__.'/tmp/logrotate/log/testlog.txt',
				4=>'logrotate finished'
			] as $index=>$message)
				if(
					isset($GLOBALS['print_log_message'][$index]) &&
					($GLOBALS['print_log_message'][$index] === $message)
				)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
			echo PHP_EOL;

		echo ' -> Checking rotated file';
			if(file_get_contents(__DIR__.'/tmp/logrotate/log/testlog.txt') === '')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(file_exists(__DIR__.'/tmp/logrotate/log/testlog-rotated.txt.gz'))
			{
				echo ' [ OK ]';

				if(gzfile(__DIR__.'/tmp/logrotate/log/testlog-rotated.txt.gz')[0] === '0')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		if($failed)
			exit(1);
	}
?>