<?php
	include './app/shared/samples/default_http_headers.php';

	if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
		ob_start('ob_gzhandler');

	include './app/shared/samples/session_start.php';

	include './lib/check_var.php';
	include './lib/sec_csrf.php';
	include './lib/sec_login.php';

	include './app/templates/samples/default/default_template.php';
	$view=new default_template();

	$view['login_failed_single']=false;
	$view['login_failed_multi']=false;
	$view['login_failed_callback']=false;
	$view['logout']=false;

	$use_login_refresh=true;
	$login_credentials_single=['test', '$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'];
	$login_credentials_multi=array(
		'test'=>'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO',
		'test2'=>'$2y$10$e6.i2KXM3orn1cFz3KVuKOCOx4WI9TXt0wCHgS3UM98MMNWsi7yau'
	);

	function callback_function($login)
	{
		// also you can access database in this function
		switch($login)
		{
			case 'test':
				return '$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO';
			case 'test2':
				return '$2y$10$e6.i2KXM3orn1cFz3KVuKOCOx4WI9TXt0wCHgS3UM98MMNWsi7yau';
		}
		return null; // login failed
	}
	function reload_page()
	{
		global $view;
		$view->view('./app/views/samples/login-library-test', 'reload_page.html');
	}

	if(csrf_check_token('post'))
	{
		if(login_single(check_post('user'), check_post('password'), $login_credentials_single[0], $login_credentials_single[1]))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page');
				exit();
			}
		else
			$view['login_failed_single']=true;

		if(login_multi(check_post('user_multi'), check_post('password_multi'), $login_credentials_multi))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page');
				exit();
			}
		else
			$view['login_failed_multi']=true;

		if(login_callback(check_post('user_callback'), check_post('password_callback'), 'callback_function'))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page');
				exit();
			}
		else
			$view['login_failed_callback']=true;

		if(logout(check_post('logout')))
		{
			$view['logout']=true;
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page');
				exit();
			}		
		}
	}

	$view->view('./app/views/samples/login-library-test');
?>