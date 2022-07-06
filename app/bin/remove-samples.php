<?php
	/*
	 * Remove the sample application
	 *
	 * Note:
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	echo ' -> Including rmdir_recursive.php';
		if(@(include __DIR__.'/../../lib/rmdir_recursive.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	chdir(__DIR__.'/..');

	foreach(array_slice(scandir('.'), 2) as $directory)
		if(is_dir('./'.$directory.'/samples'))
		{
			echo ' -> Removing '.$directory.'/samples';
				if(@rmdir_recursive('./'.$directory.'/samples'))
					echo ' [ OK ]'.PHP_EOL;
				else
					echo ' [FAIL]'.PHP_EOL;

		}

	foreach(['assets', 'databases', 'shared', 'templates'] as $directory)
		if(is_dir('./'.$directory))
		{
			echo ' -> Removing '.$directory;
				if(@rmdir('./'.$directory))
					echo ' [ OK ]'.PHP_EOL;
				else
					echo ' [FAIL]'.PHP_EOL;
		}

	echo ' -> Editing entrypoint.php';
		$entrypoint=file_get_contents('./entrypoint.php');
		file_put_contents(
			'./entrypoint.php',
			preg_replace_callback(
				'/{((?:[^{}]*|(?R))*)}/x',
				function($match){
					if(strpos($match[0], 'case') !== false)
						return ''
							.'{'
								."\n\t\t".'//'
							."\n\t".'}'
						;

					return ''
						.'{'
							."\n\t\t".'//'
							."\n\t\t".'exit();'
						."\n\t".'}'
					;
				},
				$entrypoint
			)
		);
		unset($entrypoint);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing tools';
		foreach(array_slice(scandir('./bin'), 2) as $file)
			if(@unlink('./bin/'.$file))
				echo ' [ OK ]';
			else
				echo ' [FAIL]';
		echo PHP_EOL;

	echo ' -> Removing bin directory';
		if(@rmdir('./bin'))
			echo ' [ OK ]'.PHP_EOL;
		else
			echo ' [FAIL]'.PHP_EOL;
?>