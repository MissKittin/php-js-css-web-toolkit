<?php
	/*
	 * Interface for cron.php library
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  cron.php library is required
	 *  pcntl extension is required (or use --once arg)
	 *
	 * Note:
	 *  tasks in --timestamps (cron_timestamp) will be executed with an accuracy of 1 minute
	 *  you don't need to restart the daemon after adding/deleting/editing a task
	 *  functions from the check_var.php library and load_library function
	 *   will also be available for tasks
	 *   use if(!function_exists()) instead
	 *  to run this program as a daemon, use the method
	 *   appropriate to your init, container, os or configuration
	 *
	 * Example functions.php:
		$log_callback=function($message)
		{
			echo '['.date('Y-m-d H:i:s').'.'.gettimeofday()['usec'].'] '.$message.PHP_EOL;
		};
		$debug_callback=function($message)
		{
			$GLOBALS['log_callback']('[D] '.$message);
		};
	 */

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
			if(file_exists(__DIR__.'/lib/'.$library))
				include __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				include __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
	}

	try {
		load_library([
			'check_var.php',
			'cron.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$__cron_tab=check_argv_next_param('--crontab');
	$__cron_timestamps=check_argv_next_param('--timestamps');
	$__cron_functions=check_argv_next_param('--functions');

	$__cron_boot=true;
	if(check_argv('--no-boot'))
		$__cron_boot=false;

	$__cron_once=false;
	if(check_argv('--once'))
		$__cron_once=true;

	$__debug=false;
	if(check_argv('--debug'))
		$__debug=true;

	if(
		(($__cron_tab === null) && ($__cron_timestamps === null)) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Usage: --crontab ./path/to/crontab --timestamps ./path/to/tasks [--functions ./path/to/functions.php] [--no-boot] [--once] [--debug]'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --no-boot -> disable execution of tasks from the boot hash'.PHP_EOL;
		echo ' --once -> complete the tasks and exit (no pcntl required)'.PHP_EOL;
		echo ' --debug -> run in debug mode'.PHP_EOL;
		echo PHP_EOL;
		echo 'You can use --crontab and --timestamps simultaneously'.PHP_EOL;
		echo 'but one of them must be defined'.PHP_EOL;
		echo PHP_EOL;
		echo 'For more info see this file and cron.php library'.PHP_EOL;
		exit(1);
	}

	if((!$__cron_once) && (!extension_loaded('pcntl')))
	{
		echo 'pcntl extension is not loaded'.PHP_EOL;
		exit(1);
	}

	if($__cron_functions !== null)
	{
		if(!file_exists($__cron_functions))
		{
			echo $__cron_functions.' not exists'.PHP_EOL;
			exit(1);
		}

		if((include $__cron_functions) === false)
		{
			echo $__cron_functions.' inclusion error'.PHP_EOL;
			exit(1);
		}
	}

	if((!isset($log_callback)) ||(!is_callable($log_callback)))
		$log_callback=function(){};

	if((!isset($debug_callback)) || (!is_callable($debug_callback)))
		$debug_callback=function(){};

	if($__cron_boot && ($__cron_tab !== null))
		try {
			if($__debug)
				$debug_callback('Executing cron()');

			cron([
				'crontab'=>$__cron_tab,
				'directory'=>'boot',
				'debug'=>$__debug,
				'log_callback'=>$log_callback,
				'debug_callback'=>$debug_callback
			]);

			if($__debug)
				$debug_callback('cron() done');
		} catch(Exception $error) {
			$log_callback('Error: '.$error->getMessage());
		}
	else
		if($__debug)
			$debug_callback('--no-boot applied or --crontab not specified');

	if($__cron_once)
	{
		if($__debug)
			$debug_callback('--once enabled');

		try {
			if($__cron_tab !== null)
			{
				if($__debug)
					$debug_callback('Executing cron()');

				cron([
					'crontab'=>$__cron_tab,
					'debug'=>$__debug,
					'log_callback'=>$log_callback,
					'debug_callback'=>$debug_callback
				]);

				if($__debug)
					$debug_callback('cron() done');
			}

			if($__cron_timestamps !== null)
			{
				if($__debug)
					$debug_callback('Executing cron_timestamp()');

				cron_timestamp([
					'tasks'=>$__cron_timestamps,
					'debug'=>$__debug,
					'log_callback'=>$log_callback,
					'debug_callback'=>$debug_callback
				]);

				if($__debug)
					$debug_callback('cron_timestamp() done');
			}
		} catch(Exception $error) {
			$log_callback('Error: '.$error->getMessage());
		}

		if($__debug)
			$debug_callback('Exiting');

		exit();
	}

	$GLOBALS['__children_pids']=array();
	declare(ticks=1);
	pcntl_signal(SIGCHLD, function($signal){
		if($signal === SIGCHLD)
			foreach($GLOBALS['__children_pids'] as $pid)
				if(pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED) !== 0)
					unset($GLOBALS['__children_pids'][$pid]);
	});

	while(true)
	{
		if($__debug)
			$debug_callback('Woke up');

		$__child_pid=pcntl_fork();

		if($__child_pid === -1)
			$log_callback('Fork error');
		else if($__child_pid === 0)
		{
			try {
				if($__cron_tab !== null)
				{
					if($__debug)
						$debug_callback('Executing cron()');

					cron([
						'crontab'=>$__cron_tab,
						'debug'=>$__debug,
						'log_callback'=>$log_callback,
						'debug_callback'=>$debug_callback
					]);

					if($__debug)
						$debug_callback('cron() done');
				}

				if($__cron_timestamps !== null)
				{
					if($__debug)
						$debug_callback('Executing cron_timestamp()');

					cron_timestamp([
						'tasks'=>$__cron_timestamps,
						'debug'=>$__debug,
						'log_callback'=>$log_callback,
						'debug_callback'=>$debug_callback
					]);

					if($__debug)
						$debug_callback('cron_timestamp() done');
				}
			} catch(Exception $error) {
				$log_callback('Error: '.$error->getMessage());
			}

			sleep(1);
			exit();
		}
		else
			$GLOBALS['__children_pids'][$__child_pid]=$__child_pid;

		for($__sleep=60; $__sleep>0; --$__sleep)
			if(!empty($GLOBALS['__children_pids']))
			{
				if($__debug)
					$debug_callback('Waiting for children: '.$__sleep);

				sleep(1);
			}
			else
				break;

		if($__sleep !== 0)
		{
			++$__sleep;

			if($__debug)
				$debug_callback('Child processes terminated, waiting '.$__sleep.' seconds');

			sleep($__sleep);
		}
	}
?>