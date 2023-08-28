<?php
	/*
	 * cron.php library test
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
		function _test_cron($date_array, $input_array)
		{
			$success=true;

			foreach($input_array as $hash=>$alias)
				foreach(['a', 'b'] as $job) // two jobs per hash
					$GLOBALS['job__'.$hash.'_job_'.$job]=false;

			$GLOBALS['mocked_date']=$date_array;

			cron(['crontab'=>__DIR__.'/tmp/cron/crontab']);

			foreach($input_array as $hash=>$alias)
			{
				echo '   -> '.$alias[0];
				if($alias[1])
					echo ' (true) ';
				else
					echo ' (false)';

				foreach(['a', 'b'] as $job)
				{
					if(!$alias[1])
						$GLOBALS['job__'.$hash.'_job_'.$job]=!$GLOBALS['job__'.$hash.'_job_'.$job];

					if($GLOBALS['job__'.$hash.'_job_'.$job])
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$success=false;
					}
				}

				echo PHP_EOL;
			}

			return $success;
		}
		function _test_cron_closure($date_array, $input_array)
		{
			$GLOBALS['_test_cron_closure_jobs']=[];
			$success=true;

			$cron_closure=new cron_closure();

			foreach($input_array as $hash=>$alias)
			{
				$cron_closure->add($hash, function() use($hash){
					$GLOBALS['_test_cron_closure_jobs'][$hash]['result']=true;
				});
					$GLOBALS['_test_cron_closure_jobs'][$hash]['alias']=$alias[0];
				$GLOBALS['_test_cron_closure_jobs'][$hash]['expected_result']=$alias[1];
				$GLOBALS['_test_cron_closure_jobs'][$hash]['result']=false;
			}

			$GLOBALS['mocked_date']=$date_array;

			$cron_closure->run();

			foreach($GLOBALS['_test_cron_closure_jobs'] as $hash=>$params)
			{
				echo '   -> '.$params['alias'];
				if($params['expected_result'])
					echo ' (true) ';
				else
					echo ' (false)';

				if($params['result'] === $params['expected_result'])
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$success=false;
				}

				echo PHP_EOL;
			}

			return $success;
		}

		echo ' -> Mocking functions';
			function date($param)
			{
				switch($param)
				{
					case 'i':
						return $GLOBALS['mocked_date'][0];
					case 'G':
						return $GLOBALS['mocked_date'][1];
					case 'j':
						return $GLOBALS['mocked_date'][2];
					case 'n':
						return $GLOBALS['mocked_date'][3];
					case 'w':
						return $GLOBALS['mocked_date'][4];
				}
			}
			function time()
			{
				static $timestamp=12345;
				$timestamp+=11111;

				return $timestamp;
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
			rmdir_recursive(__DIR__.'/tmp/cron');
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Creating test directory';
			@mkdir(__DIR__.'/tmp');
			mkdir(__DIR__.'/tmp/cron');
			mkdir(__DIR__.'/tmp/cron/crontab');
			foreach([
				'0_0_1_1_-',
				'0_0_1_-_-',
				'0_0_-_-_0',
				'0_0_-_-_-',
				'0_-_-_-_-',
				'-_-_-_-_-',
				'32_15_26_10_-'
			] as $hash){
				mkdir(__DIR__.'/tmp/cron/crontab/'.$hash);

				foreach(['a', 'b'] as $job)
					file_put_contents(__DIR__.'/tmp/cron/crontab/'.$hash.'/job-'.$job.'.php', '<?php $GLOBALS["job__'.$hash.'_job_'.$job.'"]=true; ?>');
			}
			mkdir(__DIR__.'/tmp/cron/timestamp.d');
			file_put_contents(__DIR__.'/tmp/cron/timestamp.d/23456_test.php', '<?php $GLOBALS["task__test_23456"]=true; ?>');
			file_put_contents(__DIR__.'/tmp/cron/timestamp.d/34567_test.php', '<?php $GLOBALS["task__test_34567"]=true; ?>');
		echo ' [ OK ]'.PHP_EOL;

		$errors=[];

		echo ' -> Testing cron'.PHP_EOL;
		echo '  -> test 1'.PHP_EOL;
			if(!_test_cron([0,0,1,1,0], [
				'0_0_1_1_-'=>['yearly ', true],
				'0_0_1_-_-'=>['monthly', true],
				'0_0_-_-_0'=>['weekly ', true],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron test 1';
		echo '  -> test 2'.PHP_EOL;
			if(!_test_cron([0,0,1,0,0], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', true],
				'0_0_-_-_0'=>['weekly ', true],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron test 2';
		echo '  -> test 3'.PHP_EOL;
			if(!_test_cron([0,0,0,0,0], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', true],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron test 3';
		echo '  -> test 4'.PHP_EOL;
			if(!_test_cron([0,0,9,9,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron test 4';
		echo '  -> test 5'.PHP_EOL;
			if(!_test_cron([0,9,9,9,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', false],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron test 5';
		echo '  -> test 6'.PHP_EOL;
			if(!_test_cron([9,9,9,9,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', false],
				'0_-_-_-_-'=>['hourly ', false],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron test 6';
		echo '  -> test 7'.PHP_EOL;
			if(!_test_cron([32,15,26,10,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', false],
				'0_-_-_-_-'=>['hourly ', false],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', true]
			]))
				$errors[]='cron test 7';

		echo ' -> Testing cron_timestamp'.PHP_EOL;
			foreach([23456, 34567] as $timestamp)
			{
				echo '  -> for timestamp '.$timestamp;

				$GLOBALS['task__test_'.$timestamp]=false;
				cron_timestamp(['tasks'=>__DIR__.'/tmp/cron/timestamp.d']);
				if($GLOBALS['task__test_'.$timestamp])
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='cron_timestamp timestamp '.$timestamp.' phase 1';
				}
				if(file_exists(__DIR__.'/tmp/cron_timestamp.d/'.$timestamp.'_test.php'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='cron_timestamp timestamp '.$timestamp.' phase 2';
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			}

		echo ' -> Testing cron_closure'.PHP_EOL;
				echo '  -> test 1'.PHP_EOL;
			if(!_test_cron_closure([0,0,1,1,0], [
				'0_0_1_1_-'=>['yearly ', true],
				'0_0_1_-_-'=>['monthly', true],
				'0_0_-_-_0'=>['weekly ', true],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron_closure test 1';
		echo '  -> test 2'.PHP_EOL;
			if(!_test_cron_closure([0,0,1,0,0], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', true],
				'0_0_-_-_0'=>['weekly ', true],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron_closure test 2';
		echo '  -> test 3'.PHP_EOL;
			if(!_test_cron_closure([0,0,0,0,0], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', true],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron_closure test 3';
		echo '  -> test 4'.PHP_EOL;
			if(!_test_cron_closure([0,0,9,9,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', true],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron_closure test 4';
		echo '  -> test 5'.PHP_EOL;
			if(!_test_cron_closure([0,9,9,9,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', false],
				'0_-_-_-_-'=>['hourly ', true],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron_closure test 5';
		echo '  -> test 6'.PHP_EOL;
			if(!_test_cron_closure([9,9,9,9,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', false],
				'0_-_-_-_-'=>['hourly ', false],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', false]
			]))
				$errors[]='cron_closure test 6';
		echo '  -> test 7'.PHP_EOL;
			if(!_test_cron_closure([32,15,26,10,9], [
				'0_0_1_1_-'=>['yearly ', false],
				'0_0_1_-_-'=>['monthly', false],
				'0_0_-_-_0'=>['weekly ', false],
				'0_0_-_-_-'=>['daily  ', false],
				'0_-_-_-_-'=>['hourly ', false],
				'-_-_-_-_-'=>['every  ', true],
				'32_15_26_10_-'=>['certain', true]
			]))
				$errors[]='cron_closure test 7';

		if(!empty($errors))
		{
			echo PHP_EOL;

			foreach($errors as $error)
				echo $error.' failed'.PHP_EOL;

			exit(1);
		}
	}
?>