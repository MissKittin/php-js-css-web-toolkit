<?php
	// login_single login_multi login_callback
	if(!isset($GLOBALS['_login']['config']['method']))
		$GLOBALS['_login']['config']['method']=null;

	if(!isset($GLOBALS['_login']['config']['remember_cookie_lifetime']))
		$GLOBALS['_login']['config']['remember_cookie_lifetime']=31556926; // 1 year

	// default session reloader
	if(!isset($GLOBALS['_login']['config']['session_reload']))
		$GLOBALS['_login']['config']['session_reload']=function($cookie_lifetime)
		{
			session_start([
				'cookie_lifetime'=>$cookie_lifetime
			]);
		};

	// exit after sending the login prompt
	if(!isset($GLOBALS['_login']['config']['exit_after_login_prompt']))
		$GLOBALS['_login']['config']['exit_after_login_prompt']=false;

	// event callbacks
	if(
		(!isset($GLOBALS['_login']['config']['on_login_prompt'])) ||
		(!is_callable($GLOBALS['_login']['config']['on_login_prompt']))
	)
		$GLOBALS['_login']['config']['on_login_prompt']=function(){};
	if(
		(!isset($GLOBALS['_login']['config']['on_login_success'])) ||
		(!is_callable($GLOBALS['_login']['config']['on_login_success']))
	)
		$GLOBALS['_login']['config']['on_login_success']=function(){};
	if(
		(!isset($GLOBALS['_login']['config']['on_login_failed'])) ||
		(!is_callable($GLOBALS['_login']['config']['on_login_failed']))
	)
		$GLOBALS['_login']['config']['on_login_failed']=function(){};
	if(
		(!isset($GLOBALS['_login']['config']['on_logout'])) ||
		(!is_callable($GLOBALS['_login']['config']['on_logout']))
	)
		$GLOBALS['_login']['config']['on_logout']=function(){};
?>