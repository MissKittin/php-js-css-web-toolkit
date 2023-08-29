<?php
	/*
	 * The cron
	 *
	 * Warning:
	 *  this library has no daemon functions
	 *  the tool is responsible for that
	 *
	 * Note:
	 *  it can be used both from the command line and from the application
	 *  but remember that the application time is limited
	 *
	 * cron -> repeated, based on the cron hash
	 *  crontab: 0_-_1_1_0/task-name.php
	 *  parameters: 'crontab' 'directory' 'debug' 'log_callback' 'debug_callback'
	 *
	 * cron_timestamp -> oneshot, based on unix timestamp
	 *  task: 1645732027_task-name.php
	 *  parameters: 'tasks' 'debug' 'log_callback' 'debug_callback'
	 *
	 * cron_closure -> call functions instead of including files
	 *  (new cron_closure)->add(string_hash, callable_function)->add(string_hash, callable_function)->run()
	 */

	class cron_exception extends Exception {}

	function cron(array $_params)
	{
		/*
		 * Basic cron implementation
		 *
		 * Note:
		 *  throws an cron_exception on error
		 *
		 * Parameters:
		 *  crontab [string] (required) -> path to directory with hashes
		 *  directory [string] -> eg. 'boot' , see Directories
		 *  debug [bool] (default: false) -> run in debug mode
		 *  log_callback [callable] -> takes a string parameter
		 *  debug_callback [callable] -> if debug mode enabled, takes a string parameter
		 *
		 * Crontab:
		 *  each task should be placed in a directory with an appropriate hash
		 *  hash consists of five numbers separated by _
		 *  eg: 0_0_1_1_- => minute_hour_day_month_weekday
		 *   where - means any (* in cron),
		 *   month === 1 means January (0 is invalid)
		 *   and weekday === 0 means sunday (1 is monday)
		 *   note: number is always without leading zeros
		 *  if the hash matches the current time,
		 *  all files in the directory will be included
		 *
		 * Cheat sheet:
		 *  0_0_1_1_- => run yearly (1 January, 00:00)
		 *  0_0_1_-_- => run monthly (1 any month, 00:00)
		 *  0_0_-_-_0 => weekly (sunday, 00:00)
		 *  0_0_-_-_- => daily (00:00)
		 *  0_-_-_-_- => hourly (a full hour)
		 *  -_-_-_-_- => run every minute
		 *
		 * Directories:
		 *  you can force tasks to run from a specific directory
		 *  directory must be in the path given by the crontab parameter
		 *  if the directories parameter is not defined,
		 *   cron will search the crontab directory
		 */

		if(!isset($_params['crontab']))
			throw new cron_exception('The crontab parameter was not specified');

		if(!isset($_params['debug']))
			$_params['debug']=false;

		if(!isset($_params['log_callback']))
			$_params['log_callback']=function(){};

		if(!isset($_params['debug_callback']))
			$_params['debug_callback']=function(){};

		if(!is_dir($_params['crontab']))
			throw new cron_exception($_params['crontab'].' is not a directory');

		if(isset($_params['directory']))
		{
			if($_params['debug'])
				$_params['debug_callback']('Requested directory: '.$_params['directory']);

			if(is_dir($_params['crontab'].'/'.$_params['directory']))
			{
				foreach(scandir($_params['crontab'].'/'.$_params['directory']) as $_job)
					if(!is_dir($_params['crontab'].'/'.$_params['directory'].'/'.$_job))
					{
						$_params['log_callback']('Executing job '.$_params['directory'].'/'.$_job);

						if((include $_params['crontab'].'/'.$_params['directory'].'/'.$_job) === false)
							$_params['log_callback']('Job '.$_params['directory'].'/'.$_job.' inclusion error');
						else
							if($_params['debug'])
								$_params['debug_callback']('Job '.$_params['directory'].'/'.$_job.' ended');
					}
			}
			else
				$_params['log_callback']($_params['crontab'].'/'.$_params['directory'].' is not a directory');

			return null;
		}

		$_current_hash=[
			'minute'=>(string)intval(date('i')),
			'hour'=>date('G'),
			'day'=>date('j'),
			'month'=>date('n'),
			'weekday'=>date('w')
		];

		if($_params['debug'])
			$_params['debug_callback'](''
			.	'Current hash: '
			.	$_current_hash['minute'].'_'
			.	$_current_hash['hour'].'_'
			.	$_current_hash['day'].'_'
			.	$_current_hash['month'].'_'
			.	$_current_hash['weekday']
			);

		foreach(preg_grep(''
		.	'/^'
		.	'('.$_current_hash['minute'].'|-)_'
		.	'('.$_current_hash['hour'].'|-)_'
		.	'('.$_current_hash['day'].'|-)_'
		.	'('.$_current_hash['month'].'|-)_'
		.	'('.$_current_hash['weekday'].'|-)'
		.	'$/'
		,	scandir($_params['crontab']))
		as $_match)
			if(is_dir($_params['crontab'].'/'.$_match))
			{
				if($_params['debug'])
					$_params['debug_callback']('Found hash '.$_match);

				foreach(array_slice(scandir($_params['crontab'].'/'.$_match), 2) as $_job)
					if(!is_dir($_params['crontab'].'/'.$_match.'/'.$_job))
					{
						$_params['log_callback']('Executing job '.$_match.'/'.$_job);

						if((include $_params['crontab'].'/'.$_match.'/'.$_job) === false)
							$_params['log_callback']('Job '.$_match.'/'.$_job.' inclusion error');
						else
							if($_params['debug'])
								$_params['debug_callback']('Job '.$_match.'/'.$_job.' ended');
					}
			}
	}
	function cron_timestamp(array $_params)
	{
		/*
		 * Oneshot cron implementation
		 * Performs all tasks with a timestamp less than the current one
		 *
		 * Warning:
		 *  the task will be deleted after execution.
		 *  if the task cannot be deleted, it will be redone
		 *  and you wouldn't want that
		 *
		 * Note:
		 *  throws an cron_exception on error
		 *
		 * Hint:
		 *  the scripts directory is best placed in the var directory
		 *  eg. var/cron/timestamps
		 *
		 * Parameters:
		 *  tasks [string] (required) -> path to directory with tasks
		 *  debug [bool] -> run in debug mode
		 *  log_callback [callable] -> takes a string parameter
		 *  debug_callback [callable] -> if debug mode enabled, takes a string parameter
		 *
		 * Tasks:
		 *  function searches for scripts by timestamp: timestamp_task-name.php
		 *  eg. 1645732027_task-name.php
		 */

		if(!isset($_params['tasks']))
			throw new cron_exception('The tasks parameter was not specified');

		if(!isset($_params['debug']))
			$_params['debug']=false;

		if(!isset($_params['log_callback']))
			$_params['log_callback']=function(){};

		if(!isset($_params['debug_callback']))
			$_params['debug_callback']=function(){};

		if(!is_dir($_params['tasks']))
			throw new cron_exception($_params['tasks'].' is not a directory');

		$_current_timestamp=time();
		if($_params['debug'])
			$_params['debug_callback']('Current timestamp: '.$_current_timestamp);

		foreach(scandir($_params['tasks']) as $_job)
		{
			$_job_timestamp=substr($_job, 0, strpos($_job, '_'));

			if((!empty($_job_timestamp)) && ($_job_timestamp <= $_current_timestamp))
			{
				$_params['log_callback']('Executing job '.$_job);

				if((include $_params['tasks'].'/'.$_job) === false)
					$_params['log_callback']('Job '.$_job.' inclusion error');
				else
				{
					if($_params['debug'])
						$_params['debug_callback']('Job '.$_job.' ended');

					if(@unlink($_params['tasks'].'/'.$_job))
					{
						if($_params['debug'])
							$_params['debug_callback']('Job '.$_job.' removed');
					}
					else
						$_params['log_callback']('Fatal error: cannot remove '.$_params['tasks'].'/'.$_job);
				}
			}
		}
	}

	class cron_closure
	{
		/*
		 * Closure cron implementation
		 * Call functions instead of including files
		 *
		 * Note:
		 *  you can define functions once and run them multiple times
		 *
		 * Cheat sheet:
		 *  0_0_1_1_- => run yearly (1 January, 00:00)
		 *  0_0_1_-_- => run monthly (1 any month, 00:00)
		 *  0_0_-_-_0 => weekly (sunday, 00:00)
		 *  0_0_-_-_- => daily (00:00)
		 *  0_-_-_-_- => hourly (a full hour)
		 *  -_-_-_-_- => run every minute
		 *
		 * Methods:
		 *  add(string_cron_hash, callable_function) [returns self]
		 *   add a function to the registry
		 *  run()
		 *   perform the appropriate functions
		 *
		 * Usage (short way):
			(new cron_closure)
				->add('0_0_1_1_-', function(){
					echo 'yearly';
				})
				->add('0_0_1_-_-', function(){
					echo 'monthly';
				})
				->add('0_0_-_-_0', function(){
					echo 'weekly';
				})
				->add('0_0_-_-_-', function(){
					echo 'daily';
				})
				->add('0_-_-_-_-', function(){
					echo 'hourly';
				})
				->add('-_-_-_-_-', function(){
					echo 'every minute';
				})
				->run();
		 */

		protected $closures=[];

		public function add(string $hash, callable $function)
		{
			$this->closures[$hash][]=$function;
			return $this;
		}

		public function run()
		{
			$_current_hash=[
				'minute'=>(string)intval(date('i')),
				'hour'=>date('G'),
				'day'=>date('j'),
				'month'=>date('n'),
				'weekday'=>date('w')
			];

			foreach(preg_grep(''
			.	'/^'
			.	'('.$_current_hash['minute'].'|-)_'
			.	'('.$_current_hash['hour'].'|-)_'
			.	'('.$_current_hash['day'].'|-)_'
			.	'('.$_current_hash['month'].'|-)_'
			.	'('.$_current_hash['weekday'].'|-)'
			.	'$/'
			,	array_keys($this->closures))
			as $hash)
				foreach($this->closures[$hash] as $closure)
					$closure();
		}
	}
?>