<?php
	/*
	 * autoloader-generator.php tool test
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
		@rmdir_recursive(__DIR__.'/tmp/autoloader-generator');
		mkdir(__DIR__.'/tmp/autoloader-generator');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/autoloader-generator/lib');

		file_put_contents(
			__DIR__.'/tmp/autoloader-generator/lib/liba.php',
			'<?php
				class classa {}
				interface inta {}
				trait tra {}
				function funca() {}
			?>'
		);
		file_put_contents(
			__DIR__.'/tmp/autoloader-generator/lib/libb.php',
			'<?php
				class classb {}
				interface intb {}
				trait trb {}
				function funcb() {}
			?>'
		);
		file_put_contents(
			__DIR__.'/tmp/autoloader-generator/lib/libc.php',
			'<?php
				class classc {}
				interface intc {}
				trait trc {}
				function funcc() {}
			?>'
		);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system(
			PHP_BINARY.' '.__DIR__.'/../'.basename(__FILE__).' '
			.'--in '.__DIR__.'/tmp/autoloader-generator/lib '
			.'--out '.__DIR__.'/tmp/autoloader-generator/autoloader.php'
		);
	echo PHP_EOL;

	echo ' -> Testing output file';
		$md5sum=md5(file_get_contents(__DIR__.	'/tmp/autoloader-generator/autoloader.php'));
		if(
			($md5sum === 'c3ad449f99530a93191da8a645b3ebef') || // windows
			($md5sum === '15f645b27cdc76daac79693e868eb32d') // unix
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>