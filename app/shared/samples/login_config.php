<?php
	$GLOBALS['login_config']['method']='login_single'; // login_single login_multi login_callback
	$GLOBALS['login_config']['title']='Logowanie'; // <title>
	$GLOBALS['login_config']['login_label']='Nazwa użytkownika';
	$GLOBALS['login_config']['password_label']='Hasło';
	$GLOBALS['login_config']['display_remember_me_checkbox']=true;
	$GLOBALS['login_config']['remember_me_label']='Zapamiętaj mnie';
	//$GLOBALS['login_config']['remember_cookie_lifetime']=31556926; // 1 year (default), optional
	$GLOBALS['login_config']['button_label']='Zaloguj';
	$GLOBALS['login_config']['loading_label']='Ładowanie...';
	$GLOBALS['login_config']['assets_path']=''; // $login_config['assets_path']/assets/*.css
?>