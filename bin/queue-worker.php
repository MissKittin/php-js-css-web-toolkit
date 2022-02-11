<?php
	/*
	 * Interface for queue_worker.php server
	 *
	 * Note:
	 *  functions from the check_var.php library and load_library function
	 *   will also be available to the worker
	 *   use if(!function_exists()) instead
	 *  to run this program as a daemon, use the method
	 *   appropriate to your init, container, os or configuration
	 *
	 * Warning:
	 *  posix extension is recommended
	 *  pcntl extension is optional
	 *  check_var.php library is required
	 *  queue_worker.php library is required
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

	load_library([
		'check_var.php',
		'queue_worker.php'
	]);

	$__worker_fifo=check_argv_next_param('--fifo');
	$__worker_functions=check_argv_next_param('--functions');
	$__worker_fork=false;
	if(check_argv('--fork'))
		$__worker_fork=true;
	$__children_limit=check_argv_param('--children-limit');
	if($__children_limit === null)
		$__children_limit=0;
	$__recreate_fifo=true;
	if(check_argv('--no-recreate-fifo'))
		$__recreate_fifo=false;
	$__debug=false;
	if(check_argv('--debug'))
		$__debug=true;

	if(
		($__worker_fifo === null) ||
		($__worker_functions === null) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Usage: --fifo ./path/to/fifo --functions ./path/to/functions.php [--fork] [--children-limit=4] [--no-recreate-fifo] [--debug]'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --fork -> enable parallel execution via PCNTL'.PHP_EOL;
		echo ' --children-limit=<positive-int> -> limit background processes (default: unlimited)'.PHP_EOL;
		echo ' --no-recreate-fifo -> use this if you want to run multiple instances'.PHP_EOL;
		echo ' --debug -> print debug messages to stdin'.PHP_EOL;
		echo PHP_EOL;
		echo 'For more info see this file and queue_worker.php library'.PHP_EOL;
		exit(1);
	}

	try {
		queue_worker::start_worker(
			$__worker_fifo,
			$__worker_functions,
			$__worker_fork,
			$__children_limit,
			$__recreate_fifo,
			$__debug
		);
	} catch(Exception $error) {
		echo '[E] '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>