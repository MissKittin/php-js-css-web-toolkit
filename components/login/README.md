# Login component
Simple middleware

## Required libraries
* `check_var.php`
* `sec_csrf.php`
* `sec_login.php`

## Reserved variables
* `$GLOBALS['login']`
* `$_SESSION['__login_remember_me']`

## Config sections
Roadmap of `$GLOBALS['login']` array
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
	* `lang` [string]  
		`<html lang="lang">`
	* `title` [string]  
		`<title>` for `views/form.php`
	* `assets_path` [string]  
		default: `/assets`
	* `login_style` [string]  
		default: `login_dark.css`
	* `html_headers` [string]  
		custom html headers, will be added to the `<head>` section
	* `login_label` [string]  
		login input box placeholder
	* `password_label` [string]  
		password input box placeholder
	* `display_remember_me_checkbox` [bool]  
		enable (default) or disable "remember me" switch
	* `remember_me_label` [string]  
		switch label
	* `wrong_credentials_label` [string]  
		message about bad credentials
	* `submit_button_label` [string]
	* `loading_title` [string]  
		`<title>` for `views/reload.php`
	* `loading_label` [string]  
		`views/reload.php` content
* `csp_header`  
	section for the CSP generator  
	to add element to the policy, do eg `$GLOBALS['login']['csp_header']['script-src'][]='\'myhash\'';`

## Event callbacks
You can define functions that will be run at the right moment, eg
```
$GLOBALS['login']['config']['on_login_prompt']=function()
{
	error_log('Login prompt requested');
};
$GLOBALS['login']['config']['on_login_success']=function()
{
	error_log('User logged in');
};
$GLOBALS['login']['config']['on_login_failed']=function()
{
	error_log('Login failed');
};
$GLOBALS['login']['config']['on_logout']=function()
{
	error_log('User logged out');
};
```

## Example usage
```
// set credentials for single method
$GLOBALS['login']['credentials']=['login', 'bcrypted-password'];

// set credentials for multi method
$GLOBALS['login']['credentials']=[
	['login1', 'bcrypted-password1'],
	['login2', 'bcrypted-password2']
];

// set callback for callback method
$GLOBALS['login']['callback']=function($login)
{
	if($login === 'login')
		return 'bcrypted-password';
	return null;
};

// set method
$GLOBALS['login']['config']['method']='login_single';

// display login prompt
include './components/login/login.php';

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
include './components/login/reload.php';
exit();
```

## Custom session reloader for "Remember Me"
If you want to use session_start() with parameters other than the default,  
you can define the function `$GLOBALS['login']['config']['session_reload']`.  
eg. for the `sec_lv_encrypter.php` library the function will look like this:
```
$GLOBALS['login']['config']['session_reload']=function($cookie_lifetime)
{
	lv_cookie_session_handler::session_start([
		'cookie_lifetime'=>$cookie_lifetime
	]);
};
```

## Assets
Link `./assets/login_bright.css` and `./assets/login_dark.css` to the `app/assets`. This step is optional.  
for *nix:
```
ln -s ../../components/login/assets/login_bright.css ./app/assets/login_bright.css; ln -s ../../components/login/assets/login_dark.css ./app/assets/login_dark.css
```
for windows:
```
mklink /d app\assets\login_bright.css ..\..\components\login\assets\login_bright.css
mklink /d app\assets\login_dark.css ..\..\components\login\assets\login_dark.css
```

## Portability
Create a directory `./components/login/lib`  
and copy the required libraries to this directory.
