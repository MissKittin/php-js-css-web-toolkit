<?php
	/*
	 * Easily add composer to your project
	 *
	 * Warning:
	 *  curl extension is recommended
	 *
	 * lib directory path:
	 *  __DIR__/lib
	 *  __DIR__/../lib
	 */

	$composer_meta=[
		'installer_url'=>'https://getcomposer.org/installer',
		'installer_sum'=>'55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae',
		'installer_file'=>'./.get-composer-installer.php',
		'composer_phar'=>'./composer.phar'
	];

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

	$GLOBALS['force_copy']=false;
	if(extension_loaded('curl'))
		try {
			load_library(['curl_file_updown.php']);
		} catch(Exception $error) {
			echo $error->getMessage().', using copy'.PHP_EOL;
			$GLOBALS['force_copy']=true;
		}

	chdir(__DIR__);

	if(file_exists($composer_meta['installer_file']))
	{
		echo __DIR__.DIRECTORY_SEPARATOR.$composer_meta['installer_file'].' exists, exiting'.PHP_EOL;
		exit(1);
	}

	if(file_exists($composer_meta['composer_phar']))
	{
		echo 'Composer already installed in '.__DIR__.DIRECTORY_SEPARATOR.$composer_meta['composer_phar'].PHP_EOL;
		exit(1);
	}

	echo 'Composer will be installed in '.getcwd().DIRECTORY_SEPARATOR.$composer_meta['composer_phar'].PHP_EOL;

	if(extension_loaded('curl') && (!$GLOBALS['force_copy']))
	{
		$GLOBALS['curl_failed']=false;

		echo 'Downloading installer via curl'.PHP_EOL;
			curl_file_download(
				$composer_meta['installer_url'],
				$composer_meta['installer_file'],
				[
					'on_error'=>function($error)
					{
						echo 'Failed to download installer: '.$error.PHP_EOL;
						$GLOBALS['curl_failed']=true;
						exit(1);
					}
				]
			);

			if($GLOBALS['curl_failed'])
				@unlink($composer_meta['installer_file']);
	}
	else
	{
		echo 'Downloading installer via copy'.PHP_EOL;
			if(!copy($composer_meta['installer_url'], $composer_meta['installer_file']))
			{
				echo 'Failed to download installer'.PHP_EOL;
				@unlink($composer_meta['installer_file']);
				exit(1);
			}
	}

	if(!file_exists($composer_meta['installer_file']))
	{
		echo 'Failed to download installer'.PHP_EOL;
		exit(1);
	}

	echo 'Verifying installer'.PHP_EOL;
		if(hash_file('sha384', $composer_meta['installer_file']) === $composer_meta['installer_sum'])
			echo 'Installer verified'.PHP_EOL;
		else
		{
			echo 'Installer corrupt'.PHP_EOL;
			unlink($composer_meta['installer_file']);
			exit(1);
		}

		echo 'Starting installer'.PHP_EOL;
			system(PHP_BINARY.' '.$composer_meta['installer_file']);

		echo 'Removing installer'.PHP_EOL;
			unlink($composer_meta['installer_file']);
?>