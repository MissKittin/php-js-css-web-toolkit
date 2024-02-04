<?php
	if(isset($_php_polyfill__))
		throw new Exception('You declared the $_php_polyfill__ variable, WHY???');

	foreach([
		70200=>[
			'php_float',
			'php_os_family',
			'stream_isatty',
			'spl_object_id'
		],
		70300=>[
			'array', // array_key_first() array_key_last()
			'is_countable'
		],
		80000=>[
			'str', // str_contains() str_ends_with() str_starts_with()
			'Stringable',
			'ValueError'
		],
		80100=>['array'], // array_is_list()
		80300=>['json_validate']
	] as $_php_polyfill__['version']=>$_php_polyfill__['libraries'])
		if(PHP_VERSION_ID < $_php_polyfill__['version'])
			foreach($_php_polyfill__['libraries'] as $_php_polyfill__['library'])
			{
				if(file_exists(__DIR__.'/lib/pf_'.$_php_polyfill__['library'].'.php'))
					require __DIR__.'/lib/pf_'.$_php_polyfill__['library'].'.php';
				else if(file_exists(__DIR__.'/../../lib/pf_'.$_php_polyfill__['library'].'.php'))
					require __DIR__.'/../../lib/pf_'.$_php_polyfill__['library'].'.php';
				else
					throw new Exception('pf_'.$_php_polyfill__['library'].'.php library not found');
			}

	if(!function_exists('getallheaders'))
	{
		if(file_exists(__DIR__.'/lib/pf_getallheaders.php'))
			require __DIR__.'/lib/pf_getallheaders.php';
		else if(file_exists(__DIR__.'/../../lib/pf_getallheaders.php'))
			require __DIR__.'/../../lib/pf_getallheaders.php';
		else
			throw new Exception('pf_getallheaders.php library not found');
	}

	unset($_php_polyfill__);
?>