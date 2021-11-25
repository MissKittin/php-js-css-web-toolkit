# Login component

## Required libraries
* `sec_csrf.php`
* `sec_login.php`
* `check_var.php`

## Initialization
Add `include './components/login/init.php';` to include not yet imported libraries.  
Add `include './components/login/controller/login.php';` to display login prompt.  
Use `if(is_logged())` for logged-only code.

## Configuration
`$login_config['method']` defines method for `sec_login.php` library.  
For individual login methods, the `$GLOBALS['login_credentials']` looks like this:
for `login_single`:
```
['test', 'bcrypted-password']
```
for `login_multi`:
```
array(
	['test1', 'bcrypted-password-1'],
	['test2', 'bcrypted-password-2'],
	['testN', 'bcrypted-password-n']
)
```
for `login_callback`:
```
function($input_login)
{
	// returns null if not found
	return get_bcrypted_password($input_login);
}
```
For more info see `lib/sec_login.php`.

## Translation
Put translated labels to `$login_config` array.  
See `./config/login_config.php` for more info.

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
Create a directory `./components/login/lib` and copy the required libraries to this directory.
