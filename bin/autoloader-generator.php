<?php
	/*
	 * A toy that scans PHP files and generates an SPL autoloader
	 *
	 * Warning:
	 *  use at your own risk
	 *  strip_php_comments.php library is required
	 *
	 * Usage: add to your app
		define('__LIB_DIR__', 'path/to/lib');
		include 'path/to/generated_autoloader.php';
	 */

	if(
		(!isset($argv[1])) ||
		($argv[1] === '--help') ||
		($argv[1] === '-h')
	){
		echo 'Usage: autoloader-generator.php path/to/lib'.PHP_EOL;
		echo 'PHP script will be printed to stdout'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[1]))
	{
		echo $argv[1].' is not a directory'.PHP_EOL;
		exit(1);
	}

	function load_library($libraries, $required=true)
	{
		foreach($libraries as $library)
			if(file_exists(__DIR__.'/lib/'.$library))
				include __DIR__.'/lib/'.$library;
			else if(file_exists(__DIR__.'/../lib/'.$library))
				include __DIR__.'/../lib/'.$library;
			else
				if($required)
					throw new Exception($library.' library not found');
	}

	load_library(['strip_php_comments.php']);

	$classes=array();
	foreach(array_slice(scandir($argv[1]), 2) as $file)
		if(
			(!is_dir($argv[1].'/'.$file)) &&
			(strtolower(substr($file, strrpos($file, '.')+1)) === 'php')
		){
			$source=strip_php_comments(file_get_contents($argv[1].'/'.$file));
			if(preg_match_all('/\s*[^\$](class|interface|trait)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $source, $matches))
				foreach($matches[2] as $class)
					$classes[strtolower($class)]=$file;
		}

	if(!empty($classes))
	{
		echo '<?php spl_autoload_register(function($c){'
			.'if(!defined(\'__LIB_DIR__\')) '
				.'throw new Exception(\'__LIB_DIR__ not defined\');'
			.'switch(strtolower($c)){';
				foreach($classes as $class=>$file)
					echo 'case \''.$class.'\':'
						.'include __LIB_DIR__.\'/'.$file.'\''
					.';break;';
		echo '}'
		.'}); ?>';
	}
?>