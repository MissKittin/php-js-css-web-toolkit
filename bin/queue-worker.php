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
	 *
	 * Example Redis config:
		<?php
			return [
				new Redis([
					'host'=>'127.0.0.1',
					'port'=>6379
				]),
				'key_prefix__'
			];
		?>
	 *
	 * Example Predis config:
		<?php
			require './vendor/autoload.php';
			require './lib/predis_connect.php';

			return [
				new predis_phpredis_proxy(new Predis\Client([
					'host'=>'127.0.0.1',
					'port'=>6379
				])),
				'key_prefix__'
			];
		?>
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
			if(file_exists(__DIR__.'/lib/'.$library))
				require __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				require __DIR__.'/../lib/'.$library;
			else if($required)
				throw new Exception($library.' library not found');
	}

	try {
		load_library([
			'check_var.php',
			'queue_worker.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$__worker_fifo=check_argv_next_param('--fifo');
	$__worker_redis=check_argv_next_param('--redis');
	$__worker_functions=check_argv_next_param('--functions');
	$__worker_fork=false;
	$__children_limit=check_argv_param('--children-limit');
	$__recreate_fifo=true;
	$__debug=false;


	if(check_argv('--fork'))
		$__worker_fork=true;
	if($__children_limit === null)
		$__children_limit=0;
	if(check_argv('--no-recreate-fifo'))
		$__recreate_fifo=false;
	if(check_argv('--debug'))
		$__debug=true;

	if(
		($__worker_functions === null) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Usage:'.PHP_EOL;
		echo ' '.$argv[0].' --fifo ./path/to/fifo --functions ./path/to/functions.php [--fork] [--children-limit=4] [--no-recreate-fifo] [--debug]'.PHP_EOL;
		echo ' '.$argv[0].' --redis ./path/to/redis-config.php --functions ./path/to/functions.php [--fork] [--children-limit=4] [--debug]'.PHP_EOL;
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
		switch(true)
		{
			case ($__worker_fifo !== null):
				queue_worker_fifo::start_worker(
					$__worker_fifo,
					$__worker_functions,
					$__worker_fork,
					$__children_limit,
					$__recreate_fifo,
					$__debug
				);
			break;
			case ($__worker_redis !== null):
				$__redis_config=include $__worker_redis;

				if(!isset($__redis_config[0]))
					throw new Exception('Redis credentials not defined');

				if(!isset($__redis_config[1]))
					$__redis_config[1]='queue_worker__';

				queue_worker_redis::start_worker(
					$__redis_config[0],
					$__worker_functions,
					$__redis_config[1],
					$__worker_fork,
					$__children_limit,
					$__debug
				);
			break;
			default:
				throw new Exception('You must use --fifo or --redis');
		}
	} catch(Throwable $error) {
		echo '[E] '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>