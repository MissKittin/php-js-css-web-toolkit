<?php
	// Compile project assets

	if($argc < 3)
	{
		echo 'Usage: ./app/assets ./public/assets'.PHP_EOL;
		echo ' where ./app/assets is source directory'.PHP_EOL;
		echo ' and ./public/assets is output directory'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[1]))
	{
		echo $argv[1].' is not a directory'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[2]))
	{
		mkdir($argv[2]);
		echo $argv[2].' created'.PHP_EOL;
	}

	$assets=scandir($argv[1]);
	$assets=array_diff($assets, array('.', '..'));
	foreach($assets as $asset)
	{
		if(file_exists($argv[2].'/'.$asset))
			if(file_put_contents($argv[2].'/'.$asset, '') === false)
			{
				echo 'Unable to write '.$argv[2].'/'.$asset.PHP_EOL;
				exit(1);
			}

		if(file_exists($argv[1].'/'.$asset.'/main.php'))
		{
			echo 'Processing '.$asset.'/main.php'.PHP_EOL;

			$current_asset=$argv[1].'/'.$asset;

			ob_start(function($content){
				file_put_contents($_SERVER['argv'][2].'/'.$GLOBALS['asset'], $content, FILE_APPEND);
			});
			include $current_asset.'/main.php';
			ob_end_clean();
		}
		else if(is_dir($argv[1].'/'.$asset))
		{
			echo 'Processing '.$asset.PHP_EOL;

			$asset_files=scandir($argv[1].'/'.$asset);
			$asset_files=array_diff($asset_files, array('.', '..'));

			foreach($asset_files as $file)
				if(is_file($argv[1].'/'.$asset.'/'.$file))
				{
					echo ' - '.$file.PHP_EOL;
					file_put_contents($argv[2].'/'.$asset, file_get_contents($argv[1].'/'.$asset.'/'.$file), FILE_APPEND);
				}
				else
					echo ' '.$file.' is not a file'.PHP_EOL;
		}
		else
		{
			echo 'Copying '.$asset.PHP_EOL;
			file_put_contents($argv[2].'/'.$asset, file_get_contents($argv[1].'/'.$asset));
		}
	}
?>