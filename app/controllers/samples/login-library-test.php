<?php
	require './app/lib/samples/default_http_headers.php';

	if(
		isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
		str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')
	)
		ob_start('ob_gzhandler');

	require './app/lib/samples/session_start.php';

	require './lib/check_var.php';
	require './lib/sec_csrf.php';
	require './lib/sec_login.php';

	require './app/templates/samples/default/default_template.php';
	$view=new default_template();

	$view['login_failed_single']=false;
	$view['login_failed_multi']=false;
	$view['login_failed_callback']=false;
	$view['logout']=false;

	$use_login_refresh=true;

	// import credentials and callback_function()
	require './app/models/samples/login_library_test_credentials.php';

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