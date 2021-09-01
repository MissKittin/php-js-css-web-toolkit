<?php
	if(!isset($login_config))
		include './components/login/config/login_config.php';

	if(csrf_check_token('post'))
	{
		if(logout(check_post('logout')))
		{
			login_refresh('file', './components/login/view/reload.php');
			exit();	
		}

		if($login_config['method'](check_post('login'), check_post('password'), $login_credentials[0], $login_credentials[1]))
		{
			login_refresh('file', './components/login/view/reload.php');
			exit();
		}
	}

	if(!is_logged())
		include './components/login/view/form.php';

	unset($login_config);
?>