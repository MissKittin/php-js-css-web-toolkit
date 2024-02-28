<?php
	if(!function_exists('login_refresh'))
	{
		if(file_exists(__DIR__.'./lib/sec_login.php'))
			require __DIR__.'./lib/sec_login.php';
		else if(file_exists(__DIR__.'/../../lib/sec_login.php'))
			require __DIR__.'/../../lib/sec_login.php';
		else
			throw new Exception('sec_login.php library not found');
	}

	require __DIR__.'/config/view.php';

	login_refresh('file', __DIR__.'/templates/'.$GLOBALS['_login']['view']['template'].'/views/reload.php');
	exit();
?>