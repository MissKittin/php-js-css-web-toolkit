<?php
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

	echo ' -> Including admin_panel.php';
		try {
			if(@(include __DIR__.'/../admin_panel.php') === false)
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

	echo ' -> Creating test pool...';
		rmdir_recursive(__DIR__.'/tmp/automatic');
		mkdir(__DIR__.'/tmp/automatic');

		mkdir(__DIR__.'/tmp/automatic/dashboard');
		file_put_contents(__DIR__.'/tmp/automatic/dashboard/main.php', "
			<pre><?php \$_module['path']='/fake/path/components/admin_panel/tests/tmp/serve/dashboard'; echo '\$_module: '; var_dump(\$_module); ?></pre>
			<pre><?php echo '\$this->registry: '; var_dump(\$this->registry); ?></pre>
		");
		file_put_contents(__DIR__.'/tmp/automatic/dashboard/config.php', "<?php
			\$this
				->set_lang('en')
				->set_title('Dashboard')
			;
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
				->set_lang('en')
				->set_title('Posts')
			;
		?>");

		mkdir(__DIR__.'/tmp/automatic/public');

		file_put_contents(__DIR__.'/tmp/automatic/public/index.php', "<?php
			\$admin_panel=new admin_panel([
				'base_url'=>'/admin',
				'assets_path'=>'/assets',
				'show_logout_button'=>true,
				'csrf_token'=>['csrf_name', 'csrf_value']
			]);

			\$admin_panel['global_variable']='global_value';

			\$admin_panel
				->add_module([
					'id'=>'dashboard',
					'path'=>'../dashboard',
					'config'=>'config.php',
					'script'=>'main.php',
					'url'=>'dashboard',
					'name'=>'Dashboard',
					'template_header'=>'Dashboard'
				])
				->add_module([
					'id'=>'posts',
					'path'=>'../posts',
					'config'=>'config.php',
					'script'=>'main.php',
					'url'=>'posts',
					'name'=>'Posts',
					'template_header'=>'Posts',
					'custom_variable'=>'Custom variable here'
				])
				->add_menu_entry([
					'id'=>'github',
					'url'=>'https://github.com/MissKittin/php-js-css-web-toolkit',
					'name'=>'GitHub'
				])
			;

			\$result=\$admin_panel
				->set_default_module('dashboard')
				->run(true)
			;
		?>");

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
		if(md5($result) === '6f99866edf0752d8998de191ba9e4dfe')
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
		if(md5($result) === '8bfff958d6511b65d189fd255e9f3054')
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
		if(md5($result) === 'd38afec2d9653241e7593d40a8fa241f')
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
		if(md5($result) === '2551553867cc7ab71f33b1cca6920f75')
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
		if(md5($result) === '236f2ed988c31940f7d39942827c96f6')
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
		if(md5($result) === '13e756911e19d0a77538e0d1429b068b')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>