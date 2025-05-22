<?php
	if(php_sapi_name() === 'cli-server')
	{
		if(
			(file_exists($_SERVER['SCRIPT_FILENAME'])) &&
			($_SERVER['SCRIPT_FILENAME'] !== __FILE__) &&
			($_SERVER['SCRIPT_NAME'] !== '/.htaccess')
		)
			return false;

		include $_SERVER['DOCUMENT_ROOT'].'/index.php';

		exit();
	}

	if(!isset($argv[1]))
	{
		echo 'Use "serve.php serve [template]" to start built-in server'.PHP_EOL;
		echo 'Note:'.PHP_EOL;
		echo ' set TEST_INLINE_STYLE=yes to test inline styles option'.PHP_EOL;
		exit();
	}
	if($argv[1] !== 'serve')
	{
		echo 'Use "serve.php serve [template]" to start built-in server'.PHP_EOL;
		echo 'Note:'.PHP_EOL;
		echo ' set TEST_INLINE_STYLE=yes to test inline styles option'.PHP_EOL;
		exit();
	}

	foreach(['assets_compiler.php', 'rmdir_recursive.php'] as $library)
	{
		echo ' Including '.$library;
			if(file_exists(__DIR__.'/../lib/'.$library))
			{
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(file_exists(__DIR__.'/../../../lib/'.$library))
			{
				if(@(include __DIR__.'/../../../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;
	}

	$admin_panel_template='default';

	if(isset($argv[2]))
		$admin_panel_template=$argv[2];

	@mkdir(__DIR__.'/tmp');

	echo 'Creating test pool...'.PHP_EOL;
		rmdir_recursive(__DIR__.'/tmp/serve');
		mkdir(__DIR__.'/tmp/serve');

		mkdir(__DIR__.'/tmp/serve/dashboard');
		file_put_contents(__DIR__.'/tmp/serve/dashboard/main.php', "
			<pre><?php echo '\$_module: '; var_dump(\$_module); ?></pre>
			<pre><?php echo '\$this->registry: '; var_dump(\$this->registry); ?></pre>
		");
		file_put_contents(__DIR__.'/tmp/serve/dashboard/config.php', "<?php
			\$this
			->	set_lang('en')
			->	set_title('Dashboard');
		?>");

		mkdir(__DIR__.'/tmp/serve/posts');
		file_put_contents(__DIR__.'/tmp/serve/posts/main.php', "
			<?php if(isset(\$_module['_is_default'])) {?>
				<h3>The module was called as default</h3>
			<?php } ?>
			<?php if(isset(\$_module['_not_found'])) {?>
				<h3>The requested module is not registered</h3>
			<?php } ?>

			<?php if(isset(\$this->registry['global_variable'])) {?>
				<h3>Global variable: <?php echo \$this->registry['global_variable']; ?></h3>
			<?php } ?>
			<h3>Custom variable: <?php echo \$_module['custom_variable']; ?></h3>

			<h1>Select action</h1>
			<div class=\"button\"><a href=\"<?php echo \$_module['url']; ?>/new\">New post</a></div>
			<div class=\"button\"><a href=\"<?php echo \$_module['url']; ?>/edit\">Edit</a></div>
			<div class=\"button\"><a href=\"<?php echo \$_module['url']; ?>/delete\">Delete post</a></div>

			<h1>Selected action</h1>
			<div>
				<?php
					switch(\$_module['_args'][0])
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
		");
		file_put_contents(__DIR__.'/tmp/serve/posts/config.php', "<?php
			\$this
			->	set_lang('en')
			->	set_title('Posts');
		?>");

		mkdir(__DIR__.'/tmp/serve/public');

		file_put_contents(__DIR__.'/tmp/serve/public/index.php', "<?php
			include __DIR__.'/../../../../main.php';

			class admin_panel_class_module
			{
				public static function admin_panel_config(\$admin_panel)
				{
					\$admin_panel
					->	set_lang('en')
					->	set_title('Class test A');
				}
				public static function admin_panel_start(\$_module)
				{
					echo 'Message from '.static::class.'::'.__FUNCTION__.'<br>';
					echo '<pre>'; var_dump(\$_module); echo '</pre>';
				}

				public static function admin_panel_config_b(\$admin_panel)
				{
					\$admin_panel
					->	set_lang('en')
					->	set_title('Class test B');
				}
				public static function admin_panel_start_b(\$_module)
				{
					echo 'Message from '.static::class.'::'.__FUNCTION__.'<br>';
					echo '<pre>'; var_dump(\$_module); echo '</pre>';
				}
			}

			\$admin_panel=new admin_panel([
				'base_url'=>'/admin',
				'template'=>'$admin_panel_template',
				'assets_path'=>'/assets',
				'show_logout_button'=>true,
				'csrf_token'=>['csrf_name', 'csrf_value']
			]);

			if(getenv('TEST_INLINE_STYLE') === 'yes')
				\$admin_panel->set_inline_assets(true);

			\$admin_panel['global_variable']='global_value';

			/*\$admin_panel->rename_assets([
				'admin_panel_default.css'=>'rename_to_default.css',
				'admin_panel_default.js'=>'rename_to_default.js',
				'admin_panel_materialized.css'=>'rename_to_materialized.css',
				'simpleblog_materialized.css'=>'rename_to_library_materialized.css'
			]);*/

			\$admin_panel->disable_assets([
				'admin_panel_default.css',
				'admin_panel_default.js',
				'admin_panel_materialized.css',
				'simpleblog_materialized.css'
			]);

			\$admin_panel
			->	add_module([
					'id'=>'dashboard',
					'path'=>'../dashboard',
					'config'=>'config.php',
					'script'=>'main.php',
					'url'=>'dashboard',
					'name'=>'Dashboard',
					'template_header'=>'Dashboard'
				])
			->	add_module([
					'id'=>'posts',
					'path'=>'../posts',
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
			->	set_default_module('dashboard')
			->	add_view_plugin_csp(function(\$admin_panel){
					\$admin_panel->add_csp_header('script-src', '\'nonce-plugintest\'');
				})
			->	add_view_plugin_head(function(){
					echo '<!-- PLUGIN HEADER TEST -->';
				})
			->	add_view_plugin_body(function(){
					echo '
						<script nonce=\"plugintest\">
							if(document.head.innerHTML.indexOf(\\'<!-- PLUGIN HEADER TEST -->\\') === -1)
								console.log(\\'View plugin headers test failed\\');
							else
								console.log(\\'View plugin headers test passed\\');
							console.log(\\'View plugin test passed\\');
						</script>
					';
				})
			->	run();
		?>");

		mkdir(__DIR__.'/tmp/serve/public/assets');

		foreach(array_diff(scandir(__DIR__.'/../templates'), ['.', '..']) as $template)
			if(is_dir(__DIR__.'/../templates/'.$template.'/assets'))
				foreach(array_diff(scandir(__DIR__.'/../templates/'.$template.'/assets'), ['.', '..']) as $file)
					assets_compiler(__DIR__.'/../templates/'.$template.'/assets/'.$file, __DIR__.'/tmp/serve/public/assets/'.$file);

		if(file_exists(__DIR__.'/../lib/simpleblog_materialized.css'))
			copy(__DIR__.'/../lib/simpleblog_materialized.css', __DIR__.'/tmp/serve/public/assets/simpleblog_materialized.css');
		else if(file_exists(__DIR__.'/../../../lib/simpleblog_materialized.css'))
			copy(__DIR__.'/../../../lib/simpleblog_materialized.css', __DIR__.'/tmp/serve/public/assets/simpleblog_materialized.css');

	echo 'Starting PHP server...'.PHP_EOL.PHP_EOL;
		chdir(__DIR__.'/tmp/serve/public');
		system('"'.PHP_BINARY.'" -S 127.0.0.1:8080 "'.__FILE__.'"');
?>