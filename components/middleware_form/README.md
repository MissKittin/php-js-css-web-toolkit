# Middleware form component
You can specify any form

## Required libraries
* `check_var.php`
* `sec_csrf.php`

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
`tag` is the first required element in an array. Can be `null` - see Special fields section.  
The rest of the elements are tag parameters (param="value"). Value can be `null`.  
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
* `submit_button_label` [string]  
	default: `Next`

## Example usage - captcha
feat. login component & `sec_captcha.php` library
```
if(!isset($_SESSION['captcha_verified']))
{
	include './lib/sec_captcha.php';

	if((!isset($_POST['captcha'])) || (!captcha_check($_POST['captcha'])))
	{
		include './components/middleware_form/middleware_form.php';
		$captcha_form=new middleware_form();

		// here you can setup the login component (view section)

		$captcha_form
			->add_csp_header('img-src', 'data:') // base64 captcha image
			->add_csp_header('style-src', '\'unsafe-hashes\'') // for the hash below
			->add_csp_header('style-src', '\'sha256-N6tSydZ64AHCaOWfwKbUhxXx2fRFDxHOaL3e3CO7GPI=\''); // captcha image style

		$captcha_form
			->add_config('middleware_form_style', 'middleware_form_bright.css')
			->add_config('title', 'Verification')
			->add_config('submit_button_label', 'Verify');

		$captcha_form
			->add_field([
				'tag'=>'img',
				'src'=>'data:image/jpeg;base64,'.base64_encode(captcha_get('captcha_gd2')),
				'style'=>'width: 100%;'
			])
			->add_field([
				'tag'=>'input',
				'type'=>'text',
				'name'=>'captcha',
				'placeholder'=>'Rewrite the text from the picture'
			]);

		if($captcha_form->is_form_sent())
			include './components/login/reload.php'; // display reload page
		else
			$captcha_form->view();

		exit();
	}

	$_SESSION['captcha_verified']=true;

	include './components/login/reload.php'; // display reload page
	exit();
}

// rest of the code
```

## Example code - change password
```
if(change_password_requested())
{
	include './components/middleware_form/middleware_form.php';
	$change_password_form=new middleware_form();

	if(($change_password_form->is_form_sent()) && is_old_password_valid($_POST['old_password']))
		save_new_password($_POST['old_password'], $_POST['new_password']);
	else
	{
		$change_password_form
			->add_config('title', 'Changing the password')
			->add_config('submit_button_label', 'Change password');

		$change_password_form
			->add_field([
				'tag'=>'input',
				'type'=>'password',
				'name'=>'old_password',
				'placeholder'=>'Old password'
			])
			->add_field([
				'tag'=>'input',
				'type'=>'password',
				'name'=>'new_password',
				'placeholder'=>'New password'
			]);

			$change_password_form->view();
			exit();
	}
}

// rest of the code
```

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

## Portability
Create a directory `./components/login/lib`  
and copy the required libraries to this directory.
