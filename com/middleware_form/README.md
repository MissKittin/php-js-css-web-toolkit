# Middleware form component
You can specify any form

## Required libraries
* `check_var.php`
* `sec_csrf.php`
* `simpleblog_materialized.css` (for materialized template)

## Note
Throws an `middleware_form_exception` on error

## Methods
* `add_field(array_field)` [returns self]  
	add input element
* `add_config(string_key, value)` [returns self]  
	change config option
* `add_csp_header(string_section, string_value)` [returns self]  
	add CSP element
* `add_html_header(string_header)` [returns self]  
	add raw html to the `<head>` section
* `add_error_message(string_message=null)` [returns self]  
	if is null, displaying the message will be canceled
* `is_form_sent()` [returns bool]  
	check if the form has been sent
* `view()`  
	display the form

## Field definition
`tag` is the first required element in an array. Can be `null` - see [Special fields](#special-fields).  
The rest of the elements are tag parameters (`param="value"`). Value can be `null`.  
Example:
```
$middleware_form->add_field([
	'tag'=>'input_or_img_or_div_etc',
	'name'=>'name_parameter',
	'param_a'=>'value_a',
	'param_b'=>'', // will be param_b=""
	'param_c'=>null // will be without =""
])
```

## Special fields
* `'tag'=>null`  
	the content will be printed in its raw form  
	second parameter: `'content'=>'<mytag>content</mytag>'`  
	other parameters will be ignored
* `'type'=>'slider'`  
	a checkbox with the slider style will be added  
	first parameter: `'tag'=>'input'`  
	second parameter: `'type'=>'slider'`  
	third parameter: `'slider_label'=>'Slider'`  
	other parameters as for the checkbox tag
* (`'type'=>'checkbox'` or `'type'=>'radio'`) and `'label'=>'Example label'`  
	style for checkbox and radiobutton  
	first parameter: `'tag'=>'input'`  
	second parameter: `'type'=>'checkbox'` or `'type'=>'radio'`  
	third parameter: `'label'=>'Check me'`  
	other parameters as for the checkbox or radio tag

## Config options
Set config options with the `add_config` method
* `lang` [string]  
	`<html lang="lang">`
* `title` [string]  
	`<title>`
* `assets_path` [string]  
	default: `/assets`
* `middleware_form_style` [string]  
	default: `middleware_form_dark.css`
* `inline_style` [bool]  
	compiles the style and adds it to the inline tag  
	instead of `link rel="stylesheet"` (not recommended)  
	default: `false`
* `submit_button_label` [string]  
	default: `Next`

## Templates
To select a template, add an argument to the constructor:  
```
$middleware_form=new middleware_form(); // select default template
$middleware_form=new middleware_form('default'); // same as above
$middleware_form=new middleware_form('materialized'); // self explanatory
```

## Example usage - captcha
feat. login component & `sec_captcha.php` library
```
require './com/login/main.php';

if(!isset($_SESSION['captcha_verified']))
{
	require './lib/sec_captcha.php';

	if((!isset($_POST['captcha'])) || (!captcha_check($_POST['captcha'])))
	{
		require './com/middleware_form/main.php';
		$captcha_form=new middleware_form();

		// here you can setup the login component (view section)

		$captcha_form
		->	add_csp_header('img-src', 'data:') // base64 captcha image
		->	add_csp_header('style-src', '\'unsafe-hashes\'') // for the hash below
		->	add_csp_header('style-src', '\'sha256-N6tSydZ64AHCaOWfwKbUhxXx2fRFDxHOaL3e3CO7GPI=\'') // captcha image style
		->	add_config('middleware_form_style', 'middleware_form_bright.css')
		->	add_config('title', 'Verification')
		->	add_config('submit_button_label', 'Verify')
		->	add_field([
				'tag'=>'img',
				'src'=>'data:image/jpeg;base64,'.base64_encode(captcha_get('captcha_gd2')),
				'style'=>'width: 100%;'
			])
		->	add_field([
				'tag'=>'input',
				'type'=>'text',
				'name'=>'captcha',
				'placeholder'=>'Rewrite the text from the picture'
			]);

		if($captcha_form->is_form_sent())
			login_com_reload(); // display reload page, do not exit()
		else
			$captcha_form->view();

		exit();
	}

	$_SESSION['captcha_verified']=true;

	// display reload page and exit()
		login_com_reload();
		exit();
}

// rest of the code
```

## Example code - change password
```
if(change_password_requested())
{
	require './com/middleware_form/main.php';
	$change_password_form=new middleware_form();

	if(($change_password_form->is_form_sent()) && is_old_password_valid($_POST['old_password']))
		save_new_password($_POST['old_password'], $_POST['new_password']);
	else
	{
		$change_password_form
		->	add_config('title', 'Changing the password')
		->	add_config('submit_button_label', 'Change password')
		->	add_field([
				'tag'=>'input',
				'type'=>'password',
				'name'=>'old_password',
				'placeholder'=>'Old password'
			])
		->	add_field([
				'tag'=>'input',
				'type'=>'password',
				'name'=>'new_password',
				'placeholder'=>'New password'
			])
		->	view();

		exit();
	}
}

// rest of the code
```

## Assets
Link template assets to the `app/assets`.

### default template
for *nix:
```
ln -s ../../tk/com/middleware_form/templates/default/assets/middleware_form_default_bright.css ./app/assets/middleware_form_default_bright.css; ln -s ../../tk/com/middleware_form/templates/default/assets/middleware_form_default_dark.css ./app/assets/middleware_form_default_dark.css
```
for windows:
```
mklink /d app\assets\middleware_form_default_bright.css ..\..\tk\com\middleware_form\assets\middleware_form_default_bright.css
mklink /d app\assets\middleware_form_default_dark.css ..\..\tk\com\middleware_form\assets\middleware_form_default_dark.css
```

### materialized template
for *nix:
```
ln -s ../../tk/com/middleware_form/templates/materialized/assets/middleware_form_materialized.css ./app/assets/middleware_form_materialized.css; ln -s ../../tk/lib/simpleblog_materialized.css ./app/assets/simpleblog_materialized.css
```
for windows:
```
mklink /d app\assets\middleware_form_materialized.css ..\..\tk\com\middleware_form\templates\materialized\assets\middleware_form_materialized.css
mklink app\assets\simpleblog_materialized.css ..\..\tk\lib\simpleblog_materialized.css
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
