<?php
	/*
	 * include2blob.php tool test
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
		@rmdir_recursive(__DIR__.'/tmp/include2blob');
		mkdir(__DIR__.'/tmp/include2blob');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/include2blob/lib');
		mkdir(__DIR__.'/tmp/include2blob/src');
		copy(__DIR__.'/../../lib/check_var.php', __DIR__.'/tmp/include2blob/lib/check_var.php');
		copy(__DIR__.'/../../lib/global_variable_streamer.php', __DIR__.'/tmp/include2blob/lib/global_variable_streamer.php');
		copy(__DIR__.'/../../lib/strip_php_comments.php', __DIR__.'/tmp/include2blob/lib/strip_php_comments.php');
		copy(__DIR__.'/../include2blob.php', __DIR__.'/tmp/include2blob/include2blob.php');
		file_put_contents(__DIR__.'/tmp/include2blob/src/main.php', '<?php
			echo "main";
			include "./liba.php";
			echo "liba ok";
			include "./libb.php";
			echo "libb ok";
		?>');
		file_put_contents(__DIR__.'/tmp/include2blob/src/liba.php', '<?php
			echo "liba";
			include "./libc.php";
			echo "libc ok";
		?>');
		file_put_contents(__DIR__.'/tmp/include2blob/src/libb.php', '<?php
			echo "libb";
		?>');
		file_put_contents(__DIR__.'/tmp/include2blob/src/libc.php', '<?php
			echo "libc";
		?>');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing tool';
		chdir(__DIR__.'/tmp/include2blob/src');
		$output=shell_exec('"'.PHP_BINARY.'" ../'.basename(__FILE__).' ./main.php');
		if(
			$output
			===
			'<?php echo "main"; echo "liba"; echo "libc"; echo "libc ok"; echo "liba ok"; echo "libb"; echo "libb ok"; ?>'
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>