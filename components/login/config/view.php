<?php
	if(!isset($GLOBALS['_login']['view']['lang']))
		$GLOBALS['_login']['view']['lang']='en';

	// <title>
	if(!isset($GLOBALS['_login']['view']['title']))
		$GLOBALS['_login']['view']['title']='Login';

	// $GLOBALS['_login']['view']['assets_path']/$GLOBALS['_login']['view']['login_style']
	if(!isset($GLOBALS['_login']['view']['assets_path']))
		$GLOBALS['_login']['view']['assets_path']='/assets';

	// $GLOBALS['_login']['view']['assets_path']/$GLOBALS['_login']['view']['login_style']
	if(!isset($GLOBALS['_login']['view']['login_style']))
		$GLOBALS['_login']['view']['login_style']='login_bright.css';

	if(!isset($GLOBALS['_login']['view']['html_headers']))
		$GLOBALS['_login']['view']['html_headers']='';

	if(!isset($GLOBALS['_login']['view']['login_label']))
		$GLOBALS['_login']['view']['login_label']='Login';

	if(!isset($GLOBALS['_login']['view']['password_label']))
		$GLOBALS['_login']['view']['password_label']='Password';

	if(!isset($GLOBALS['_login']['view']['display_remember_me_checkbox']))
		$GLOBALS['_login']['view']['display_remember_me_checkbox']=true;

	if(!isset($GLOBALS['_login']['view']['remember_me_label']))
		$GLOBALS['_login']['view']['remember_me_label']='Remember me';

	if(!isset($GLOBALS['_login']['view']['wrong_credentials_label']))
		$GLOBALS['_login']['view']['wrong_credentials_label']='Invalid username or password';

	if(!isset($GLOBALS['_login']['view']['submit_button_label']))
		$GLOBALS['_login']['view']['submit_button_label']='Login';

	// <title> for reload.php
	if(!isset($GLOBALS['_login']['view']['loading_title']))
		$GLOBALS['_login']['view']['loading_title']='Loading';

	if(!isset($GLOBALS['_login']['view']['loading_label']))
		$GLOBALS['_login']['view']['loading_label']='Loading...';
?>