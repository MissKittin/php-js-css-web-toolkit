<?php
	// webdev.sh client

	if($argc < 2)
	{
		echo 'Usage: ./public/assets'.PHP_EOL;
		echo ' where ./public/assets is directory'.PHP_EOL;
		exit(1);
	}

	if(!is_dir($argv[1]))
	{
		echo $argv[1].' is not a directory'.PHP_EOL;
		exit(1);
	}

	include __DIR__.'/../lib/webdevsh.php';

	$minify_styles=true;
	$minify_scripts=true;

	$ignore_https=false;
	if(isset($argv[2]))
		if($argv[2] === '--no-check-certificate')
			$ignore_https=true;

	$assets=scandir($argv[1]); $assets=array_diff($assets, array('.', '..'));
	foreach($assets as $asset)
	{
		$file_extension=pathinfo($asset, PATHINFO_EXTENSION);
		if($file_extension === 'css')
		{
			if($minify_styles)
			{
				echo 'Processing ' . $asset . PHP_EOL;
				file_put_contents($argv[1] . '/' . $asset, css_minifier_com(file_get_contents($argv[1] . '/' . $asset), $ignore_https));
			}
		}
		else if($file_extension === 'js')
		{
			if($minify_scripts)
			{
				echo 'Processing ' . $asset . PHP_EOL;
				file_put_contents($argv[1] . '/' . $asset, javascript_minifier_com(file_get_contents($argv[1] . '/' . $asset), $ignore_https));
			}
		}
	}
?>