<?php
	if(!isset($GLOBALS['login_config']))
		include __DIR__.'/../config/login_config.php';

	if(csrf_check_token('post'))
	{
		if(logout(check_post('logout')))
		{
			login_refresh('file', __DIR__.'/../view/reload.php');
			exit();	
		}

		switch($GLOBALS['login_config']['method'])
		{
			case 'login_single':
				$__login_result=login_single(check_post('login'), check_post('password'), $GLOBALS['login_credentials'][0], $GLOBALS['login_credentials'][1]);
			break;
			case 'login_multi':
				$__login_result=login_multi(check_post('login'), check_post('password'), $GLOBALS['login_credentials']);
			break;
			case 'login_callback':
				$__login_callback_cache=check_post('login');
				$__login_result=login_callback($__login_callback_cache, check_post('password'), $GLOBALS['login_credentials']($__login_callback_cache));
				unset($__login_callback_cache);
			break;
			default:
				$__login_result=false;
		}

		if($__login_result)
		{
			if(check_post('remember_me') !== null)
				$_SESSION['__login_remember_me']=true;

			login_refresh('file', __DIR__.'/../view/reload.php');
			exit();
		}

		unset($__login_result);
	}

	if(!is_logged())
		include __DIR__.'/../view/form.php';

	if(check_session('__login_remember_me') === true)
	{
		$__login_cookie_lifetime=31556926; // 1 year
		if(isset($GLOBALS['login_config']['remember_cookie_lifetime']))
			$__login_cookie_lifetime=$GLOBALS['login_config']['remember_cookie_lifetime'];

		session_write_close();
		session_start(array(
			'cookie_lifetime'=>$__login_cookie_lifetime
		));

		unset($__login_cookie_lifetime);
	}

	unset($login_config);
?>