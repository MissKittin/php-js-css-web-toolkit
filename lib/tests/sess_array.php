<?php
	/*
	 * sess_array.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	ob_start();

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

	echo ' -> Registering handler';
		sess_array::register_handler();
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing slot #0 w/create_sid() 1/2';
		sess_array::session_start();
		$_SESSION['test']='yes';
		session_write_close();
		$_SESSION['test']='fail';
		sess_array::session_start();
		if($_SESSION['test'] === 'yes')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		session_write_close();

	echo ' -> Testing slot #1 1/2';
		session_id('1');
		sess_array::session_start();
		if(isset($_SESSION['test']))
		{
			echo ' [FAIL]';
			$failed=true;
		}
		else
			echo ' [ OK ]';
		$_SESSION['test']='yesx';
		session_write_close();
		$_SESSION['test']='fail';
		sess_array::session_start();
		if($_SESSION['test'] === 'yesx')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		session_write_close();

	echo ' -> Testing slot #0 2/2';
		session_id('0');
		sess_array::session_start();
		if(isset($_SESSION['test']))
		{
			echo ' [ OK ]';

			if($_SESSION['test'] === 'yes')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		session_write_close();

	echo ' -> Testing slot #1 2/2';
		session_id('1');
		sess_array::session_start();
		if(isset($_SESSION['test']))
		{
			echo ' [ OK ]';

			if($_SESSION['test'] === 'yesx')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		session_write_close();

	echo ' -> Testing slot #0 destroy';
		session_id('0');
		sess_array::session_start();
		if(isset($_SESSION['test']))
		{
			echo ' [ OK ]';

			if($_SESSION['test'] === 'yes')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}

			session_destroy();
			sess_array::session_start();

			if(isset($_SESSION['test']))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		}
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		session_write_close();

	echo ' -> Testing slot #1 destroy';
		session_id('1');
		sess_array::session_start();
		if(isset($_SESSION['test']))
		{
			echo ' [ OK ]';

			if($_SESSION['test'] === 'yesx')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}

			session_destroy();
			sess_array::session_start();

			if(isset($_SESSION['test']))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		}
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		session_write_close();

	ob_end_flush();

	if($failed)
		exit(1);
?>