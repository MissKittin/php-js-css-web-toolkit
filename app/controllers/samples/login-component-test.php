<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/login.php';
	include './components/login/init.php'; // login.php won't be imported again

	$view['lang']='en';
	$view['title']='Protected page';
	$login_config['login_style']='login-bright.css';

	$login_credentials=['test', '$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'];
	include './app/shared/samples/login_config.php';
	include './components/login/controller/login.php'; // display login prompt

	if(is_logged())
	{
		include './app/models/samples/login-component-test.php';
		include './app/views/samples/default/default.php';
	}
?>