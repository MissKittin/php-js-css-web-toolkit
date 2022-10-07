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
		'db_type'=>'pgsql', // sqlite pgsql mysql
		'host'=>$db_getenv('PGSQL_HOST', '127.0.0.1'),
		'port'=>$db_getenv('PGSQL_PORT', '5432'),
		//'socket'=>'/var/run/postgresql',
		'db_name'=>$db_getenv('PGSQL_DBNAME', 'sampledb'),
		'charset'=>$db_getenv('PGSQL_CHARSET', 'UTF8');
		'user'=>$db_getenv('PGSQL_USER', 'postgres'),
		'password'=>$db_getenv('PGSQL_PASSWORD', 'postgres'),
		//'seeded_path'=>$db
	];

	// socket has priority over the host/port
	if(getenv('PGSQL_SOCKET') !== false)
		$db_config['socket']=getenv('PGSQL_SOCKET');

	// you can implement the var/databases hierarchy
	$var_databases=__DIR__.'/../../../../var/lib/databases';
	$var_databases_db_name=$db_config['db_type'];
	if(!file_exists($var_databases.'/'.$var_databases_db_name))
		@mkdir($var_databases.'/'.$var_databases_db_name, 0777, true);
	$db_seeded_path=$var_databases.'/'.$var_databases_db_name;
?>