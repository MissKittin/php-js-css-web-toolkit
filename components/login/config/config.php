<?php
	// login_single login_multi login_callback
	if(!isset($GLOBALS['login']['config']['method']))
		$GLOBALS['login']['config']['method']=null;

	if(!isset($GLOBALS['login']['config']['remember_cookie_lifetime']))
		$GLOBALS['login']['config']['remember_cookie_lifetime']=31556926; // 1 year

	// default session reloader
	if(!isset($GLOBALS['login']['config']['session_reload']))
		$GLOBALS['login']['config']['session_reload']=function($cookie_lifetime)
		{
			session_start([
				'cookie_lifetime'=>$cookie_lifetime
			]);
		};
?>