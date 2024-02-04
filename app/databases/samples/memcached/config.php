<?php
	if(getenv('MEMCACHED_IGNORE_ENV') === 'true')
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

	return [[
		'host'=>$db_getenv('MEMCACHED_HOST', '127.0.0.1'),
		'port'=>$db_getenv('MEMCACHED_PORT', 11211)
	]];
?>