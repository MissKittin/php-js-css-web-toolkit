<?php
	$test_hashes=[
		'notfound'=>'28d91b7627af1f8cbc9b8540467e37dc',
		'default'=>'35ac74a13c4dcfb215b1ba8a6e9b8854',
		'dashboard'=>'64b68c520b95496ba9b591db39dfb6e8',
		'posts-new'=>'586f0f5283ad192bcfcb2c37c50ea38f',
		'posts-edit'=>'0a0ec05f73b541d9e51e4c57222ed50f',
		'posts-del'=>'94e06b10d012424b6490103825f9b121',
		'class-a'=>'e895f6de2c52106e85fba4d1b12e9405',
		'class-b'=>'a55947cbb8360ce2cc59c370c11c6f97'
	];

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

	echo ' -> Including main.php';
		try {
			if(@(include __DIR__.'/../main.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'
				.PHP_EOL.PHP_EOL
				.'Caught: '.$error->getMessage()
				.PHP_EOL;

			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	@mkdir(__DIR__.'/tmp');

	echo ' -> Creating test pool';
		rmdir_recursive(__DIR__.'/tmp/automatic');
		mkdir(__DIR__.'/tmp/automatic');

		mkdir(__DIR__.'/tmp/automatic/dashboard');
		file_put_contents(__DIR__.'/tmp/automatic/dashboard/main.php', "
			<pre><?php \$_module['path']='/fake/path/components/admin_panel/tests/tmp/serve/dashboard'; echo '\$_module: '; var_dump(\$_module); ?></pre>
			<pre><?php echo '\$this->registry: '; var_dump(\$this->registry); ?></pre>
		");
		file_put_contents(__DIR__.'/tmp/automatic/dashboard/config.php', "<?php
			\$this
			->	set_lang('en')
			->	set_title('Dashboard');
		?>");

		mkdir(__DIR__.'/tmp/automatic/posts');
		file_put_contents(__DIR__.'/tmp/automatic/posts/main.php', "
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
		file_put_contents(__DIR__.'/tmp/automatic/posts/config.php', "<?php
			\$this
			->	set_lang('en')
			->	set_title('Posts');
		?>");

		mkdir(__DIR__.'/tmp/automatic/public');

		file_put_contents(__DIR__.'/tmp/automatic/public/index.php', "<?php
			if(!class_exists('admin_panel_class_module'))
			{
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
			}

			\$admin_panel=new admin_panel([
				'base_url'=>'/admin',
				'assets_path'=>'/assets',
				'show_logout_button'=>true,
				'csrf_token'=>['csrf_name', 'csrf_value']
			]);

			\$admin_panel['global_variable']='global_value';

			\$admin_panel
			->	add_favicon(__DIR__.'/favicon.html')
			->	set_logout_button_name('testname')
			->	set_logout_button_label('testlabel')
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
				]);

			\$admin_panel['_favicon']='./favicon.html';

			\$_index_pwd=getcwd();
			chdir(__DIR__);

			\$result=\$admin_panel
			->	set_default_module('dashboard')
			->	run(true);

			chdir(\$_index_pwd);
			unset(\$_index_pwd);
		?>");

		file_put_contents(__DIR__.'/tmp/automatic/public/favicon.html', '<!-- favicon content -->');

		mkdir(__DIR__.'/tmp/automatic/public/assets');
		foreach(array_diff(scandir(__DIR__.'/../templates'), ['.', '..']) as $template)
			foreach(array_diff(scandir(__DIR__.'/../templates/'.$template.'/assets'), ['.', '..']) as $file)
				assets_compiler(__DIR__.'/../templates/'.$template.'/assets/'.$file, __DIR__.'/tmp/automatic/public/assets/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;
	chdir(__DIR__.'/tmp/automatic/public');

	echo ' -> Testing not found';
		$result='';
		$_SERVER['REQUEST_URI']='/admin/notfound';
		include './index.php';
		if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
		{
			file_put_contents(__DIR__.'/tmp/automatic/result_notfound.html', $result);
			echo ' ('.md5($result).')';
		}
		$hash=md5($result);
		if($hash === $test_hashes['notfound'])
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing default';
		$result='';
		$_SERVER['REQUEST_URI']='/admin';
		include './index.php';
		if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
		{
			file_put_contents(__DIR__.'/tmp/automatic/result_default.html', $result);
			echo ' ('.md5($result).')';
		}
		$hash=md5($result);
		if($hash === $test_hashes['default'])
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing dashboard';
		$result='';
		$_SERVER['REQUEST_URI']='/admin/dashboard';
		include './index.php';
		if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
		{
			file_put_contents(__DIR__.'/tmp/automatic/result_dashboard.html', $result);
			echo ' ('.md5($result).')';
		}
		$hash=md5($result);
		if($hash === $test_hashes['dashboard'])
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing posts'.PHP_EOL;
		echo '  -> new';
			$result='';
			$_SERVER['REQUEST_URI']='/admin/posts/new';
			include './index.php';
			if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
			{
				file_put_contents(__DIR__.'/tmp/automatic/result_posts-new.html', $result);
				echo ' ('.md5($result).')';
			}
			if(md5($result) === $test_hashes['posts-new'])
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> edit';
			$result='';
			$_SERVER['REQUEST_URI']='/admin/posts/edit';
			include './index.php';
			if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
			{
				file_put_contents(__DIR__.'/tmp/automatic/result_posts-edit.html', $result);
				echo ' ('.md5($result).')';
			}
			if(md5($result) === $test_hashes['posts-edit'])
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> delete';
			$result='';
			$_SERVER['REQUEST_URI']='/admin/posts/delete';
			include './index.php';
			if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
			{
				file_put_contents(__DIR__.'/tmp/automatic/result_posts-delete.html', $result);
				echo ' ('.md5($result).')';
			}
			if(md5($result) === $test_hashes['posts-del'])
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing class module'.PHP_EOL;
		echo '  -> module A';
			$result='';
			$_SERVER['REQUEST_URI']='/admin/class-test';
			include './index.php';
			if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
			{
				file_put_contents(__DIR__.'/tmp/automatic/result_class-a.html', $result);
				echo ' ('.md5($result).')';
			}
			if(md5($result) === $test_hashes['class-a'])
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> module B';
			$result='';
			$_SERVER['REQUEST_URI']='/admin/class-test-b';
			include './index.php';
			if(isset($argv[1]) && ($argv[1] === 'sumdebug'))
			{
				file_put_contents(__DIR__.'/tmp/automatic/result_class-b.html', $result);
				echo ' ('.md5($result).')';
			}
			if(md5($result) === $test_hashes['class-b'])
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>