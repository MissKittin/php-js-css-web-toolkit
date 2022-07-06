<?php
	// Replaces public/index.php with link

	echo 'chdir("'.__DIR__.'/../../public")'.PHP_EOL;
	if(!chdir(__DIR__.'/../../public'))
	{
		echo 'Cannot chdir to '.__DIR__.'/../../public'.PHP_EOL;
		exit(1);
	}

	if(!is_file('./index.php'))
	{
		echo 'index.php is not a file'.PHP_EOL;
		exit(1);
	}

	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		echo 'MS Windows detected, using mklink'.PHP_EOL;

		echo 'Testing mklink';
		if(@exec('mklink .\\index-test.php .\\index.php') !== 'symbolic link created for .\index-test.php <<===>> .\index.php')
		{
			echo ' mklink failed'.PHP_EOL;
			@unlink('.\\index-test.php');
			exit(1);
		}
		unlink('.\\index-test.php');
		echo ' OK'.PHP_EOL;

		echo 'unlink(".\\index.php")'.PHP_EOL;
		unlink('.\\index.php');

		echo 'exec("mklink .\\index.php ..\\app\\entrypoint.php")'.PHP_EOL;
		exec('mklink .\\index.php ..\\app\\entrypoint.php');
	}
	else
	{
		echo 'MS Windows not detected, using symlink()'.PHP_EOL;

		echo 'Testing symlink';
		@symlink('./index.php', './index-test.php');
		if(@readlink('./index-test.php') === false)
		{
			echo ' symlink failed'.PHP_EOL;
			@unlink('./index-test.php');
			exit(1);
		}
		unlink('./index-test.php');
		echo ' OK'.PHP_EOL;

		echo 'unlink("./index.php")'.PHP_EOL;
		unlink('./index.php');

		echo 'symlink("../app/entrypoint.php", "./index.php")'.PHP_EOL;
		symlink('../app/entrypoint.php', './index.php');
	}
?>