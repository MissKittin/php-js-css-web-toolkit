<?php
	if(getenv('DB_IGNORE_ENV') === 'true')
		$db_getenv=function($env, $default_value)
		{
			return $default_value;
		};
	else
		$db_getenv=function($env, $default_value)
		{
			$value=getenv($env);

			if($value === false)
				return $default_value;

			return $value;
		};

	$db_config=[
		'db_type'=>'mysql', // sqlite pgsql mysql
		'host'=>$db_getenv('MYSQL_HOST', '[::1]'),
		'port'=>$db_getenv('MYSQL_PORT', '3306'),
		//'socket'=>'/tmp/mysql.sock',
		'db_name'=>$db_getenv('MYSQL_DBNAME', 'sampledb'),
		'charset'=>$db_getenv('MYSQL_CHARSET', 'utf8mb4'),
		'user'=>$db_getenv('MYSQL_USER', 'root'),
		'password'=>$db_getenv('MYSQL_PASSWORD', ''),
		//'seeded_path'=>$db
	];

	// socket has priority over the host/port
	if(getenv('MYSQL_SOCKET') !== false)
		$db_config['socket']=getenv('MYSQL_SOCKET');

	// you can implement the var/databases hierarchy
	$var_databases=__DIR__.'/../../../../var/lib/databases';
	$var_databases_db_name=$db_config['db_type'];
	if(!file_exists($var_databases.'/'.$var_databases_db_name))
		@mkdir($var_databases.'/'.$var_databases_db_name, 0777, true);
	$db_config['seeded_path']=$var_databases.'/'.$var_databases_db_name;
?>