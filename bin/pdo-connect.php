<?php
	/*
	 * Run pdo_connect offline
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  pdo_cheat.php library is optional
	 *  pdo_connect.php library is required
	 *  pdo_crud_builder.php library is optional
	 *
	 * Note:
	 *  you can use $pdo_handler in post script
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
		'pdo_connect.php'
	]);
	load_library([
		'pdo_cheat.php',
		'pdo_crud_builder.php'
	], false);

	chdir(__DIR__ . '/..');

	if(!$pcos_db_name=check_argv_next_param('-db'))
	{
		echo 'No database config path'.PHP_EOL;
		echo 'Usage:'.PHP_EOL;
		echo ' -db ./databases/database_name [-pre ./path_to/pre_script.php] [-post ./path_to/post_script.php]'.PHP_EOL;
		exit(1);
	}

	if($pcos_pre_script=check_argv_next_param('-pre'))
		include $pcos_pre_script;

	$pdo_handler=pdo_connect($pcos_db_name);

	if($pcos_post_script=check_argv_next_param('-post'))
		include $pcos_post_script;
?>