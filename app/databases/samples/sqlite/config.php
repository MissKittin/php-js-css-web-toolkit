<?php
	$db_type='sqlite'; // sqlite pgsql mysql
	$db_host=$db.'/database.sqlite3';
	//$db_socket='';
	//$db_port='';
	//$db_name='';
	//$db_charset='';
	//$db_user='';
	//$db_password='';
	//$db_seeded_path=$db;

	// you can implement the var/databases hierarchy
	$var_databases=__DIR__.'/../../../..';
	if(!file_exists($var_databases.'/var/databases/sqlite'))
	{
		@mkdir($var_databases.'/var');
		@mkdir($var_databases.'/var/databases');
		mkdir($var_databases.'/var/databases/sqlite');
	}
	$db_host=$var_databases.'/var/databases/sqlite/database.sqlite3';
	$db_seeded_path=$var_databases.'/var/databases/sqlite';
?>