<?php
	/*
	 * Run pdo_connect offline
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  pdo_connect.php library is required
	 *
	 * Note:
	 *  you can use $pdo_handler in post script
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function load_library($libraries)
	{
		foreach($libraries as $library)
			if(file_exists(__DIR__.'/lib/'.$library))
				require __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				require __DIR__.'/../lib/'.$library;
			else
				throw new Exception($library.' library not found');
	}

	try {
		load_library([
			'check_var.php',
			'pdo_connect.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(!$pcos_db_name=check_argv_next_param('--db'))
	{
		echo 'No database config path'.PHP_EOL;
		echo 'Usage:'.PHP_EOL;
		echo ' [--chdir /path/to/project] --db ./app/databases/database_name [--pre ./path_to/pre_script.php] [--post ./path_to/post_script.php]'.PHP_EOL;
		exit(1);
	}

	if($pcos_dirname=check_argv_next_param('--chdir'))
		if(!chdir($pcos_dirname))
			exit(1);

	if($pcos_pre_script=check_argv_next_param('--pre'))
		require $pcos_pre_script;

	$pdo_handler=pdo_connect($pcos_db_name);

	if($pcos_post_script=check_argv_next_param('--post'))
		require $pcos_post_script;
?>