<?php
	if(!function_exists('login_refresh'))
	{
		if(file_exists(__DIR__.'./lib/sec_login.php'))
			include __DIR__.'./lib/sec_login.php';
		else if(file_exists(__DIR__.'/../../lib/sec_login.php'))
			include __DIR__.'/../../lib/sec_login.php';
		else
			throw new Exception('sec_login.php library not found');
	}

	include __DIR__.'/config/view.php';

	login_refresh('file', __DIR__.'/views/reload.php');
	exit();
?>