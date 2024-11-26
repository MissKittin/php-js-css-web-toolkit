<?php
	/*
	 * Compile project assets
	 *
	 * Warning:
	 *  assets_compiler.php library is required
	 */

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
		load_library(['assets_compiler.php']);
	} catch(Exception $error) {
		echo 'Error: '.$error->getMessage().PHP_EOL;
		exit(1);
	}

	if($argc < 3)
	{
		echo 'Usage: ./app/assets ./public/assets'.PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' ./app/assets is source directory'.PHP_EOL;
		echo ' ./public/assets is output directory'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[1]))
	{
		echo $argv[1].' is not a directory'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[2]))
	{
		if(mkdir($argv[2]))
			echo $argv[2].' created'.PHP_EOL;
		else
		{
			echo 'mkdir '.$argv[2].' failed'.PHP_EOL;
			exit(1);
		}
	}

	$errors=[];

	foreach(array_diff(scandir($argv[1]), ['.', '..']) as $asset)
	{
		switch(true)
		{
			case (is_file($argv[1].'/'.$asset.'/main.php')):
				echo ' -> Processing '.$asset.'/main.php'.PHP_EOL;
			break;
			case (is_dir($argv[1].'/'.$asset)):
				echo ' -> Processing '.$asset.PHP_EOL;

				foreach(assets_compiler($argv[1].'/'.$asset, $argv[2].'/'.$asset) as $file)
					echo '  -> '.$file.PHP_EOL;

				continue;
			break;
			default:
				echo ' -> Copying '.$asset.PHP_EOL;
		}

		switch(assets_compiler($argv[1].'/'.$asset, $argv[2].'/'.$asset))
		{
			case 1:
				$errors[]='unable to write '.$argv[2].'/'.$asset;
			break;
			case 2:
				$errors[]=$asset.' copy failed';
		}
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo 'Error: '.$error.PHP_EOL;

		exit(1);
	}
?>