<?php
	// Quickly dump SQLite3 database

	if($argc < 2)
	{
		echo 'Usage: database.file [output-file.sql]'.PHP_EOL;
		echo ' if output-file.sql is not specified, prints to stdout'.PHP_EOL;
		exit(1);
	}

	if(!file_exists($argv[1]))
	{
		echo $argv[1].' not exists'.PHP_EOL;
		exit(1);
	}

	include __DIR__.'/../lib/sqlite3_db_dump.php';

	$output=sqlite3_db_dump($argv[1]);

	if($output === '')
		echo 'Database dump is empty';
	else
	{
		if(isset($argv[2]))
			file_put_contents($argv[2], $output);
		else
			echo $output;
	}
?>