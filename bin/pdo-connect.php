<?php
	/*
	 * Run pdo_connect offline
	 *
	 * Note:
	 *  you can use $pdo_handler in post script
	 *
	 * check_var.php library is required
	 * pdo_connect.php library is required
	 * pdo_crud_builder.php library is optional
	 */

	chdir(__DIR__ . '/..');

	include './lib/check_var.php';
	include './lib/pdo_connect.php';
	@include './lib/pdo_crud_builder.php';

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