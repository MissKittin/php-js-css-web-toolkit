<?php
	if(!function_exists('check_post'))
	{
		if(file_exists(__DIR__.'/lib/check_var.php'))
			require __DIR__.'/lib/check_var.php';
		else if(file_exists(__DIR__.'/../../lib/check_var.php'))
			require __DIR__.'/../../lib/check_var.php';
		else
			throw new Exception('check_var.php library not found');
	}
	if(!function_exists('csrf_check_token'))
	{
		if(file_exists(__DIR__.'/lib/sec_csrf.php'))
			require __DIR__.'/lib/sec_csrf.php';
		else if(file_exists(__DIR__.'/../../lib/sec_csrf.php'))
			require __DIR__.'/../../lib/sec_csrf.php';
		else
			throw new Exception('sec_csrf.php library not found');
	}
	if(!function_exists('is_logged'))
	{
		if(file_exists(__DIR__.'/lib/sec_login.php'))
			require __DIR__.'/lib/sec_login.php';
		else if(file_exists(__DIR__.'/../../lib/sec_login.php'))
			require __DIR__.'/../../lib/sec_login.php';
		else
			throw new Exception('sec_login.php library not found');
	}

	require __DIR__.'/config/config.php';
	require __DIR__.'/config/csp_header.php';
	require __DIR__.'/config/view.php';

	if(csrf_check_token('post'))
	{
		if(logout(check_post('logout')))
		{
			$GLOBALS['_login']['config']['on_logout']();
			login_refresh('file', __DIR__.'/views/reload.php');

			exit();
		}

		if(
			(check_post('login_prompt') !== null) &&
			(check_post('login') !== null) &&
			(check_post('password') !== null)
		){
			if(!isset($GLOBALS['_login']['config']['method']))
				throw new Exception('Login method not specified');

			switch($GLOBALS['_login']['config']['method'])
			{
				case 'login_single':
					$GLOBALS['_login']['result']=login_single(
						check_post('login'),
						check_post('password'),
						$GLOBALS['_login']['credentials'][0],
						$GLOBALS['_login']['credentials'][1]
					);
				break;
				case 'login_multi':
					$GLOBALS['_login']['result']=login_multi(
						check_post('login'),
						check_post('password'),
						$GLOBALS['_login']['credentials']
					);
				break;
				case 'login_callback':
					$GLOBALS['_login']['result']=login_callback(
						check_post('login'),
						check_post('password'),
						$GLOBALS['_login']['callback'](check_post('login'))
					);
				break;
				default:
					throw new Exception('Unknown login method');
			}

			if($GLOBALS['_login']['result'])
			{
				if(check_post('remember_me') !== null)
					$_SESSION['_login_remember_me']=true;

				$GLOBALS['_login']['config']['on_login_success']();
				login_refresh('file', __DIR__.'/views/reload.php');

				exit();
			}
			else
			{
				$GLOBALS['_login']['wrong_credentials']=true;
				$GLOBALS['_login']['config']['on_login_failed']();
			}

			unset($GLOBALS['_login']['result']);
		}
	}

	if(!is_logged())
	{
		$GLOBALS['_login']['config']['on_login_prompt']();
		require __DIR__.'/views/form.php';

		if($GLOBALS['_login']['config']['exit_after_login_prompt'])
			exit();
	}

	if(check_session('_login_remember_me') === true)
	{
		session_write_close();
		$GLOBALS['_login']['config']['session_reload'](
			$GLOBALS['_login']['config']['remember_cookie_lifetime']
		);
	}
?>