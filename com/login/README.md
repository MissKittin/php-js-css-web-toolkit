# Login component
Simple login screen middleware

## Required libraries
* `check_var.php`
* `registry.php`
* `sec_csrf.php`
* `sec_login.php`
* `simpleblog_materialized.css` (for materialized template)

## Reserved variables
* `$_SESSION['_com_login_remember_me']`

## Note
Throws an `login_com_exception` on error

## Configuration registers
Component are configured using registers.  
To set the desired value use `login_com_reg_name::_()['key']='value'` (except `login_com_reg_csp`)
* `login_com_reg`
	* `credentials` [array]  
		data for the `login_single` and `login_multi` methods  
		see [Example usage](#example-usage)
	* `callback` [closure]  
		a function that returns a hash for the `login_callback` method  
		see [Example usage](#example-usage)
* `login_com_reg_config`
	* `method` [string]  
		available: `login_single` `login_multi` `login_callback`  
		see `sec_login.php` library for more info
	* `remember_cookie_lifetime` [int]  
		if "remember me" option is checked  
		in seconds  
		default: `31556926`
	* `session_reload` [closure]  
		custom session reloader
	* `on_login_prompt` [closure]  
		do before sending the login prompt
	* `on_login_success` [closure]  
		do on successful login
	* `on_login_failed` [closure]  
		do on failed login
	* `on_logout` [closure]  
		do before logout
	* `reload_by_http` [bool]  
		reload via HTTP 301 redirect  
		default: `true`
* `login_com_reg_view`
	* `template` [string]  
		`default` or `materialized`  
		default: `default`
	* `templates_dir` [string]  
		path to the template directory
	* `lang` [string]  
		`<html lang="lang">`  
		default: `en`
	* `title` [string]  
		`<title>` for `views/form.php`  
		default: `Login`
	* `assets_path` [string]  
		default: `/assets`
	* `login_style` [string]  
		`login_default_bright.css` for default template or  
		`login_default_dark.css` for default template or  
		`login_materialized.css` for materialized template  
		default: `login_default_bright.css`
	* `inline_style` [bool]  
		compiles the style and adds it to the inline tag instead of `link rel="stylesheet"` (not recommended)  
		default: `false`
	* `html_headers` [string]  
		custom html headers, will be added to the `<head>` section
	* `favicon` [string]  
		path to the favicon headers file  
		the content will be appended to the `<head>` section  
		**note:** pass the path through the realpath function
	* `login_label` [string]  
		login input box placeholder  
		default: `Login`
	* `password_label` [string]  
		password input box placeholder  
		default: `Password`
	* `login_default_value` [string]  
		set default text for login box  
		**note:** this string will be escaped
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
		default: `Remember me`
	* `remember_me_box_disabled` [bool]  
		set "remember me" switch status to disabled state  
		default: `false`
	* `wrong_credentials_label` [string]  
		message about bad credentials  
		to force the login page to be displayed, use `login_com_reg::_()['wrong_credentials']=true`  
		default: `Invalid username or password`
	* `submit_button_label` [string]  
		default: `Login`
	* `submit_button_disabled` [bool]  
		set submit button status to disabled state  
		default: `false`
	* `loading_title` [string]  
		`<title>` for `views/reload.php`  
		default: `Loading`
	* `loading_label` [string]  
		`views/reload.php` content  
		default: `Loading...`
* `login_com_reg_csp`  
	section for the CSP generator  
	to add element to the policy, do eg `login_com_reg_csp::add('script-src', '\'myhash\'');`

## Event callbacks
You can define functions that will be run at the right moment, eg
```
login_com_reg_config::_()['on_login_prompt']=function()
{
	error_log('Login prompt requested');
};
login_com_reg_config::_()['on_login_success']=function()
{
	error_log('User logged in');
};
login_com_reg_config::_()['on_login_failed']=function()
{
	error_log('Login failed');
};
login_com_reg_config::_()['on_logout']=function()
{
	error_log('User logged out');
};
```

## Example usage
```
// include component
require './com/login/main.php';

// set credentials for single method
login_com_reg::_()['credentials']=['login', 'bcrypted-password'];

// or set credentials for multi method
login_com_reg::_()['credentials']=[
	['login1', 'bcrypted-password1'],
	['login2', 'bcrypted-password2']
];

// or set callback for callback method
login_com_reg::_()['callback']=function($login)
{
	if($login === 'login')
		return 'bcrypted-password';

	return null;
};

// set method
login_com_reg_config::_()['method']='login_single';

// display login prompt (returns bool, if true, it's time to evacuate)
if(login_com())
	exit();

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
Reload via HTTP 301 redirect (default):
```
login_com_reload();
exit();
```
Refresh by displaying the loading page:
```
login_com_reg_config::_()['reload_by_http']=false;
login_com_reload();
exit();
```

## Custom session reloader for "Remember Me"
If you want to use `session_start()` with parameters other than the default,  
you can define the function `login_com_reg_config::_()['session_reload']`  
eg. for the `sec_lv_encrypter.php` library the function will look like this:
```
login_com_reg_config::_()['session_reload']=function($cookie_lifetime)
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
