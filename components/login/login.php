<?php
	if(!function_exists('check_post'))
	{
		if(file_exists(__DIR__.'/lib/check_var.php'))
			include __DIR__.'/lib/check_var.php';
		else if(file_exists(__DIR__.'/../../lib/check_var.php'))
			include __DIR__.'/../../lib/check_var.php';
		else
			throw new Exception('check_var.php library not found');
	}
	if(!function_exists('csrf_check_token'))
	{
		if(file_exists(__DIR__.'/lib/sec_csrf.php'))
			include __DIR__.'/lib/sec_csrf.php';
		else if(file_exists(__DIR__.'/../../lib/sec_csrf.php'))
			include __DIR__.'/../../lib/sec_csrf.php';
		else
			throw new Exception('sec_csrf.php library not found');
	}
	if(!function_exists('is_logged'))
	{
		if(file_exists(__DIR__.'/lib/sec_login.php'))
			include __DIR__.'/lib/sec_login.php';
		else if(file_exists(__DIR__.'/../../lib/sec_login.php'))
			include __DIR__.'/../../lib/sec_login.php';
		else
			throw new Exception('sec_login.php library not found');
	}

	include __DIR__.'/config/config.php';
	include __DIR__.'/config/csp_header.php';
	include __DIR__.'/config/view.php';

	if(csrf_check_token('post'))
	{
		if(logout(check_post('logout')))
		{
			$GLOBALS['login']['config']['on_logout']();
			login_refresh('file', __DIR__.'/views/reload.php');
			exit();
		}

		if(
			(check_post('login_prompt') !== null) &&
			(check_post('login') !== null) &&
			(check_post('password') !== null)
		){
			if(!isset($GLOBALS['login']['config']['method']))
				throw new Exception('Login method not specified');

			switch($GLOBALS['login']['config']['method'])
			{
				case 'login_single':
					$GLOBALS['login']['result']=login_single(
						check_post('login'),
						check_post('password'),
						$GLOBALS['login']['credentials'][0],
						$GLOBALS['login']['credentials'][1]
					);
				break;
				case 'login_multi':
					$GLOBALS['login']['result']=login_multi(
						check_post('login'),
						check_post('password'),
						$GLOBALS['login']['credentials']
					);
				break;
				case 'login_callback':
					$GLOBALS['login']['result']=login_callback(
						check_post('login'),
						check_post('password'),
						$GLOBALS['login']['callback'](check_post('login'))
					);
				break;
				default:
					throw new Exception('Unknown login method');
			}

			if($GLOBALS['login']['result'])
			{
				if(check_post('remember_me') !== null)
					$_SESSION['__login_remember_me']=true;

				$GLOBALS['login']['config']['on_login_success']();
				login_refresh('file', __DIR__.'/views/reload.php');
				exit();
			}
			else
			{
				$GLOBALS['login']['wrong_credentials']=true;
				$GLOBALS['login']['config']['on_login_failed']();
			}

			unset($GLOBALS['login']['result']);
		}
	}

	if(!is_logged())
	{
		$GLOBALS['login']['config']['on_login_prompt']();
		include __DIR__.'/views/form.php';

		if($GLOBALS['login']['config']['exit_after_login_prompt'])
			exit();
	}

	if(check_session('__login_remember_me') === true)
	{
		session_write_close();
		$GLOBALS['login']['config']['session_reload'](
			$GLOBALS['login']['config']['remember_cookie_lifetime']
		);
	}
?>