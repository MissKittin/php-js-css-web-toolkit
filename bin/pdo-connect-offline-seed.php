<?php
	// run database seeder offline

	if(!isset($argv[1])) die('No database config path'.PHP_EOL.'Usage: pdo_connect_offline_seed.php ./pathTo/databaseConfig'.PHP_EOL);

	chdir(__DIR__ . '/..');

	include './lib/pdo_connect.php';
	if(file_exists('./lib/pdo_crud_builder.php')) include './lib/pdo_crud_builder.php';

	pdo_connect($argv[1]);
?>