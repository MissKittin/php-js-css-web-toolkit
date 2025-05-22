# Admin panel
A small framework

## Required libraries
* `registry.php`
* `simpleblog_materialized.css` (for `materialized` and `materialized_dark` templates)
* `rand_str.php` (if `set_inline_assets(true)` was used)
* `assets_compiler.php` (for tests)
* `rmdir_recursive.php` (for tests)

## Note
Throws an `admin_panel_exception` on error  
The logout button action is the current URL via POST

## Methods
* `__construct(array_params)`  
	params:  
	* `'base_url'=>'/admin'` - required; don't add a slash at the end; if without "subdirectory" leave it empty
	* `'template'=>'template_name'` - `default`, `materialized` or `materialized_dark`, optional
	* `'templates_dir'=>'path/to/directory'` - path to the template directory, optional
	* `'assets_path'=>'/assets'` - optional
	* `'show_logout_button'=>true` - optional
	* `'csrf_token'=>['csrf_name', 'csrf_value']` - required by `show_logout_button`

* **[protected]** `_list_modules()` [yields `array(link_name=>url)`]  
	for view, prepares a list of modules for the menu
* **[protected]** `_set_default_labels()`  
	sets default values when called by the constructor
* `add_module(array_params)` [returns self]  
	you can add other parameters - they will be redirected to the module  
	reserved params:  
	* `_args` - exploded and filtered from URI (eg. `/admin/module/arg1/arg2/arg3`)  
		**note:** will always be an `array('')` when the requested module is not registered
	* `_is_default` - `true` when the module was called as default
	* `_not_found` - `true` when the requested module is not registered

	params:  
	* `'id'=>'dashboard'` - must be unique
	* `'path'=>'./app/admin/dashboard'` - to the files, will be converted to the full path
	* `'config'=>'config.php'` - view configuration script (here `./app/admin/dashboard/config.php`), optional
	* `'script'=>'main.php'` - here: `./app/admin/dashboard/main.php`
	* `'url'=>'dashboard'` - must be unique, here: `/admin/dashboard`
	* `'name'=>'Dashboard'` - in menu, will not be displayed if not defined
	* `'template_header'=>'Dashboard'` - in page header, will not be displayed if not defined
* `add_module_class(array_params)` [returns self]  
	you can add other parameters - they will be redirected to the module  
	reserved params:  
	* `_args` - exploded and filtered from URI (eg. `/admin/module/arg1/arg2/arg3`)  
		**note:** will always be an `array('')` when the requested module is not registered
	* `_is_default` - `true` when the module was called as default
	* `_not_found` - `true` when the requested module is not registered

	params:  
	* `'id'=>'dashboard'` - must be unique
	* `'class'=>'controller_class'` - class name (you can use `static::class` in static method)
	* `'config_method'=>'configure_framework'` - view configuration method (here `controller_class::configure_framework`), optional
	* `'main_method'=>'admin_panel_content'` - here: `controller_class::admin_panel_content`
	* `'url'=>'dashboard'` - must be unique, here: `/admin/dashboard`
	* `'name'=>'Dashboard'` - in menu, will not be displayed if not defined
	* `'template_header'=>'Dashboard'` - in page header, will not be displayed if not defined
* `remove_module(string_module_id)` [returns self]  
	unregister a module or menu entry
* `set_default_module(string_module_id)` [returns self]  
	must be invoked
* `add_menu_entry(array_params)` [returns self]  
	add a custom link to the menu  
	params:
	* `'id'=>'github'` - required
	* `'url'=>'https://github.com/MissKittin/php-js-css-web-toolkit'` - required
	* `'name'=>'GitHub'` - in menu, required
* `is_module_registered(string_module_id)` [returns bool]
* `is_url_registered(string_module_url)` [returns bool]
* `is_default_module_registered()` [returns bool]
* `run(bool_return_content=false)` [returns string|`null`]  
	returns rendered page if `return_content=true`
* `set_lang(string_lang)` [returns self]  
	for the module configuration script, `<html lang="lang">`
* `set_title(string_title)` [returns self]  
	for the module configuration script, `<title>`  
	default: `Administration`
* `add_csp_header(string_section, string_value)` [returns self]  
	for the module configuration script, eg `$this->add_csp_header('script-src', '\'myhash\'')`
* `add_style_header(string_path)` [returns self]  
	`<link rel="stylesheet" href="string_path">`
* `add_script_header(string_path)` [returns self]  
	`<script src="string_path"></script>`
* `add_html_header(string_header)` [returns self]  
	for the module configuration script
* `add_favicon(string_path)` [returns self]  
	path to the favicon headers file  
	the content will be appended to the `<head>` section
* `set_menu_button_label(string_label)` [returns self]  
	default: `Menu`
* `set_panel_label(string_label)` [returns self]  
	`<h1>`  
	default: `Administration`
* `set_logout_button_name(string_name)` [returns self]  
	set the `name=` parameter of the submit button  
	default: `logout`
* `set_logout_button_label(string_label)` [returns self]  
	default: `Logout`
* `set_inline_assets(bool_option)` [returns self]  
	compiles styles and scripts and adds them to the inline tag instead of `link rel="stylesheet"` and `script src=""` (not recommended)  
	default: `false`
* `rename_assets(array_assets)` [returns self]  
	change the built-in asset file names

		->	rename_assets([
				// default template
				'admin_panel_default.css'=>'rename_to_default.css',
				'admin_panel_default.js'=>'rename_to_default.js',
				// materialized and materialized_dark templates
				'admin_panel_materialized.css'=>'rename_to_materialized.css',
				'simpleblog_materialized.css'=>'rename_to_library_materialized.css'
			])

* `disable_assets(array_assets)` [returns self]  
	disable built-in template assets

		->	disable_assets([
				// default template
				'admin_panel_default.css',
				'admin_panel_default.js',
				// materialized and materialized_dark templates
				'admin_panel_materialized.css',
				'simpleblog_materialized.css'
			])

* `add_view_plugin_csp(callable_function)` [returns self]  
	add CSP rules for view plugin (they will be applied for each page)

		->	add_view_plugin_csp(function($admin_panel){
				$admin_panel->add_csp_header('script-src', '\'nonce-myscript\'');
			})

* `add_view_plugin_head(callable_function)` [returns self]  
	add HTML headers of the view plugin (they will be applied for each page)

		->	add_view_plugin_head(function($admin_panel){
				echo '<myheadertag>';
				//$admin_panel->method(args);
			})

* `add_view_plugin_body(callable_function)` [returns self]  
	add the HTML code of the view plugin (they will be applied for each page before the `</body>` tag)

		->	add_view_plugin_body(function($admin_panel){
				echo '<div>My bottom content</div>';
				//$admin_panel->method(args);
			})

## Templates
* `default`  
	purple-yellow-blue theme
* `materialized`  
	based on the Google's Material Design in green
* `materialized_dark`  
	**Warning:** requires `materialized` template assets

## Standard modules
All application logic is defined by modules.  
The module first configures the view in a `config` file, the view is rendered, and then runs the `script` file (see `add_module()`).  
**Note:** `config` and `script` may point to the same file.

## Class modules
You can also use a class to define modules, for example:
```
<?php
	class admin_panel_class_module
	{
		public static function admin_panel_config($admin_panel)
		{
			static::$admin_panel
			->	set_lang('en')
			->	set_title('Class test A');
		}
		public static function admin_panel_start($_module)
		{
			echo 'Message from '.static::class.' A';
		}

		public static function admin_panel_config_b($admin_panel)
		{
			static::$admin_panel
			->	set_lang('en')
			->	set_title('Class test B');
		}
		public static function admin_panel_start_b($_module)
		{
			echo 'Message from '.static::class.' B';
		}
	}

	// the code below may be in another file

	require './com/admin_panel/main.php';

	$admin_panel=new admin_panel([
		'base_url'=>'/admin',
		'assets_path'=>'/assets',
		'show_logout_button'=>true,
		'csrf_token'=>['csrf_name', 'csrf_value']
	]);
	$admin_panel
	->	add_module_class([
			'id'=>'classtest',
			'class'=>'admin_panel_class_module',
			'config_method'=>'admin_panel_config',
			'main_method'=>'admin_panel_start',
			'url'=>'class-test',
			'name'=>'Class test A',
			'template_header'=>'Class test A'
		])
	->	add_module_class([
			'id'=>'classtestb',
			'class'=>'admin_panel_class_module',
			'config_method'=>'admin_panel_config_b',
			'main_method'=>'admin_panel_start_b',
			'url'=>'class-test-b',
			'name'=>'Class test B',
			'template_header'=>'Class test B'
		])
	->	set_default_module('classtest')
	->	run();
?>
```

### Example - standard modules
**Note:** you need to add the `/admin` path to the router  
Admin router:
```
<?php
	require './com/admin_panel/main.php';

	$admin_panel=new admin_panel([
		'base_url'=>'/admin',
		'assets_path'=>'/assets',
		'show_logout_button'=>true,
		'csrf_token'=>['csrf_name', 'csrf_value']
	]);

	$admin_panel['global_variable']='global_value';

	$admin_panel
	->	add_module([
			'id'=>'dashboard',
			'path'=>'./app/admin/dashboard',
			'config'=>'config.php',
			'script'=>'main.php',
			'url'=>'dashboard',
			'name'=>'Dashboard',
			'template_header'=>'Dashboard'
		])
	->	add_module([
			'id'=>'posts',
			'path'=>'./app/admin/posts',
			'config'=>'config.php',
			'script'=>'main.php',
			'url'=>'posts',
			'name'=>'Posts',
			'template_header'=>'Posts',
			'custom_variable'=>'Custom variable here'
		])
	->	add_menu_entry([
			'id'=>'github',
			'url'=>'https://github.com/MissKittin/php-js-css-web-toolkit',
			'name'=>'GitHub'
		])
	->	set_default_module('dashboard')
	->	run();
?>
```

`app/admin/dashboard/config.php`:
```
<?php
	$this
	->	set_lang('en')
	->	set_title('Dashboard');
?>
```

`app/admin/dashboard/main.php`:
```
<pre><?php echo '$_module: '; var_dump($_module); ?></pre>
<pre><?php echo '$this->registry: '; var_dump($this->registry); ?></pre>
```

app/admin/posts/config.php:
```
<?php
	$this
	->	set_lang('en')
	->	set_title('Posts');
?>
```

`app/admin/posts/main.php`:
```
<?php if(isset($_module['_is_default'])) { ?>
	<h3>The module was called as default</h3>
<?php } ?>
<?php if(isset($_module['_not_found'])) { ?>
	<h3>The requested module is not registered</h3>
<?php } ?>

<?php if(isset($this->registry['global_variable'])) { ?>
	<h3>Global variable: <?php echo $this->registry['global_variable']; ?></h3>
<?php } ?>
<h3>Custom variable: <?php echo $_module['custom_variable']; ?></h3>

<h1>Select action</h1>
<div class="button"><a href="<?php echo $_module['url']; ?>/new">New post</a></div>
<div class="button"><a href="<?php echo $_module['url']; ?>/edit">Edit</a></div>
<div class="button"><a href="<?php echo $_module['url']; ?>/delete">Delete post</a></div>

<h1>Selected action</h1>
<div>
	<?php
		switch($_module['_args'][0])
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

## Integrating PHP DebugBar
To achieve this, you need to use the view plugin system.  
The given example uses the `maximebf_debugbar.php` library to make bar installation easier:
```
<?php
	require './vendor/autoload.php';
	require './lib/maximebf_debugbar.php';

	if(maximebf_debugbar
	::	enable(($app_env === 'dev')) // enable((getenv('APP_ENV') === 'dev'))
	::	set_vendor_dir('./vendor')
	::	collectors([
			'pdo'=>(class_exists('\DebugBar\DataCollector\PDO\PDOCollector')) ? new DebugBar\DataCollector\PDO\PDOCollector() : new maximebf_debugbar_dummy()
		])
	::	set_csp_nonce('phpdebugbar')
	::	set_base_url('/__PHPDEBUGBAR__')
	::	route(strtok($_SERVER['REQUEST_URI'], '?'))) // route('/'.app_params())
		exit();

	$admin_panel=new admin_panel([
		//etc...
	]);

	$admin_panel
	//etc...
	->	add_view_plugin_csp(function($admin_panel){
			if(!maximebf_debugbar::is_enabled())
				return;

			foreach(maximebf_debugbar::get_csp_headers() as $section=>$values)
				foreach($values as $value)
					$admin_panel->add_csp_header($section, $value);
		})
	->	add_view_plugin_head(function(){
			echo maximebf_debugbar::get_html_headers();
		})
	->	add_view_plugin_body(function(){
			echo maximebf_debugbar::get_page_content();
		})
	//etc...
?>
```

## Assets
Link template assets to the `app/assets`.

### default template
for *nix:
```
ln -s ../../tk/com/admin_panel/templates/default/assets/admin_panel_default.css ./app/assets/admin_panel_default.css; ln -s ../../tk/com/admin_panel/templates/default/assets/admin_panel_default.js ./app/assets/admin_panel_default.js
```
for windows:
```
mklink /d app\assets\admin_panel_default.css ..\..\tk\com\admin_panel\templates\default\assets\admin_panel_default.css
mklink app\assets\admin_panel_default.js ..\..\tk\com\admin_panel\templates\default\assets\admin_panel_default.js
```

### materialized and materialized_dark templates
for *nix:
```
ln -s ../../tk/com/admin_panel/templates/materialized/assets/admin_panel_materialized.css ./app/assets/admin_panel_materialized.css; ln -s ../../tk/lib/simpleblog_materialized.css ./app/assets/simpleblog_materialized.css
```
for windows:
```
mklink app\assets\admin_panel_materialized.css ..\..\tk\com\admin_panel\templates\materialized\assets\admin_panel_materialized.css
mklink app\assets\simpleblog_materialized.css ..\..\tk\lib\simpleblog_materialized.css
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
