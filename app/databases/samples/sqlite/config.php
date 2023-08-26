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
		'db_type'=>'sqlite', // sqlite pgsql mysql
		'host'=>$db_getenv('SQLITE_PATH', $db.'/database.sqlite3')
		//'port'=>'',
		//,'socket'=>'',
		//'db_name'=>'',
		//'charset'=>'',
		//'user'=>'',
		//'password'=>'',
		//'seeded_path'=>$db
	];

	if(getenv('SQLITE_PATH') === false)
	{
		// you can implement the var/databases hierarchy

		$var_databases=__DIR__.'/../../../../var/lib/databases';
		$var_databases_db_name=$db_config['db_type'];

		if(!file_exists($var_databases.'/'.$var_databases_db_name))
			@mkdir($var_databases.'/'.$var_databases_db_name, 0777, true);

		$db_config['host']=$var_databases.'/'.$var_databases_db_name.'/database.sqlite3';
		$db_config['seeded_path']=$var_databases.'/'.$var_databases_db_name;
	}

	return $db_config;
?>