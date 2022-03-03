<?php
	/*
	 * The cron
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
	 */

	function cron(array $__params)
	{
		/*
		 * Basic cron implementation
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

		if(!isset($__params['crontab']))
			throw new Exception('The crontab parameter was not specified');

		if(!isset($__params['debug']))
			$__params['debug']=false;

		if(!isset($__params['log_callback']))
			$__params['log_callback']=function(){};

		if(!isset($__params['debug_callback']))
			$__params['debug_callback']=function(){};

		if(!is_dir($__params['crontab']))
			throw new Exception($__params['crontab'].' is not a directory');

		if(isset($__params['directory']))
		{
			if($__params['debug'])
				$__params['debug_callback']('Requested directory: '.$__params['directory']);

			if(is_dir($__params['crontab'].'/'.$__params['directory']))
			{
				foreach(scandir($__params['crontab'].'/'.$__params['directory']) as $__job)
					if(!is_dir($__params['crontab'].'/'.$__params['directory'].'/'.$__job))
					{
						$__params['log_callback']('Executing job '.$__params['directory'].'/'.$__job);

						if((include $__params['crontab'].'/'.$__params['directory'].'/'.$__job) === false)
							$__params['log_callback']('Job '.$__params['directory'].'/'.$__job.' inclusion error');
						else
							if($__params['debug'])
								$__params['debug_callback']('Job '.$__params['directory'].'/'.$__job.' ended');
					}
			}
			else
				$__params['log_callback']($__params['crontab'].'/'.$__params['directory'].' is not a directory');

			return null;
		}

		$__current_hash=[
			'minute'=>(string)intval(date('i')),
			'hour'=>date('G'),
			'day'=>date('j'),
			'month'=>date('n'),
			'weekday'=>date('w')
		];

		if($__params['debug'])
			$__params['debug_callback'](
				'Current hash: '
				.$__current_hash['minute'].'_'
				.$__current_hash['hour'].'_'
				.$__current_hash['day'].'_'
				.$__current_hash['month'].'_'
				.$__current_hash['weekday']
			);

		foreach
		(
			preg_grep('/^'
				.'('.$__current_hash['minute'].'|-)_'
				.'('.$__current_hash['hour'].'|-)_'
				.'('.$__current_hash['day'].'|-)_'
				.'('.$__current_hash['month'].'|-)_'
				.'('.$__current_hash['weekday'].'|-)'
			.'$/', scandir($__params['crontab']))
			as $__match
		)
			if(is_dir($__params['crontab'].'/'.$__match))
			{
				if($__params['debug'])
					$__params['debug_callback']('Found hash '.$__match);

				foreach(array_slice(scandir($__params['crontab'].'/'.$__match), 2) as $__job)
					if(!is_dir($__params['crontab'].'/'.$__match.'/'.$__job))
					{
						$__params['log_callback']('Executing job '.$__match.'/'.$__job);

						if((include $__params['crontab'].'/'.$__match.'/'.$__job) === false)
							$__params['log_callback']('Job '.$__match.'/'.$__job.' inclusion error');
						else
							if($__params['debug'])
								$__params['debug_callback']('Job '.$__match.'/'.$__job.' ended');
					}
			}
	}
	function cron_timestamp(array $__params)
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
		 *  
		 */

		if(!isset($__params['tasks']))
			throw new Exception('The tasks parameter was not specified');

		if(!isset($__params['debug']))
			$__params['debug']=false;

		if(!isset($__params['log_callback']))
			$__params['log_callback']=function(){};

		if(!isset($__params['debug_callback']))
			$__params['debug_callback']=function(){};

		if(!is_dir($__params['tasks']))
			throw new Exception($__params['tasks'].' is not a directory');

		$__current_timestamp=time();
		if($__params['debug'])
			$__params['debug_callback']('Current timestamp: '.$__current_timestamp);

		foreach(scandir($__params['tasks']) as $__job)
		{
			$__job_timestamp=substr($__job, 0, strpos($__job, '_'));

			if((!empty($__job_timestamp)) && ($__job_timestamp <= $__current_timestamp))
			{
				$__params['log_callback']('Executing job '.$__job);

				if((include $__params['tasks'].'/'.$__job) === false)
					$__params['log_callback']('Job '.$__job.' inclusion error');
				else
				{
					if($__params['debug'])
						$__params['debug_callback']('Job '.$__job.' ended');

					if(@unlink($__params['tasks'].'/'.$__job))
					{
						if($__params['debug'])
							$__params['debug_callback']('Job '.$__job.' removed');
					}
					else
						$__params['log_callback']('Fatal error: cannot remove '.$__params['tasks'].'/'.$__job);
				}
			}
		}
	}
?>