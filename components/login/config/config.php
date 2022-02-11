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

	// exit after sending the login prompt
	if(!isset($GLOBALS['login']['config']['exit_after_login_prompt']))
		$GLOBALS['login']['config']['exit_after_login_prompt']=false;

	// event callbacks
	if(
		(!isset($GLOBALS['login']['config']['on_login_prompt'])) ||
		(!is_callable($GLOBALS['login']['config']['on_login_prompt']))
	)
		$GLOBALS['login']['config']['on_login_prompt']=function(){};
	if(
		(!isset($GLOBALS['login']['config']['on_login_success'])) ||
		(!is_callable($GLOBALS['login']['config']['on_login_success']))
	)
		$GLOBALS['login']['config']['on_login_success']=function(){};
	if(
		(!isset($GLOBALS['login']['config']['on_login_failed'])) ||
		(!is_callable($GLOBALS['login']['config']['on_login_failed']))
	)
		$GLOBALS['login']['config']['on_login_failed']=function(){};
	if(
		(!isset($GLOBALS['login']['config']['on_logout'])) ||
		(!is_callable($GLOBALS['login']['config']['on_logout']))
	)
		$GLOBALS['login']['config']['on_logout']=function(){};
?>