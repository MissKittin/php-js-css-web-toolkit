<?php
	/*
	 * Easily add composer to your project
	 *
	 * Dangars note:
	 *  add --no-installer-verification parameter
	 *  to skip installer vefification
	 *
	 * Warning:
	 *  curl extension is recommended
	 *  curl_file_updown.php library is required for curl
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	$composer_meta=[
		'signature_url'=>'https://composer.github.io/installer.sig',
		'installer_url'=>'https://getcomposer.org/installer',
		'signature_file'=>'./.get-composer-installer.php.sig',
		'installer_file'=>'./.get-composer-installer.php',
		'composer_phar'=>'./composer.phar'
	];

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

	$GLOBALS['force_copy']=false;
	if(extension_loaded('curl'))
		try {
			load_library(['curl_file_updown.php']);
		} catch(Exception $error) {
			echo $error->getMessage().', using copy'.PHP_EOL;
			$GLOBALS['force_copy']=true;
		}

	chdir(__DIR__);

	foreach(['signature', 'installer'] as $file)
		if(file_exists($composer_meta[$file.'_file']))
		{
			echo __DIR__.DIRECTORY_SEPARATOR.$composer_meta[$file.'_file'].' exists, exiting'.PHP_EOL;
			exit(1);
		}

	if(file_exists($composer_meta['composer_phar']))
	{
		echo 'Composer already installed in '.__DIR__.DIRECTORY_SEPARATOR.$composer_meta['composer_phar'].PHP_EOL;
		exit(1);
	}

	echo 'Composer will be installed in '.getcwd().DIRECTORY_SEPARATOR.$composer_meta['composer_phar'].PHP_EOL;

	if(extension_loaded('curl') && (!$GLOBALS['force_copy']))
		foreach(['signature', 'installer'] as $file)
		{
			echo 'Downloading '.$file.' via curl'.PHP_EOL;

			curl_file_download(
				$composer_meta[$file.'_url'],
				$composer_meta[$file.'_file'],
				[
					'on_error'=>function($error) use($file)
					{
						echo 'Failed to download '.$file.': '.$error.PHP_EOL;

						@unlink($composer_meta['signature_file']);
						@unlink($composer_meta['installer_file']);

						exit(1);
					}
				]
			);
		}
	else
		foreach(['signature', 'installer'] as $file)
		{
			echo 'Downloading '.$file.' via copy'.PHP_EOL;

			if(!copy($composer_meta[$file.'_url'], $composer_meta[$file.'_file']))
			{
				echo 'Failed to download '.$file.PHP_EOL;

				@unlink($composer_meta['signature_file']);
				@unlink($composer_meta['installer_file']);

				exit(1);
			}
		}

	foreach(['signature', 'installer'] as $file)
		if(!file_exists($composer_meta[$file.'_file']))
		{
			echo 'Failed to download '.$file.PHP_EOL;
			exit(1);
		}

	echo 'Verifying installer';
		if(isset($argv[1]) && ($argv[1] === '--no-installer-verification'))
			echo ' [SKIP] !!!'.PHP_EOL;
		else
		{
			echo PHP_EOL;

			if(
				hash_file('sha384', $composer_meta['installer_file'])
				==
				trim(file_get_contents($composer_meta['signature_file']))
			)
				echo 'Installer verified'.PHP_EOL;
			else
			{
				echo 'Installer corrupted'.PHP_EOL;

				unlink($composer_meta['signature_file']);
				unlink($composer_meta['installer_file']);

				exit(1);
			}
		}

	echo 'Starting installer'.PHP_EOL;
		system(PHP_BINARY.' '.$composer_meta['installer_file']);

	echo 'Removing installer'.PHP_EOL;
	{
		unlink($composer_meta['signature_file']);
		unlink($composer_meta['installer_file']);
	}
?>