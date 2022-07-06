<?php
	include './app/shared/samples/default_http_headers.php';

	if(
		isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
		(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
	)
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

	// import credentials and callback_function()
	include './app/models/samples/login_library_test_credentials.php';

	function reload_page($view)
	{
		$view->view('./app/views/samples/login-library-test', 'reload_page.html');
	}

	if(csrf_check_token('post'))
	{
		if(login_single(
			check_post('user'),
			check_post('password'),
			$login_credentials_single[0],
			$login_credentials_single[1]
		))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page', [$view]);
				exit();
			}
		else
			$view['login_failed_single']=true;

		if(login_multi(
			check_post('user_multi'),
			check_post('password_multi'),
			$login_credentials_multi
		))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page', [$view]);
				exit();
			}
		else
			$view['login_failed_multi']=true;

		if(login_callback(
			check_post('user_callback'),
			check_post('password_callback'),
			'callback_function'
		))
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page', [$view]);
				exit();
			}
		else
			$view['login_failed_callback']=true;

		if(logout(check_post('logout')))
		{
			$view['logout']=true;
			if($use_login_refresh)
			{
				login_refresh('callback', 'reload_page', [$view]);
				exit();
			}
		}
	}

	$view->view('./app/views/samples/login-library-test');
?>