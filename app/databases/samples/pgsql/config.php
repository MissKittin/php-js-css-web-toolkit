<?php
	$db_type='pgsql'; // sqlite pgsql mysql
	$db_host='127.0.0.1';
	//$db_socket='/var/run/postgresql';
	$db_port='5432';
	$db_name='sampledb';
	$db_user='postgres';
	$db_password='postgres';
	//$db_seeded_path=$db;

	// you can implement the var/databases hierarchy
	$var_databases=__DIR__.'/../../../..';
	if(!file_exists($var_databases.'/var/databases/pgsql'))
	{
		@mkdir($var_databases.'/var');
		@mkdir($var_databases.'/var/databases');
		mkdir($var_databases.'/var/databases/pgsql');
	}
	$db_seeded_path=$var_databases.'/var/databases/pgsql';
?>