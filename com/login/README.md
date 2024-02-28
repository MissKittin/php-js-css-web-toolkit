# Login component
Simple middleware

## Required libraries
* `check_var.php`
* `sec_csrf.php`
* `sec_login.php`
* `simpleblog_materialized.css` (for materialized template)

## Reserved variables
* `$GLOBALS['_login']`
* `$_SESSION['_login_remember_me']`

## Note
Throws an `Exception` if the library is not found  
Throws an `login_component_exception` on error

## Config sections
Roadmap of `$GLOBALS['_login']` array
* `config`
	* `method` [string]  
		available: login_single login_multi login_callback  
		see `sec_login.php` library for more info
	* `remember_cookie_lifetime` [int]  
		if "remember me" option is checked  
		in seconds
	* `session_reload` [closure]  
		custom session reloader
	* `exit_after_login_prompt` [bool]  
		default: false
	* `on_login_prompt` [closure]  
		do before sending the login prompt
	* `on_login_success` [closure]  
		do on successful login
	* `on_login_failed` [closure]  
		do on failed login
	* `on_logout` [closure]  
		do before logout
* `view`
	* `template` [string]  
		default or materialized
	* `lang` [string]  
		`<html lang="lang">`
	* `title` [string]  
		`<title>` for `views/form.php`
	* `assets_path` [string]  
		default: `/assets`
	* `login_style` [string]  
		`login_default_bright.css` for default template or  
		`login_default_dark.css` for default template or  
		`login_materialized.css` for materialized template  
		default: `login_default_bright.css`
	* `inline_style` [bool]  
		compiles the style and adds it to the inline tag  
		instead of link rel="stylesheet" (not recommended)  
		default: `false`
	* `html_headers` [string]  
		custom html headers, will be added to the `<head>` section
	* `login_label` [string]  
		login input box placeholder
	* `password_label` [string]  
		password input box placeholder
	* `login_default_value` [string]  
		set default text for login box  
		note: this string will be escaped
	* `login_box_disabled` [bool]  
		set login input status to disabled state  
		default: `false`
	* `password_box_disabled` [bool]  
		set password input status to disabled state  
		default: `false`
	* `display_remember_me_checkbox` [bool]  
		enable (default) or disable "remember me" switch
	* `remember_me_label` [string]  
		switch label
	* `remember_me_box_disabled` [bool]  
		set "remember me" switch status to disabled state  
		default: `false`
	* `wrong_credentials_label` [string]  
		message about bad credentials  
		to force display use `$GLOBALS['_login']['wrong_credentials']=true`
	* `submit_button_label` [string]
	* `submit_button_disabled` [bool]  
		set submit button status to disabled state  
		default: `false`
	* `loading_title` [string]  
		`<title>` for `views/reload.php`
	* `loading_label` [string]  
		`views/reload.php` content
* `csp_header`  
	section for the CSP generator  
	to add element to the policy, do eg `$GLOBALS['_login']['csp_header']['script-src'][]='\'myhash\'';`

## Event callbacks
You can define functions that will be run at the right moment, eg
```
$GLOBALS['_login']['config']['on_login_prompt']=function()
{
	error_log('Login prompt requested');
};
$GLOBALS['_login']['config']['on_login_success']=function()
{
	error_log('User logged in');
};
$GLOBALS['_login']['config']['on_login_failed']=function()
{
	error_log('Login failed');
};
$GLOBALS['_login']['config']['on_logout']=function()
{
	error_log('User logged out');
};
```

## Example usage
```
// set credentials for single method
$GLOBALS['_login']['credentials']=['login', 'bcrypted-password'];

// set credentials for multi method
$GLOBALS['_login']['credentials']=[
	['login1', 'bcrypted-password1'],
	['login2', 'bcrypted-password2']
];

// set callback for callback method
$GLOBALS['_login']['callback']=function($login)
{
	if($login === 'login')
		return 'bcrypted-password';
	return null;
};

// set method
$GLOBALS['_login']['config']['method']='login_single';

// display login prompt
include './com/login/login.php';

// check if user is authenticated
if(is_logged())
{
	echo '
		<h1>Logged!</h1>
		<form method="post" action="">
			<input type="submit" name="logout" value="Logout">
			<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
		</form>
	';
}
```

## Display reload page only
```
include './com/login/reload.php';
exit();
```

## Custom session reloader for "Remember Me"
If you want to use session_start() with parameters other than the default,  
you can define the function `$GLOBALS['_login']['config']['session_reload']`.  
eg. for the `sec_lv_encrypter.php` library the function will look like this:
```
$GLOBALS['_login']['config']['session_reload']=function($cookie_lifetime)
{
	lv_cookie_session_handler::session_start([
		'cookie_lifetime'=>$cookie_lifetime
	]);
};
```

## Assets
Link template assets to the `app/assets`.

### default template
for *nix:
```
ln -s ../../tk/com/login/templates/default/assets/login_default_bright.css ./app/assets/login_default_bright.css; ln -s ../../tk/com/login/templates/default/assets/login_default_dark.css ./app/assets/login_default_dark.css
```
for windows:
```
mklink /d app\assets\login_default_bright.css ..\..\tk\com\login\templates\default\assets\login_default_bright.css
mklink /d app\assets\login_default_dark.css ..\..\tk\com\login\templates\default\assets\login_default_dark.css
```

### materialized template
for *nix:
```
ln -s ../../tk/com/login/templates/materialized/assets/login_materialized.css ./app/assets/login_materialized.css; ln -s ../../tk/lib/simpleblog_materialized.css ./app/assets/simpleblog_materialized.css
```
for windows:
```
mklink /d app\assets\login_materialized.css ..\..\tk\com\login\templates\materialized\assets\login_materialized.css
mklink app\assets\simpleblog_materialized.css ..\..\tk\lib\simpleblog_materialized.css
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
