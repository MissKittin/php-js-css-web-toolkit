<?php
	// compile project assets

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

	$assets=scandir($argv[1]); $assets=array_diff($assets, array('.', '..'));
	foreach($assets as $asset)
	{
		if(file_exists($argv[2] . '/' . $asset))
			unlink($argv[2] . '/' . $asset);

		if(file_exists($argv[1] . '/' . $asset . '/main.php')) // preprocessed assets
		{
			$current_asset=$argv[1] . '/' . $asset;

			echo 'Processing ' . $asset . '/main.php' . PHP_EOL;
			ob_start();
			include $current_asset . '/main.php';
			file_put_contents($argv[2] . '/' . $asset, ob_get_clean());

			unset($current_asset);
		}
		else if(is_dir($argv[1] . '/' . $asset)) // concatenated assets
		{
			echo 'Processing ' . $asset . PHP_EOL;
			$asset_files=scandir($argv[1] . '/' . $asset); $asset_files=array_diff($asset_files, array('.', '..'));
			foreach($asset_files as $file)
				if(is_file($argv[1] . '/' . $asset . '/' . $file))
				{
					echo ' - ' . $file . PHP_EOL;
					file_put_contents($argv[2] . '/' . $asset, file_get_contents($argv[1] . '/' . $asset . '/' . $file), FILE_APPEND);
				}
				else
					echo ' ' . $file . ' is not a file' . PHP_EOL;
		}
		else // single file assets
		{
			echo 'Copying ' . $asset . PHP_EOL;
			copy($argv[1] . '/' . $asset, $argv[2] . '/' . $asset);
		}
	}
?>