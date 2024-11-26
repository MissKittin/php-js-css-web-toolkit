<?php
	/*
	 * sec_login.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		echo ' -> Mocking functions';
			class Exception extends \Exception {}
			function session_status()
			{
				return PHP_SESSION_ACTIVE;
			}
			function session_regenerate_id() {}
			function session_destroy() {}
			function find_password($login)
			{
				if($login === 'login-test')
					return '$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO';

				return null;
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
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
		}

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
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

		$_SERVER['HTTP_USER_AGENT']='UA-TEST';
		$failed=false;

		echo ' -> Testing login_single'.PHP_EOL;
			echo '  -> returns true';
				if(login_single('login-test', 'test',
					'login-test',
					'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false';
				if(!login_single('login-test', 'wrongpassword',
					'login-test',
					'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing login_multi'.PHP_EOL;
			echo '  -> returns true';
				if(login_multi('login-test', 'test', [
					'login-test'=>'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO',
					'login-test2'=>'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'
				]))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false';
				if(!login_multi('login-test', 'wrongpassword', [
					'login-test'=>'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO',
					'login-test2'=>'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'
				]))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing login_callback'.PHP_EOL;
			echo '  -> returns true';
				if(login_callback('login-test', 'test', 'Test\find_password'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> returns false';
				if(!login_callback('login-test', 'wrongpassword', 'Test\find_password'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing is_logged (returns true)';
			if(is_logged(false))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing logout (returns null)';
			if(logout() === null)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing is_logged (returns false)';
			if(!login_validator::is_logged(false)) // bypass cache
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		if($failed)
			exit(1);
	}
?>