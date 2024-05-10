<?php
	$polyfill_map=[
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
	];

	function append_library($libraries)
	{
		foreach($libraries as $library)
		{
			if(file_exists(__DIR__.'/../lib/pf_'.$library.'.php'))
				$file=__DIR__.'/../lib/pf_'.$library.'.php';
			else if(file_exists(__DIR__.'/../../../lib/pf_'.$library.'.php'))
				$file=__DIR__.'/../../../lib/pf_'.$library.'.php';
			else
			{
				echo 'Error: pf_'.$library.'.php library not found'.PHP_EOL;
				@unlink(__DIR__.'/../_php_polyfill_cache');
				exit(1);
			}

			file_put_contents(
				__DIR__.'/../_php_polyfill_cache',
				php_strip_whitespace($file),
				FILE_APPEND
			);

			if(!has_php_close_tag(file_get_contents($file)))
				file_put_contents(
					__DIR__.'/../_php_polyfill_cache',
					' ?>',
					FILE_APPEND
				);
		}

		file_put_contents(
				__DIR__.'/../_php_polyfill_cache',
				'<?php }',
				FILE_APPEND
			);
	}
	function render_content()
	{
		global $polyfill_map;

		file_put_contents(
			__DIR__.'/../_php_polyfill_cache',
			'<?php '
		);

		foreach($polyfill_map as $php_version=>$libraries)
		{
			file_put_contents(
				__DIR__.'/../_php_polyfill_cache',
				'if(PHP_VERSION_ID < '.$php_version.'){ ?>',
				FILE_APPEND
			);

			append_library($libraries);
		}

		file_put_contents(
			__DIR__.'/../_php_polyfill_cache',
			' ?>',
			FILE_APPEND
		);

		file_put_contents(
			__DIR__.'/../_php_polyfill_cache',
			str_replace(
				['<?php'."\n", '<?php'."\r\n", '?><?php'],
				['<?php ', '<?php', ''],
				file_get_contents(__DIR__.'/../_php_polyfill_cache')
			)
		);
	}

	if(isset($argv[1]))
	{
		switch($argv[1])
		{
			case '-h':
			case '--help':
				echo 'Usage: '.basename(__FILE__).' [--remove]'.PHP_EOL;
			break;
			case '--remove':
				if(!is_file(__DIR__.'/../main_original.php'))
				{
					echo __DIR__.'/../main_original.php is not a file'.PHP_EOL;
					echo 'Maybe '.basename(__FILE__).' was not used yet'.PHP_EOL;
					echo ' or this file was removed'.PHP_EOL;
					exit(1);
				}

				@unlink(__DIR__.'/../main.php');
				rename(
					__DIR__.'/../main_original.php',
					__DIR__.'/../main.php'
				);
			break;
			default:
				echo 'Unrecognized option: '.$argv[1].PHP_EOL;
				exit(1);
		}

		exit();
	}

	if(file_exists(__DIR__.'/../lib/has_php_close_tag.php'))
		require __DIR__.'/../lib/has_php_close_tag.php';
	else if(file_exists(__DIR__.'/../../../lib/has_php_close_tag.php'))
		require __DIR__.'/../../../lib/has_php_close_tag.php';
	else
	{
		echo 'Error: has_php_close_tag.php library not found'.PHP_EOL;
		exit(1);
	}

	render_content();

	if(file_exists(__DIR__.'/../main.php'))
		rename(
			__DIR__.'/../main.php',
			__DIR__.'/../main_original.php'
		);

	rename(
		__DIR__.'/../_php_polyfill_cache',
		__DIR__.'/../main.php'
	);
?>