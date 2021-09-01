<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/login.php';
	include './lib/sec_csrf.php';
	include './lib/check_var.php';

	$view['lang']='en';
	$view['title']='login';
	$view['template']='samples/default/default.php';
	$view['login_failed']['single']=false;
	$view['login_failed']['multi']=false;
	$view['login_failed']['callback']=false;
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
			break;
			case 'test2':
				return '$2y$10$e6.i2KXM3orn1cFz3KVuKOCOx4WI9TXt0wCHgS3UM98MMNWsi7yau';
			break;
		}
		return null; // login failed
	}
	function reload_page()
	{
		global $view;
		$view['content']=function(){ echo '<h1>Loading...</h1><meta http-equiv="refresh" content="0">'; };
		include './app/views/'.$view['template'];
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
			$view['login_failed']['single']=true;

		if(login_multi(check_post('user_multi'), check_post('password_multi'), $login_credentials_multi))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page');
				exit();
			}
		else
			$view['login_failed']['multi']=true;

		if(login_callback(check_post('user_callback'), check_post('password_callback'), 'callback_function'))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page');
				exit();
			}
		else
			$view['login_failed']['callback']=true;

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

	include './app/models/samples/login-library-test.php';
	include './app/views/'.$view['template'];
?>