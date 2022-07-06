<?php
	if(!isset($GLOBALS['login']['view']['lang']))
		$GLOBALS['login']['view']['lang']='en';

	// <title>
	if(!isset($GLOBALS['login']['view']['title']))
		$GLOBALS['login']['view']['title']='Login';

	// $GLOBALS['login']['view']['assets_path']/$GLOBALS['login']['view']['login_style']
	if(!isset($GLOBALS['login']['view']['assets_path']))
		$GLOBALS['login']['view']['assets_path']='/assets';

	// $GLOBALS['login']['view']['assets_path']/$GLOBALS['login']['view']['login_style']
	if(!isset($GLOBALS['login']['view']['login_style']))
		$GLOBALS['login']['view']['login_style']='login_bright.css';

	if(!isset($GLOBALS['login']['view']['html_headers']))
		$GLOBALS['login']['view']['html_headers']='';

	if(!isset($GLOBALS['login']['view']['login_label']))
		$GLOBALS['login']['view']['login_label']='Login';

	if(!isset($GLOBALS['login']['view']['password_label']))
		$GLOBALS['login']['view']['password_label']='Password';

	if(!isset($GLOBALS['login']['view']['display_remember_me_checkbox']))
		$GLOBALS['login']['view']['display_remember_me_checkbox']=true;

	if(!isset($GLOBALS['login']['view']['remember_me_label']))
		$GLOBALS['login']['view']['remember_me_label']='Remember me';

	if(!isset($GLOBALS['login']['view']['wrong_credentials_label']))
		$GLOBALS['login']['view']['wrong_credentials_label']='Invalid username or password';

	if(!isset($GLOBALS['login']['view']['submit_button_label']))
		$GLOBALS['login']['view']['submit_button_label']='Login';

	// <title> for reload.php
	if(!isset($GLOBALS['login']['view']['loading_title']))
		$GLOBALS['login']['view']['loading_title']='Loading';

	if(!isset($GLOBALS['login']['view']['loading_label']))
		$GLOBALS['login']['view']['loading_label']='Loading...';
?>