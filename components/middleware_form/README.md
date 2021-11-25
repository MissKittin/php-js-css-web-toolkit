# Middleware form component
You can specify any middleware form, not change the password only.

## Required libraries
* `sec_csrf.php`
* `check_var.php`

## Initialization
Add `include './components/middleware_form/init.php';` to include not yet imported libraries.  
Add `include './components/middleware_form/controller/middleware_form.php';` to display form.  
Use `if(!$middleware_form_is_form_sent) exit();` check if the form is to be displayed.

## Configuration
`$view['form_fields']` defines form fields: input type, name and placeholder.  
All data will be sent through the `$_POST` array.
See `./config/middleware_form_fields.php` for more info.

## Translation
Put translated labels to `$middleware_form_config` array.  
See `./config/middleware_form_config.php` for more info.

## Assets
Link `./assets/middleware_form_bright.css` and `./assets/middleware_form_dark.css` to the `app/assets`. This step is optional.  
for *nix:
```
ln -s ../../components/middleware_form/assets/middleware_form_bright.css ./app/assets/middleware_form_bright.css; ln -s ../../components/middleware_form/assets/middleware_form_dark.css ./app/assets/middleware_form_dark.css
```
for windows:
```
mklink /d app\assets\middleware_form_bright.css ..\..\components\middleware_form\assets\middleware_form_bright.css
mklink /d app\assets\middleware_form_dark.css ..\..\components\middleware_form\assets\middleware_form_dark.css
```

## Sample code
```
if(middleware_form_requested())
{
	include './components/middleware_form/init.php';

	$middleware_form_config['title']='Zmiana hasla';
	$middleware_form_config['button_label']='Zmien haslo';
	$view['form_fields']=array(
		['password', 'old_password', 'Stare haslo'],
		['password', 'new_password', 'Nowe haslo']
	);

	include './components/middleware_form/controller/middleware_form.php';
	if(!$middleware_form_is_form_sent)
		exit();

	save_new_password($_POST['old_password'], $_POST['new_password']);
}

// rest of the code
```

## Portability
Create a directory `./components/login/lib` and copy the required libraries to this directory.
