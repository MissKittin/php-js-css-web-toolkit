# Login component

## Required libraries
* `login.php`
* `sec_csrf.php`
* `check_var.php`

## Initialization
Add `include './components/login/init.php';` to include not yet imported libraries.  
Add `include './components/login/controller/login.php';` to display login prompt.  
Use `if is_logged()` for logged-only code.

## Configuration
`$login_config['method']` defines method for `login.php` library.  
For more info see `lib/login.php`.

## Translation
Put translated labels to `$login_config` array.  
See `./config/login_config.php` for more info.

## Assets
Link `./assets/login-bright.css` and `./assets/login-dark.css` to the `app/assets`. This step is optional.  
for *nix:
```
ln -s ../../components/login/assets/login-bright.css ./app/assets/login-bright.css
ln -s ../../components/login/assets/login-dark.css ./app/assets/login-dark.css
```
for windows:
```
mklink /d app\assets\login-bright.css ..\..\components\login\assets\login-bright.css
mklink /d app\assets\login-dark.css ..\..\components\login\assets\login-dark.css
```
