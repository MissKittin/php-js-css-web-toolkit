<?php
	/*
	 * assets-compiler.php tool test
	 *
	 * Note:
	 *  looks for a tool at ..
	 *  looks for a library at ../../lib
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

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
		@rmdir_recursive(__DIR__.'/tmp/assets-compiler');
		mkdir(__DIR__.'/tmp/assets-compiler');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/assets-compiler/assets');
		mkdir(__DIR__.'/tmp/assets-compiler/public');

		mkdir(__DIR__.'/tmp/assets-compiler/assets/preprocessed.js');
		file_put_contents(__DIR__.'/tmp/assets-compiler/assets/preprocessed.js/first.js', 'first');
		file_put_contents(
			__DIR__.'/tmp/assets-compiler/assets/preprocessed.js/main.php',
			'<?php echo "pre"; include __DIR__."/first.js"; echo "post"; ?>'
		);

		mkdir(__DIR__.'/tmp/assets-compiler/assets/concatenated.js');
		file_put_contents(__DIR__.'/tmp/assets-compiler/assets/concatenated.js/first.js', 'first');
		file_put_contents(__DIR__.'/tmp/assets-compiler/assets/concatenated.js/second.js', 'second');

		file_put_contents(__DIR__.'/tmp/assets-compiler/assets/single.js', 'first');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" "'.__DIR__.'/../assets-compiler.php" '
		.	'"'.__DIR__.'/tmp/assets-compiler/assets" '
		.	'"'.__DIR__.'/tmp/assets-compiler/public"'
		);
	echo PHP_EOL;

	echo ' -> Testing output files';
		if(file_get_contents(__DIR__.'/tmp/assets-compiler/public/preprocessed.js') === 'prefirstpost')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents(__DIR__.'/tmp/assets-compiler/public/concatenated.js') === 'firstsecond')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(file_get_contents(__DIR__.'/tmp/assets-compiler/public/single.js') === 'first')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>