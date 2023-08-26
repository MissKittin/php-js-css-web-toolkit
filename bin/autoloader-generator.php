<?php
	/*
	 * A toy that scans PHP files and generates an autoloader script
	 *
	 * Warning:
	 *  use at your own risk
	 *  check_var.php library is required
	 *  find_php_definitions.php library is required
	 *  relative_path.php library is required
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	function generate_autoloader($classes, $functions, $debug)
	{
		$a='';

		if((!empty($classes)) || (!empty($functions)))
		{
			$a.='<?php ';

			if(!empty($classes))
			{
				$a.='spl_autoload_register(function($c){'
					.'switch(strtolower($c))'
					.'{';
						foreach($classes as $class=>$file)
						{
							$a.='case \''.strtolower($class).'\':';
								if($debug)
									$a.='error_log(__FILE__.\' autoloader: loading \'.__DIR__.\'/'.$file.'\');';
								$a.='require __DIR__.\'/'.$file.'\''
							.';break;';
						}
					$a.='}'
				.'});';
			}

			if(!empty($functions))
			{
				$a.='function load_function(string $f)'
				.'{'
					.'if(!function_exists($f))'
						.'switch(strtolower($f))'
						.'{';
							foreach($functions as $function=>$file)
							{
								$a.='case \''.strtolower($function).'\':';
									if($debug)
										$a.='error_log(__FILE__.\' load_function: loading \'.__DIR__.\'/'.$file.'\');';
									$a.='require __DIR__.\'/'.$file.'\''
								.';break;';
							}
							$a.='default:';
								if($debug)
									$a.='error_log(__FILE__.\' load_function: function \'.$f.\' not found\');';
								$a.='return false;'
						.'}'
					.'return true;'
				.'}';
			}

			$a.=' ?>';
		}

		return $a;
	}
	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
			if(file_exists(__DIR__.'/lib/'.$library))
				require __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				require __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
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
	$debug=check_argv('--debug');
	$output_file_dir=dirname($output_file);

	if(
		($input_dirs === null) ||
		($output_file === null) ||
		check_argv('--help') ||
		check_argv('-h')
	){
		echo 'Usage:'.PHP_EOL;
		echo ' --in path/to/dir_a --in path/to/dir_b --out path/to/your_autoloader.php --debug'.PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' --debug adds the ability to track loaded files to the autoloader via error_log()'.PHP_EOL;
		echo PHP_EOL;
		echo 'Warning:'.PHP_EOL;
		echo ' the generated autoloader does not check if the file'.PHP_EOL;
		echo ' that existed at the time of generation exists at the time of loading'.PHP_EOL;
		echo PHP_EOL;
		echo 'Loading functions:'.PHP_EOL;
		echo '  load_function(\'function_name\')'.PHP_EOL;
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

	$classes=[];
	$functions=[];
	foreach($input_dirs as $input_dir)
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($input_dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $input_file)
			if(
				(!is_dir($input_file)) &&
				(pathinfo($input_file, PATHINFO_EXTENSION) === 'php')
			){
				echo $input_file.PHP_EOL;
				$definitions=find_php_definitions(file_get_contents($input_file));

				foreach($definitions['classes'] as $class)
				{
					if(isset($classes[$class]))
					{
						echo ' error: class '.$class.' already exists in '.$classes[$class].PHP_EOL;
						exit(1);
					}

					echo ' found class '.$class.PHP_EOL;
					$classes[$class]=relative_path($output_file_dir, $input_file);
				}
				foreach($definitions['interfaces'] as $interface)
				{
					if(isset($classes[$interface]))
					{
						echo ' error: interface '.$interface.' already exists in '.$classes[$interface].PHP_EOL;
						exit(1);
					}

					echo ' found interface '.$interface.PHP_EOL;
					$classes[$interface]=relative_path($output_file_dir, $input_file);
				}
				foreach($definitions['traits'] as $trait)
				{
					if(isset($classes[$trait]))
					{
						echo ' error: trait '.$trait.' already exists in '.$classes[$trait].PHP_EOL;
						exit(1);
					}

					echo ' found trait '.$trait.PHP_EOL;
					$classes[$trait]=relative_path($output_file_dir, $input_file);
				}
				foreach($definitions['functions'] as $function)
				{
					if(isset($functions[$function]))
					{
						echo ' error: function '.$function.' already exists in '.$functions[$function].PHP_EOL;
						exit(1);
					}

					echo ' found function '.$function.PHP_EOL;
					$functions[$function]=relative_path($output_file_dir, $input_file);
				}
			}

	if(@file_put_contents($output_file, generate_autoloader($classes, $functions, $debug)) === false)
	{
		echo 'Cannot create '.$output_file.PHP_EOL;
		exit(1);
	}
?>