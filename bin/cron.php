<?php
	/*
	 * Interface for cron.php library
	 * For more info, see cron.php library
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
		$debug_callback=function($message) use($log_callback)
		{
			$log_callback('[D] '.$message);
		};
	 */

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
		{
			if(file_exists(__DIR__.'/lib/'.$library))
			{
				require __DIR__.'/lib/'.$library;
				continue;
			}

			if(file_exists(__DIR__.'/../lib/'.$library))
			{
				require __DIR__.'/../lib/'.$library;
				continue;
			}

			if($required)
				throw new Exception($library.' library not found');
		}
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

	$_cron_tab=check_argv_next_param('--crontab');
	$_cron_timestamps=check_argv_next_param('--timestamps');
	$_cron_functions=check_argv_next_param('--functions');

	$_cron_boot=true;
	$_cron_once=false;
	$_debug=false;

	if(check_argv('--no-boot'))
		$_cron_boot=false;

	if(check_argv('--once'))
		$_cron_once=true;

	if(check_argv('--debug'))
		$_debug=true;

	if(
		(!$_cron_once) &&
		(!function_exists('pcntl_signal'))
	){
		echo 'pcntl extension is not loaded'.PHP_EOL;
		exit(1);
	}

	if(
		(
			($_cron_tab === null) &&
			($_cron_timestamps === null)
		) ||
		check_argv('--help') || check_argv('-h')
	){
		echo 'Usage: '.$argv[0].' --crontab ./path/to/crontab --timestamps ./path/to/tasks [--functions ./path/to/functions.php] [--no-boot] [--once] [--debug]'.PHP_EOL;
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

	if($_cron_functions !== null)
	{
		if(!file_exists($_cron_functions))
		{
			echo $_cron_functions.' not exists'.PHP_EOL;
			exit(1);
		}

		require $_cron_functions;
	}

	if(
		(!isset($log_callback)) ||
		(!is_callable($log_callback))
	)
		$log_callback=function(){};

	if(
		(!isset($debug_callback)) ||
		(!is_callable($debug_callback))
	)
		$debug_callback=function(){};

	if(
		$_cron_boot &&
		($_cron_tab !== null)
	)
		try {
			if($_debug)
				$debug_callback('Executing cron()');

			cron([
				'crontab'=>$_cron_tab,
				'directory'=>'boot',
				'debug'=>$_debug,
				'log_callback'=>$log_callback,
				'debug_callback'=>$debug_callback
			]);

			if($_debug)
				$debug_callback('cron() done');
		} catch(Throwable $error) {
			$log_callback('Error: '.$error->getMessage());
		}
	else if($_debug)
		$debug_callback('--no-boot applied or --crontab not specified');

	if($_cron_once)
	{
		if($_debug)
			$debug_callback('--once enabled');

		try {
			if($_cron_tab !== null)
			{
				if($_debug)
					$debug_callback('Executing cron()');

				cron([
					'crontab'=>$_cron_tab,
					'debug'=>$_debug,
					'log_callback'=>$log_callback,
					'debug_callback'=>$debug_callback
				]);

				if($_debug)
					$debug_callback('cron() done');
			}

			if($_cron_timestamps !== null)
			{
				if($_debug)
					$debug_callback('Executing cron_timestamp()');

				cron_timestamp([
					'tasks'=>$_cron_timestamps,
					'debug'=>$_debug,
					'log_callback'=>$log_callback,
					'debug_callback'=>$debug_callback
				]);

				if($_debug)
					$debug_callback('cron_timestamp() done');
			}
		} catch(Throwable $error) {
			$log_callback('Error: '.$error->getMessage());
		}

		if($_debug)
			$debug_callback('Exiting');

		exit();
	}

	$GLOBALS['_children_pids']=[];
	declare(ticks=1);
	pcntl_signal(SIGCHLD, function($signal){
		if($signal === SIGCHLD)
			foreach($GLOBALS['_children_pids'] as $pid)
				if(pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED) !== 0)
					unset($GLOBALS['_children_pids'][$pid]);
	});

	while(true)
	{
		if($_debug)
			$debug_callback('Woke up');

		$_child_pid=pcntl_fork();

		if($_child_pid === -1)
			$log_callback('Fork error');
		else if($_child_pid === 0)
		{
			try {
				if($_cron_tab !== null)
				{
					if($_debug)
						$debug_callback('Executing cron()');

					cron([
						'crontab'=>$_cron_tab,
						'debug'=>$_debug,
						'log_callback'=>$log_callback,
						'debug_callback'=>$debug_callback
					]);

					if($_debug)
						$debug_callback('cron() done');
				}

				if($_cron_timestamps !== null)
				{
					if($_debug)
						$debug_callback('Executing cron_timestamp()');

					cron_timestamp([
						'tasks'=>$_cron_timestamps,
						'debug'=>$_debug,
						'log_callback'=>$log_callback,
						'debug_callback'=>$debug_callback
					]);

					if($_debug)
						$debug_callback('cron_timestamp() done');
				}
			} catch(Throwable $error) {
				$log_callback('Error: '.$error->getMessage());
			}

			sleep(1);
			exit();
		}
		else
			$GLOBALS['_children_pids'][$_child_pid]=$_child_pid;

		for($_sleep=60; $_sleep>0; --$_sleep)
		{
			if(!empty($GLOBALS['_children_pids']))
			{
				if($_debug)
					$debug_callback('Waiting for children: '.$_sleep);

				sleep(1);

				continue;
			}

			break;
		}

		if($_sleep !== 0)
		{
			++$_sleep;

			if($_debug)
				$debug_callback('Child processes terminated, waiting '.$_sleep.' seconds');

			sleep($_sleep);
		}
	}
?>