# Admin panel
A small framework

## Required libraries
* `registry.php`

## Note
The logout button action is the current URL via POST

## Methods
* `__construct(array_params)`  
	params:  
	* `'base_url'=>'/admin'` required
	* `'assets_path'=>'/assets'` optional
	* `'show_logout_button'=>true` optional
	* `'csrf_token'=>['csrf_name', 'csrf_value']` required by show_logout_button

* **[protected]** `__list_modules()` [returns array(link_name=>url)]  
	for view, prepares a list of modules for the menu
* **[protected]** `__set_default_labels()`  
	sets default values when called by the constructor
* `add_module(array_params)` [returns self]  
	you can add other parameters - they will be redirected to the module  
	reserved params:  
	* `__args` exploded and filtered from URI (eg. `/admin/module/arg1/arg2/arg3`)  
		note: will always be an `array('')` when the requested module is not registered
	* `__is_default` true when the module was called as default
	* `__not_found` true when the requested module is not registered

	params:  
	* `'id'=>'dashboard'` must be unique
	* `'path'=>'./app/admin/dashboard'` to the files, will be converted to the full path
	* `'config'=>'config.php'` view configuration script (here `./app/admin/dashboard/config.php`), optional
	* `'script'=>'main.php'` here: `./app/admin/dashboard/main.php`
	* `'url'=>'dashboard'` must be unique, here: `/admin/dashboard`
	* `'name'=>'Dashboard'` in menu, will not be displayed if not defined
* `remove_module(string_module_id)` [returns self]  
	unregister a module or menu entry
* `set_default_module(string_module_id)` [returns self]  
	must be invoked
* `add_menu_entry(array_params)` [returns self]  
	add a custom link to the menu  
	params:
	* `'id'=>'github'` required
	* `'url'=>'https://github.com/MissKittin/php-js-css-web-toolkit'` required
	* `'name'=>'GitHub'` in menu, required
* `is_module_registered(string_module_id)` [returns bool]
* `is_url_registered(string_module_url)` [returns bool]
* `is_default_module_registered()` [returns bool]
* `run(bool_return_content=false)` [returns string|null]  
	returns rendered page if return_content=true
* **[protected]** `set_lang(string_lang)` [returns self]  
	for the module configuration script, `<html lang="lang">`
* **[protected]** `set_title(string_title)` [returns self]  
	for the module configuration script, `<title>`, default: Administration
* **[protected]** `add_csp_header(string_section, string_value)` [returns self]  
	for the module configuration script, see `views/csp_header.php`
* `add_style_header(string_path)` [returns self]  
	`<link rel="stylesheet" href="string_path">`
* `add_script_header(string_path)` [returns self]  
	`<script src="string_path"></script>`
* **[protected]** `add_html_header(string_header)` [returns self]  
	for the module configuration script
* `set_menu_button_label(string_label)` [returns self]  
	default: Menu
* `set_panel_label(string_label)` [returns self]  
	`<h1>`, default: Administration
* `set_logout_button_label(string_label)` [returns self]  
	default: Logout

## Modules
All application logic is defined by modules.  
The module first configures the view in a `config` file,  
the view is rendered, and then runs the `script` file (see `add_module()`).  
Note: `config` and `script` may point to the same file.

### Example
Note: you need to add the `/admin` path to the router  
Admin router:
```
<?php
	include './components/admin_panel/admin_panel.php';
	$admin_panel=new admin_panel([
		'base_url'=>'/admin',
		'assets_path'=>'/assets',
		'show_logout_button'=>true,
		'csrf_token'=>['csrf_name', 'csrf_value']
	]);

	$admin_panel['global_variable']='global_value';

	$admin_panel
		->add_module([
			'id'=>'dashboard',
			'path'=>'./app/admin/dashboard',
			'config'=>'config.php',
			'script'=>'main.php',
			'url'=>'dashboard',
			'name'=>'Dashboard'
		])
		->add_module([
			'id'=>'posts',
			'path'=>'./app/admin/posts',
			'config'=>'config.php',
			'script'=>'main.php',
			'url'=>'posts',
			'name'=>'Posts',
			'custom_variable'=>'Custom variable here'
		])
		->add_menu_entry([
			'id'=>'github',
			'url'=>'https://github.com/MissKittin/php-js-css-web-toolkit',
			'name'=>'GitHub'
		]);

	$admin_panel
		->set_default_module('dashboard')
		->run();
?>
```

./app/admin/posts/config.php:
```
<?php
	$this
		->set_lang('en')
		->set_title('Posts');
?>
```

./app/admin/posts/main.php:
```
<h1>Posts</h1>

<?php if(isset($__module['__is_default'])) {?>
	<h3>The module was called as default</h3>
<?php } ?>
<?php if(isset($__module['__not_found'])) {?>
	<h3>The requested module is not registered</h3>
<?php } ?>

<?php if(isset($this->registry['global_variable'])) {?>
	<h3>Global variable: <?php echo $this->registry['global_variable']; ?></h3>
<?php } ?>
<h3>Custom variable: <?php echo $__module['custom_variable']; ?></h3>

<h1>Select action</h1>
<div class="button"><a href="<?php echo $__module['url']; ?>/new">New post</a></div>
<div class="button"><a href="<?php echo $__module['url']; ?>/edit">Edit</a></div>
<div class="button"><a href="<?php echo $__module['url']; ?>/delete">Delete post</a></div>

<h1>Selected action</h1>
<div>
	<?php
		switch($__module['__args'][0])
		{
			case 'new':
				echo 'Action: write new post';
			break;
			case 'edit':
				echo 'Action: edit post';
			break;
			case 'delete':
				echo 'Action: delete post';
			break;
			default:
				echo 'No action';
		}
	?>
</div>
```

## Assets
Link `./assets/admin_panel.css` and `./assets/admin_panel.js` to the `app/assets`. This step is optional.  
for *nix:
```
ln -s ../../components/admin_panel/assets/admin_panel.css ./app/assets/admin_panel.css; ln -s ../../components/admin_panel/assets/admin_panel.js ./app/assets/admin_panel.js
```
for windows:
```
mklink /d app\assets\admin_panel.css ..\..\components\admin_panel\assets\admin_panel.css
mklink /d app\assets\admin_panel.js ..\..\components\admin_panel\assets\admin_panel.js
```

## Portability
Create a directory `./components/admin_panel/lib`  
and copy the required libraries to this directory.