<?php
	function logrotate(array $files, ?callable $print_log=null)
	{
		/*
		 * Journalists rotation machine
		 *
		 * Note:
		 *  directory for output_file file and timestamp (rotate_every[2])
		 *  will be created automatically if it does not exist
		 *
		 * Hint:
		 *  you can run this function via the tool,
		 *  task scheduler or call directly in the app
		 *
		 * Configuration options:
		 *  'output_file' -> absolute or relative path
		 *    warning: this file must not exist
		 *  'min_size' -> do not rotate the log until it is sufficiently swollen
		 *  'rotate_every' -> do not turn the log until it is mature
		 *    warning: timestamp path (rotate_every[2]) is required
		 *  'gzip' -> crush the file after rotating
		 *    warning: this file must not exist
		 *
		 * Example $files array:
			[
				'./var/log/example-log.txt'=>[
					'output_file'=>'./var/logrotate/example-log_'.date('Y-m-d_H-i-s').'.txt', // required
					'min_size'=>[5, 'k'], // empty string (bytes), k (kB) or m (MB)
					'rotate_every'=>[
						1, 'hours', // minutes hours days months (30 days)
						'./var/logrotate/example-log.timestamp' // required for rotate_every
					],
					'gzip'=>'w9' // .gz will be added automatically, w9 is the second parameter for gzopen()
				]
			]
		 *
		 * Example $print_log callback:
			function($priority, $message)
			{
				switch($priority)
				{
					case 'info':
						$priority='I';
					break;
					case 'warning':
						$priority='W';
					break;
					case 'error':
						$priority='E';
				}

				echo date('Y-m-d H:i:s').' ['.$priority.'] '.$message.PHP_EOL;
			}
		 */

		if($print_log === null)
			$print_log=function() {};

		$print_log('info', 'logrotate started');

		foreach($files as $file=>$opts)
		{
			if(!isset($opts['output_file']))
			{
				$print_log('error', 'Bad configuration for '.$file.' (output_file) - skipping');
				continue;
			}

			if(isset($opts['rotate_every']) && (!isset($opts['rotate_every'][2])))
			{
				$print_log('error', 'Bad configuration for '.$file.' (rotate_every[2] undefined) - skipping');
				continue;
			}

			if(!file_exists($file))
			{
				$print_log('warning', $file.' does not exists');
				continue;
			}

			if(file_exists($opts['output_file']))
			{
				$print_log('error', $opts['output_file'].' exists - skipping');
				continue;
			}

			if(isset($opts['min_size']))
			{
				switch($opts['min_size'][1])
				{
					case 'k':
						$opts['min_size'][0]*=1024;
					break;
					case 'm':
						$opts['min_size'][0]*=1048576;
				}

				if(filesize($file) < $opts['min_size'][0])
				{
					$print_log('info', $file.' min_size '.$opts['min_size'][0].'B not exceeded - skipping');
					continue;
				}
			}

			if(isset($opts['rotate_every']))
			{
				$rotate_every_multiplier=null;

				switch($opts['rotate_every'][1])
				{
					case 'minutes':
						$rotate_every_multiplier=60;
					break;
					case 'hours':
						$rotate_every_multiplier=3600;
					break;
					case 'days':
						$rotate_every_multiplier=86400;
					break;
					case 'months':
						$rotate_every_multiplier=2592000;
				}

				if($rotate_every_multiplier === null)
					$print_log('warning', $file.' unrecognized option rotate_every[1] '.$opts['rotate_every'][1].' - rotating file');
				else if(file_exists($opts['rotate_every'][2]))
				{
					$rotate_every_time=time();
					$rotate_every_filemtime=filemtime($opts['rotate_every'][2])+($opts['rotate_every'][0]*$rotate_every_multiplier);

					if($rotate_every_time < $rotate_every_filemtime)
					{
						$print_log('info', $opts['rotate_every'][2].' rotate_every not exceeded ([current] '.$rotate_every_time.' < '.$rotate_every_filemtime.' [timestamp])  - skipping');
						continue;
					}
				}

				$dirname_cache=dirname($opts['rotate_every'][2]);

				if(!file_exists($dirname_cache))
				{
					$print_log('info', 'Creating directory '.$dirname_cache);
					mkdir($dirname_cache, 0777, true);
				}

				$print_log('info', 'Updating timestamp file '.$opts['rotate_every'][2]);
				file_put_contents($opts['rotate_every'][2], date('Y-m-d H:i:s'));
			}

			$dirname_cache=dirname($opts['output_file']);

			if(!file_exists($dirname_cache))
			{
				$print_log('info', 'Creating directory '.$dirname_cache);
				mkdir($dirname_cache, 0777, true);
			}

			$print_log('info', 'Rotating '.$file);

			if(!copy($file, $opts['output_file']))
			{
				$print_log('error', $file.' rotation error - skipping');
				continue;
			}

			$print_log('info', 'Cleaning '.$file);

			if(file_put_contents($file, '') === false)
				$print_log('warning', $file.' cleaning failed');

			if(isset($opts['gzip']))
			{
				if(file_exists($opts['output_file'].'.gz'))
				{
					$print_log('error', $opts['output_file'].'.gz exists - compressing skipped');
					continue;
				}

				$print_log('info', 'Compressing '.$file);
				$file_gzip=gzopen($opts['output_file'].'.gz', $opts['gzip']);

				if($file_gzip === false)
				{
					$print_log('error', $opts['output_file'].'.gz gzopen error');
					continue;
				}

				if(gzwrite($file_gzip, file_get_contents($opts['output_file'])) === false)
				{
					$print_log('error', $opts['output_file'].'.gz write error - compressing skipped');
					gzclose($file_gzip);

					if(file_exists($opts['output_file'].'.gz'))
						unlink($opts['output_file'].'.gz');

					continue;
				}

				gzclose($file_gzip);
				unlink($opts['output_file']);
			}
		}

		$print_log('info', 'logrotate finished');
	}
?>