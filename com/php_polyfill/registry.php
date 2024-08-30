<?php
	return [
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
			'json_validate',
			'str' // str_decrement() str_increment()
		],
		80400=>[
			'array', // array_all() array_any() array_find() array_find_key()
			'mbstring' // mb_lcfirst() mb_ucfirst()
		]
	];
?>