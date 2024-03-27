<?php
	/*
	 * opcache-preload-generator.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	if(!is_file(__DIR__.'/../'.basename(__FILE__)))
	{
		echo 'Error: '.basename(__FILE__).' tool does not exist'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../../lib/rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@rmdir_recursive(__DIR__.'/tmp/opcache-preload-generator');
		mkdir(__DIR__.'/tmp/opcache-preload-generator');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/opcache-preload-generator/src');
		file_put_contents(__DIR__.'/tmp/opcache-preload-generator/src/main.php', '<?php
			echo "main";
			include "./liba.php";
			echo "liba ok";
			include "./libb.php";
			echo "libb ok";
		?>');
		file_put_contents(__DIR__.'/tmp/opcache-preload-generator/src/liba.php', '<?php
			echo "liba";
			include "./libc.php";
			echo "libc ok";
		?>');
		file_put_contents(__DIR__.'/tmp/opcache-preload-generator/src/libb.php', '<?php
			echo "libb";
		?>');
		file_put_contents(__DIR__.'/tmp/opcache-preload-generator/src/libc.php', '<?php
			echo "libc";
		?>');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		chdir(__DIR__.'/tmp/opcache-preload-generator/src');
		system(
			PHP_BINARY.' '.__DIR__.'/../'.basename(__FILE__).' '
			.'. '
			.'. '
			.'../preload.php'
			//.' --debug'
		);
	echo PHP_EOL;

	echo ' -> Testing preload script';
		$md5sum=md5(str_replace(PHP_EOL, '', file_get_contents('../preload.php')));
		// echo $md5sum;
		if(
			($md5sum === '23c05d24904c5248b443bc2cb270ef29') || // windows
			($md5sum === 'da722e76b8de6af80099d8b26f663568') // unix
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>