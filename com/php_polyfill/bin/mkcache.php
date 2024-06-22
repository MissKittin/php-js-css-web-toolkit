<?php
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
		file_put_contents(
			__DIR__.'/../_php_polyfill_cache',
			'<?php '
		);

		foreach(
			require __DIR__.'/../registry.php'
			as $php_version=>$libraries
		){
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

	$output_file=null;

	if(isset($argv[1]))
	{
		switch($argv[1])
		{
			case '-h':
			case '--help':
				echo 'Usage: '.basename(__FILE__).PHP_EOL;
				echo 'Usage: '.basename(__FILE__).' --out path/to/file.php'.PHP_EOL;
				echo 'Usage: '.basename(__FILE__).' --remove'.PHP_EOL;
				exit();
			case '--remove':
				if(!is_file(__DIR__.'/../main_original.php'))
				{
					echo 'Error: '.__DIR__.'/../main_original.php is not a file'.PHP_EOL;
					echo 'Maybe '.basename(__FILE__).' was not used yet'.PHP_EOL;
					echo ' or this file was removed'.PHP_EOL;
					exit(1);
				}

				@unlink(__DIR__.'/../main.php');
				rename(
					__DIR__.'/../main_original.php',
					__DIR__.'/../main.php'
				);

				exit();
			case '--out':
				if(!isset($argv[2]))
				{
					echo 'Error: no output file path given'.PHP_EOL;
					echo 'Usage: '.basename(__FILE__).' --out path/to/file.php'.PHP_EOL;
					exit(1);
				}

				if(file_exists($argv[2]))
				{
					echo 'Error: '.$argv[2].' already exists'.PHP_EOL;
					exit(1);
				}

				$output_file=$argv[2];
			break;
			default:
				echo 'Error: unrecognized option: '.$argv[1].PHP_EOL;
				exit(1);
		}
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

	if(($output_file === null) && file_exists(__DIR__.'/../main.php'))
	{
		rename(
			__DIR__.'/../main.php',
			__DIR__.'/../main_original.php'
		);
		rename(
			__DIR__.'/../_php_polyfill_cache',
			__DIR__.'/../main.php'
		);

		exit();
	}

	rename(
		__DIR__.'/../_php_polyfill_cache',
		$output_file
	);

	if(file_exists(__DIR__.'/../_php_polyfill_cache'))
		unlink(__DIR__.'/../_php_polyfill_cache');
?>