<?php
	(function($library_list){
		foreach($library_list as $version=>$libraries)
			if(PHP_VERSION_ID < $version)
				foreach($libraries as $library)
				{
					if(file_exists(__DIR__.'/lib/pf_'.$library.'.php'))
						require_once __DIR__.'/lib/pf_'.$library.'.php';
					else if(file_exists(__DIR__.'/../../lib/pf_'.$library.'.php'))
						require_once __DIR__.'/../../lib/pf_'.$library.'.php';
					else
						throw new Exception('pf_'.$library.'.php library not found');
				}
	})([
		PHP_INT_MAX=>['getallheaders'], // all PHP versions
		70200=>[
			'mbstring', // mb_chr() mb_ord() mb_scrub()
			'php_float',
			'php_os_family',
			'stream_isatty',
			'spl_object_id'
		],
		70300=>[
			'array', // array_key_first() array_key_last()
			'is_countable'
		],
		70400=>['mbstring'], // mb_str_split()
		80000=>[
			'get_debug_type',
			'str', // str_contains() str_ends_with() str_starts_with()
			'Stringable',
			'ValueError'
		],
		80100=>['array'], // array_is_list()
		80300=>[
			'mbstring', // mb_str_pad()
			'json_validate'
		]
	]);
?>