<?php
	/*
	 * Assets installer for example application
	 *
	 * Warning:
	 *  copy_recursive.php library is required
	 */

	if(!isset($argv[1]))
	{
		echo 'Usage:'.PHP_EOL;
		echo ' '.basename(__FILE__).' cp|ln|mklink'.PHP_EOL;
		echo PHP_EOL;
		echo 'Where:'.PHP_EOL;
		echo ' cp -> copy files'.PHP_EOL;
		echo ' ln -> create symlink (*nix only)'.PHP_EOL;
		echo ' mklink -> use windows mklink utility'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including copy_recursive.php';
		if(@(include __DIR__.'/../../lib/copy_recursive.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	chdir(__DIR__.'/..');
	@mkdir('./assets');

	$files_cp=[
		'./templates/samples/default/assets/default_bright.css'=>'./assets/default_bright.css',
		'./templates/samples/default/assets/default_dark.css'=>'./assets/default_dark.css',
		'./templates/samples/default/assets/default.js'=>'./assets/default.js',
		'../lib/sendNotification.js'=>'./assets/sendNotification.js',
		'../components/login/assets/login_bright.css'=>'./assets/login_bright.css',
		'../components/login/assets/login_dark.css'=>'./assets/login_dark.css',
		'../components/middleware_form/assets/middleware_form_bright.css'=>'./assets/middleware_form_bright.css',
		'../components/middleware_form/assets/middleware_form_dark.css'=>'./assets/middleware_form_dark.css'
	];
	$files_nix=[
		'../templates/samples/default/assets/default_bright.css'=>'./assets/default_bright.css',
		'../templates/samples/default/assets/default_dark.css'=>'./assets/default_dark.css',
		'../templates/samples/default/assets/default.js'=>'./assets/default.js',
		'../../lib/sendNotification.js'=>'./assets/sendNotification.js',
		'../../components/login/assets/login_bright.css'=>'./assets/login_bright.css',
		'../../components/login/assets/login_dark.css'=>'./assets/login_dark.css',
		'../../components/middleware_form/assets/middleware_form_bright.css'=>'./assets/middleware_form_bright.css',
		'../../components/middleware_form/assets/middleware_form_dark.css'=>'./assets/middleware_form_dark.css'
	];
	$files_win=[
		'.\\assets\\default_bright.css'=>'..\\templates\\samples\\default\\assets\\default_bright.css',
		'.\\assets\\default_dark.css'=>'..\\templates\\samples\\default\\assets\\default_dark.css',
		'.\\assets\\default.js'=>'..\\templates\\samples\\default\\assets\\default.js',
		'.\\assets\\sendNotification.js'=>'..\\..\\lib\\sendNotification.js',
		'.\\assets\\login_bright.css'=>'..\\..\\components\\login\\assets\\login_bright.css',
		'.\\assets\\login_dark.css'=>'..\\..\\components\\login\\assets\\login_dark.css',
		'.\\assets\\middleware_form_bright.css'=>'..\\..\\components\\middleware_form\\assets\\middleware_form_bright.css',
		'.\\assets\\middleware_form_dark.css'=>'..\\..\\components\\middleware_form\\assets\\middleware_form_dark.css'
	];

	switch($argv[1])
	{
		case 'cp':
			foreach($files_cp as $target=>$destination)
			{
				echo $target.' => '.$destination.PHP_EOL;
				copy_recursive($target, $destination);
			}
		break;
		case 'ln':
			@symlink(basename(__FILE__), './bin/install-assets-test');
			if(@readlink('./bin/install-assets-test') === false)
			{
				echo 'Cannot create symlink'.PHP_EOL;
				@unlink('./bin/install-assets-test');
				exit(1);
			}
			unlink('./bin/install-assets-test');

			foreach($files_nix as $target=>$destination)
			{
				echo $target.' => '.$destination.PHP_EOL;
				symlink($target, $destination);
			}
		break;
		case 'mklink':
			if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
			{
				echo 'This is not a MS Windows'.PHP_EOL;
				exit(1);
			}

			if(@exec('mklink bin\\install-assets-test '.basename(__FILE__)) !== 'symbolic link created for bin\install-assets-test <<===>> install-assets.php')
			{
				echo 'mklink failed'.PHP_EOL;
				@unlink('bin\\install-assets-test');
				exit(1);
			}
			unlink('bin\\install-assets-test');

			foreach($files_win as $link=>$target)
			{
				echo $target.' => '.$link.PHP_EOL;

				$dir_switch='';
				if(is_dir($target))
					$dir_switch='/d';

				exec('mklink '.$dir_switch.' '.$link.' '.$target);
			}
		break;
		default:
			echo 'Invaild argument'.PHP_EOL;
			echo PHP_EOL;
			echo 'Usage:'.PHP_EOL;
			echo ' '.basename(__FILE__).' cp|ln|mklink'.PHP_EOL;
			echo PHP_EOL;
			echo 'Where:'.PHP_EOL;
			echo ' cp -> copy files'.PHP_EOL;
			echo ' ln -> create symlink (*nix only)'.PHP_EOL;
			echo ' mklink -> use windows mklink utility'.PHP_EOL;
			exit(1);
	}
?>