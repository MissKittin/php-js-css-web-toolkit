<?php
	/*
	 * Run pdo_connect offline
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  pdo_connect.php library is required
	 *
	 * Note:
	 *  you can use $pdo_handle in post script
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
		load_library([
			'check_var.php',
			'pdo_connect.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$pcos_db_name=check_argv_next_param('--db');
	$pcos_dirname=check_argv_next_param('--chdir');
	$pcos_pre_script=check_argv_next_param('--pre');
	$pcos_post_script=check_argv_next_param('--post');
	$pcos_reseed=check_argv('--reseed');

	if(
		($pcos_db_name === null) ||
		check_argv('--help') || check_argv('-h')
	){
		echo 'No database config path'.PHP_EOL;
		echo PHP_EOL;
		echo 'Usage: '.$argv[0].' --db ./app/databases/database_name [--chdir /path/to/project] [--pre ./path_to/pre_script.php] [--post ./path_to/post_script.php] [--reseed]'.PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --db -> path to database connection definition'.PHP_EOL;
		echo ' --chdir -> enter directory before connecting'.PHP_EOL;
		echo ' --pre -> execute script before connecting'.PHP_EOL;
		echo ' --post -> execute script before exit'.PHP_EOL;
		echo ' --reseed -> force start seeder'.PHP_EOL;
		echo PHP_EOL;
		echo 'Note:'.PHP_EOL;
		echo ' you can define callable $pcos_on_error in pre_script.php'.PHP_EOL;
		echo ' this variable will be passed to the pdo_connect function as the second argument'.PHP_EOL;
		echo ' for more info see pdo_connect.php library'.PHP_EOL;
		exit(1);
	}

	if(
		($pcos_dirname !== null) &&
		(!chdir($pcos_dirname))
	)
		exit(1);

	$pcos_on_error=null;

	if($pcos_pre_script !== null)
		require $pcos_pre_script;

	$pdo_handle=pdo_connect(
		$pcos_db_name,
		$pcos_on_error,
		true,
		$pcos_reseed
	);

	if($pcos_post_script !== null)
		require $pcos_post_script;
?>