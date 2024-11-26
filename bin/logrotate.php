<?php
	/*
	 * Interface for logrotate.php library
	 * For more info, see logrotate.php library
	 *
	 * Note:
	 *  use cron to rotate the logs periodically
	 *  the library takes the $print_log argument
	 *   as callable (it can be closure), while
	 *   the print_log function can be defined in the
	 *   configuration file (see example)
	 *
	 * Warning:
	 *  logrotate.php library is required
	 *
	 * Example configuration (app/logrotate.config.php):
		<?php
			chdir(__DIR__.'/..'); // suggested

			// required
			$files=[
				'./var/log/example-log.txt'=>[
					'output_file'=>'./var/logrotate/example-log_'.date('Y-m-d_H-i-s').'.txt', // required
					'min_size'=>[5, 'k'], // empty string (bytes), k (kB) or m (MB)
					'rotate_every'=>[
						2, 'hours', // minutes hours days months (30 days)
						'./var/logrotate/example-log.timestamp' // required for rotate_every
					],
					'gzip'=>'w9' // .gz will be added automatically, w9 is the second parameter for gzopen()
				]
			];

			// optional
			function print_log($priority, $message)
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

				file_put_contents(
					'./var/log/logrotate.log',
					date('Y-m-d H:i:s').' ['.$priority.'] '.$message.PHP_EOL,
					FILE_APPEND
				);
			}
		?>
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
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
		load_library(['logrotate.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(!isset($argv[1]))
	{
		echo 'Usage: '.$argv[0].' path/to/logrotate_config.php'.PHP_EOL;
		echo 'For more info see this file and logrotate.php library'.PHP_EOL;
		exit(1);
	}

	if(($argv[1] === '--help') || ($argv[1] === '-h'))
	{
		echo 'Usage: '.$argv[0].' path/to/logrotate_config.php'.PHP_EOL;
		echo 'For more info see this file and logrotate.php library'.PHP_EOL;
		exit();
	}

	if(!file_exists($argv[1]))
	{
		echo 'Error: '.$argv[1].' not exists'.PHP_EOL;
		exit(1);
	}

	require $argv[1];

	if(!isset($files))
	{
		echo 'Error: $files array not defined'.PHP_EOL;
		exit(1);
	}

	if(!is_array($files))
	{
		echo 'Error: $files is not an array'.PHP_EOL;
		exit(1);
	}

	if(!function_exists('print_log'))
	{
		function print_log($priority, $message)
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
	}

	logrotate($files, 'print_log');
?>