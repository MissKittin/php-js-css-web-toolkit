<?php
	// Vacuum SQLite3 database

	if($argc < 2)
	{
		echo 'Usage: database.file'.PHP_EOL;
		exit(1);
	}

	if(!file_exists($argv[1]))
	{
		echo $argv[1].' not exists'.PHP_EOL;
		exit(1);
	}

	$db=new PDO('sqlite:'.$argv[1]);
	$db->exec('VACUUM');
?>