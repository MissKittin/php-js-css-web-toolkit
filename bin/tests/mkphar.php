<?php
	/*
	 * mkphar.php tool test
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
		@rmdir_recursive(__DIR__.'/tmp/mkphar');
		mkdir(__DIR__.'/tmp/mkphar');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		chdir(__DIR__.'/tmp/mkphar');
		mkdir('./com');
		mkdir('./lib');
		mkdir('./lib/tests');
		file_put_contents('./com/README.md', 'readme');
		file_put_contents('./lib/lib.php', 'libphp');
		file_put_contents('./lib/pf_lib.php', 'pflibphp');
		file_put_contents('./lib/lib.js', 'libjs');
		file_put_contents('./lib/sleep.js', 'sleepjs');
		file_put_contents('./lib/tests/lib.php', 'libtest');
		file_put_contents('./lib/pf_inc.php', 'pfincphp');
		file_put_contents('./stub.php', '<?php echo "STUB"; __HALT_COMPILER();');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Starting tool'.PHP_EOL.PHP_EOL;
		system('"'.PHP_BINARY.'" -d phar.readonly=0 "'.__DIR__.'/../'.basename(__FILE__).'" '
		.	'--compress=gz '
		.	'--source=com '
		.	'--source=lib '
		.	'--ignore=tests/ '
		.	'--ignore=README.md '
		.	'--ignore=.js '
		.	'--include=lib/pf_inc.php '
		.	'"--ignore-regex=\/pf_(.*?).php" '
		.	'"--include-regex=sleep.js$" '
		.	'--stub=./stub.php '
		.	'"--shebang=#!/usr/bin/env php" '
		.	'--output=./output.phar'
		);
	echo PHP_EOL;

	echo ' -> Testing stub';
		if(shell_exec('"'.PHP_BINARY.'" ./output.phar') === 'STUB')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing shebang';
		$handle=fopen('./output.phar', "rb");
		if(fread($handle, 19) === '#!/usr/bin/env php'."\n")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		fclose($handle);

	echo ' -> Testing phar'.PHP_EOL;
		echo '  -> com/README.md';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/com/README.md')){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> lib/tests/lib.php';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/lib/tests/lib.php')){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> lib/lib.php';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/lib/lib.php'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(file_get_contents('phar://'
			.	'./output.phar'
			.	'/lib/lib.php') === 'libphp')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lib/pf_lib.php';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/lib/pf_lib.php')){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> lib/lib.js';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/lib/lib.js')){
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> lib/sleep.js';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/lib/sleep.js'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(file_get_contents('phar://'
			.	'./output.phar'
			.	'/lib/sleep.js') === 'sleepjs')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lib/pf_inc.php';
			if(is_file('phar://'
			.	'./output.phar'
			.	'/lib/pf_inc.php'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(file_get_contents('phar://'
			.	'./output.phar'
			.	'/lib/pf_inc.php') === 'pfincphp')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>