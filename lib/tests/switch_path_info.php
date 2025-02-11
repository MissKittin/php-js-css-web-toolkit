<?php
	/*
	 * switch_path_info.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	function do_test(
		$request_uri,
		$script_filename_nix,
		$document_root_nix,
		$script_filename_win,
		$document_root_win,
		$output,
		&$failed
	){
		echo '   -> *nix';
			if(switch_path_info(
				$request_uri.'?getparam=getval',
				$script_filename_nix,
				$document_root_nix
			) === $output)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '   -> windows';
			if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
			{
				// dirname() for windows paths does not work on linux
				echo ' [SKIP]'.PHP_EOL;
				return;
			}
			if(switch_path_info(
				$request_uri.'?getparam=getval',
				$script_filename_win,
				$document_root_win
			) === $output)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
	}

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
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

	$failed=false;

	echo ' -> Testing library'.PHP_EOL;

	echo '  -> / => ""'.PHP_EOL;
		do_test(
			'/',
			'/var/www/html/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\index.php',
			'C:\\inetpub\\docroot',
			'',
			$failed
		);

	echo '  -> /subdir => ""'.PHP_EOL;
		do_test(
			'/subdir',
			'/var/www/html/subdir/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\subdir\\index.php',
			'C:\\inetpub\\docroot',
			'',
			$failed
		);

	echo '  -> /subdir/ => ""'.PHP_EOL;
		do_test(
			'/subdir/',
			'/var/www/html/subdir/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\subdir\\index.php',
			'C:\\inetpub\\docroot',
			'',
			$failed
		);

	echo '  -> /index.php => "index.php"'.PHP_EOL;
		do_test(
			'/index.php',
			'/var/www/html/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\index.php',
			'C:\\inetpub\\docroot',
			'index.php',
			$failed
		);

	echo '  -> /subdir/index.php => "index.php"'.PHP_EOL;
		do_test(
			'/subdir/index.php',
			'/var/www/html/subdir/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\subdir\\index.php',
			'C:\\inetpub\\docroot',
			'index.php',
			$failed
		);

	echo '  -> /arg1/arg2/arg3/ => "arg1/arg2/arg3"'.PHP_EOL;
		do_test(
			'/arg1/arg2/arg3/',
			'/var/www/html/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\index.php',
			'C:\\inetpub\\docroot',
			'arg1/arg2/arg3',
			$failed
		);

	echo '  -> /arg1/arg2/arg3 => "arg1/arg2/arg3"'.PHP_EOL;
		do_test(
			'/arg1/arg2/arg3',
			'/var/www/html/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\index.php',
			'C:\\inetpub\\docroot',
			'arg1/arg2/arg3',
			$failed
		);

	echo '  -> /subdir/arg1/arg2/arg3/ => "arg1/arg2/arg3"'.PHP_EOL;
		do_test(
			'/subdir/arg1/arg2/arg3/',
			'/var/www/html/subdir/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\subdir\\index.php',
			'C:\\inetpub\\docroot',
			'arg1/arg2/arg3',
			$failed
		);

	echo '  -> /subdir/index.php/arg1/arg2/arg3/ => "index.php/arg1/arg2/arg3"'.PHP_EOL;
		do_test(
			'/subdir/index.php/arg1/arg2/arg3/',
			'/var/www/html/subdir/index.php',
			'/var/www/html',
			'C:\\inetpub\\docroot\\subdir\\index.php',
			'C:\\inetpub\\docroot',
			'index.php/arg1/arg2/arg3',
			$failed
		);

	echo '  -> wrong $_SERVER["SCRIPT_FILENAME"]'.PHP_EOL;
		do_test(
			'/subdir/arg1/arg2/arg3/',
			'/var/router.php',
			'/var/www/html',
			'C:\\router.php',
			'C:\\inetpub\\docroot',
			'subdir/arg1/arg2/arg3',
			$failed
		);

	if($failed)
		exit(1);
?>