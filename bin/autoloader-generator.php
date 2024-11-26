<?php
	/*
	 * A toy that scans PHP files and generates an autoloader script
	 *
	 * Warning:
	 *  check_var.php library is required
	 *  find_php_definitions.php library is required
	 *  relative_path.php library is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function generate_autoloader(
		$classes,
		$functions,
		$debug,
		$namespace
	){
		$output.='<?php ';

		if(
			(!empty($classes)) ||
			(!empty($functions))
		){
			if($namespace !== null)
				$output.='namespace '.$namespace.'{';

			if(!empty($classes))
			{
				$output.=''
				.'spl_autoload_register(function($c){'
				.	'switch(strtolower($c))'
				.	'{';

						foreach($classes as $class=>$file)
						{
							$output.='case \''.strtolower($class).'\':';

								if($debug)
									$output.='error_log(__FILE__.\' autoloader: loading \'.__DIR__.\'/'.$file.'\');';

								$output.='require __DIR__.\'/'.$file.'\'';

							$output.=';break;';
						}

					$output.='}'
				.'});';
			}

			if(!empty($functions))
			{
				$output.=''
				.'function load_function(string $f)'
				.'{'
				.	'if(!function_exists($f))'
				.		'switch(strtolower($f))'
				.		'{';
							foreach($functions as $function=>$file)
							{
								$output.='case \''.strtolower($function).'\':';

									if($debug)
										$output.='error_log(__FILE__.\' load_function: loading \'.__DIR__.\'/'.$file.'\');';

									$output.='require __DIR__.\'/'.$file.'\'';

								$output.=';break;';
							}

							$output.='default:';
								if($debug)
									$output.='error_log(__FILE__.\' load_function: function \'.$f.\' not found\');';

								$output.='return false;'
				.		'}'
				.	'return true;'
				.'}';
			}

			if($namespace !== null)
				$output.='}';
		}

		return $output.' ?>';
	}
	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
		{
			if(file_exists(__DIR__.'/lib/'.$library))
			{
				require __DIR__.'/lib/'.$library;
				continue;
			}

			if(file_exists(__DIR__.'/../lib/'.$library))
			{
				require __DIR__.'/../lib/'.$library;
				continue;
			}

			if($required)
				throw new Exception($library.' library not found');
		}
	}

	try {
		load_library([
			'check_var.php',
			'find_php_definitions.php',
			'relative_path.php'
		]);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	$input_dirs=check_argv_next_param_many('--in');
	$output_file=check_argv_next_param('--out');
	$ignores=check_argv_next_param_many('--ignore');
	$namespace=check_argv_next_param('--namespace');
	$debug=check_argv('--debug');
	$output_file_dir=dirname($output_file);

	if(
		($input_dirs === null) ||
		($output_file === null) ||
		check_argv('--help') || check_argv('-h')
	){
		echo 'Usage:'.PHP_EOL;
		echo ' '.$argv[0].' --in path/to/dir_a --in path/to/dir_b [--ignore filename] [--ignore dirname/] [--ignore dir/file] [--namespace MyNamespace] --out path/to/your_autoloader.php [--debug]'.PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --debug adds the ability to track loaded files to the autoloader via error_log()'.PHP_EOL;
		echo PHP_EOL;
		echo 'Warning:'.PHP_EOL;
		echo ' the generated autoloader does not check if the file'.PHP_EOL;
		echo ' that existed at the time of generation exists at the time of loading'.PHP_EOL;
		echo PHP_EOL;
		echo 'Loading functions:'.PHP_EOL;
		echo '  load_function(\'function_name\')'.PHP_EOL;
		echo ' or if a namespace is defined'.PHP_EOL;
		echo '  MyNamespace\load_function(\'function_name\')'.PHP_EOL;
		echo '  returns bool'.PHP_EOL;
		exit(1);
	}

	foreach($input_dirs as $input_dir)
		if(!is_dir($input_dir))
		{
			echo $input_dir.' is not a directory'.PHP_EOL;
			exit(1);
		}

	if(@file_put_contents($output_file, '') === false)
	{
		echo 'Cannot create '.$output_file.PHP_EOL;
		exit(1);
	}

	if($ignores === null)
		$ignores=[];

	$classes=[];
	$functions=[];

	foreach($input_dirs as $input_dir)
		foreach(new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($input_dir, RecursiveDirectoryIterator::SKIP_DOTS)
		) as $input_file)
			if(
				(!is_dir($input_file)) &&
				(pathinfo($input_file, PATHINFO_EXTENSION) === 'php')
			){
				foreach($ignores as $ignore)
					if(strpos(
						strtr($input_file->getPathname(), '\\', '/'),
						strtr($ignore, '\\', '/')
					) !== false){
						echo '[IGN] '.$input_file->getPathname().PHP_EOL;
						continue 2;
					}

				echo $input_file.PHP_EOL;

				try {
					$definitions=find_php_definitions(file_get_contents($input_file), true);
				} catch(find_php_definitions_exception $error) {
					echo ' error: '.$error->getMessage().PHP_EOL;
					continue;
				}

				foreach($definitions['classes'] as $class)
				{
					if(isset($classes[$class]))
					{
						if($classes[$class] === relative_path($output_file_dir, $input_file))
							continue;

						echo ' error: class '.$class.' already exists in '.$classes[$class].PHP_EOL;

						continue;
					}

					echo ' found class '.$class.PHP_EOL;
					$classes[$class]=relative_path($output_file_dir, $input_file);
				}
				foreach($definitions['interfaces'] as $interface)
				{
					if(isset($classes[$interface]))
					{
						if($classes[$interface] === relative_path($output_file_dir, $input_file))
							continue;

						echo ' error: interface '.$interface.' already exists in '.$classes[$interface].PHP_EOL;

						continue;
					}

					echo ' found interface '.$interface.PHP_EOL;
					$classes[$interface]=relative_path($output_file_dir, $input_file);
				}
				foreach($definitions['traits'] as $trait)
				{
					if(isset($classes[$trait]))
					{
						if($classes[$trait] === relative_path($output_file_dir, $input_file))
							continue;

						echo ' error: trait '.$trait.' already exists in '.$classes[$trait].PHP_EOL;

						continue;
					}

					echo ' found trait '.$trait.PHP_EOL;
					$classes[$trait]=relative_path($output_file_dir, $input_file);
				}
				foreach($definitions['functions'] as $function)
				{
					if(isset($functions[$function]))
					{
						if($functions[$function] === relative_path($output_file_dir, $input_file))
							continue;

						echo ' error: function '.$function.' already exists in '.$functions[$function].PHP_EOL;

						continue;
					}

					echo ' found function '.$function.PHP_EOL;
					$functions[$function]=relative_path($output_file_dir, $input_file);
				}
			}

	if(@file_put_contents($output_file, generate_autoloader(
		$classes,
		$functions,
		$debug,
		$namespace
	)) === false){
		echo 'Cannot create '.$output_file.PHP_EOL;
		exit(1);
	}
?>