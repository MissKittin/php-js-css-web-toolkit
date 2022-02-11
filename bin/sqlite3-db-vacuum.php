<?php
	// Vacuum SQLite3 database

	if(!extension_loaded('PDO'))
	{
		echo 'PDO extension is not loaded'.PHP_EOL;
		exit(1);
	}
	if(!extension_loaded('pdo_sqlite'))
	{
		echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
		exit(1);
	}

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

	try {
		$db=new PDO('sqlite:'.$argv[1]);
		$db->exec('VACUUM');
	} catch(PDOException $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}
?>