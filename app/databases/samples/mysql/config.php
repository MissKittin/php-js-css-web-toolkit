<?php
	$db_type='mysql'; // sqlite pgsql mysql
	$db_host='[::1]';
	//$db_socket='/tmp/mysql.sock';
	$db_port='3306';
	$db_name='sampledb';
	$db_charset='utf8mb4';
	$db_user='root';
	$db_password='';
	//$db_seeded_path=$db;

	// you can implement the var/databases hierarchy
	$var_databases=__DIR__.'/../../../..';
	if(!file_exists($var_databases.'/var/databases/mysql'))
	{
		@mkdir($var_databases.'/var');
		@mkdir($var_databases.'/var/databases');
		mkdir($var_databases.'/var/databases/mysql');
	}
	$db_seeded_path=$var_databases.'/var/databases/mysql';
?>