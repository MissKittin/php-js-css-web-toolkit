<?php
	/*
	 * Cache generator for check_easter_cache()
	 *
	 * Warning:
	 *  check_date.php library is required
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
			else
				if($required)
					throw new Exception($library.' library not found');
	}

	try {
		load_library(['check_date.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if(!isset($argv[1]))
	{
		echo 'No output file given'.PHP_EOL;
		echo 'Usage: check-easter-mkcache.php path/to/output-file|--stdout'.PHP_EOL;
		exit(1);
	}
	if($argv[1] === '--stdout')
	{
		echo check_easter__make_cache();
		exit();
	}
	if(file_exists($argv[1]))
	{
		echo 'Output file exists'.PHP_EOL;
		exit(1);
	}
	if(file_put_contents($argv[1], check_easter__make_cache()) === false)
	{
		echo 'File cannot be saved'.PHP_EOL;
		exit(1);
	}
?>