<?php
	/*
	 * add ?theme=dark in url to apply middleware_form_default_dark.css
	 * add ?theme=materialized in url to apply middleware_form_materialized.css
	 * set TEST_INLINE_STYLE=yes to test inline styles option
	 */

	if(php_sapi_name() === 'cli-server')
	{
		error_log('Request '.$_SERVER['REQUEST_URI']);

		if(substr($_SERVER['REQUEST_URI'], 0, 8) === '/assets/')
		{
			switch($_SERVER['REQUEST_URI'])
			{
				case '/assets/middleware_form_default_bright.css':
					$assets_template='default';
					$assets_dir='middleware_form_default_bright.css';
				break;
				case '/assets/middleware_form_default_dark.css':
					$assets_template='default';
					$assets_dir='middleware_form_default_dark.css';
				break;
				case '/assets/middleware_form_materialized.css':
					$assets_template='materialized';
					$assets_dir='middleware_form_materialized.css';
				break;
				case '/assets/simpleblog_materialized.css':
					error_log(' -> Reading simpleblog_materialized.css');

					header('Content-Type: text/css');

					if(is_file(__DIR__.'/../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../lib/simpleblog_materialized.css');
					else if(is_file(__DIR__.'/../../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../../lib/simpleblog_materialized.css');
					else
						echo '/* simpleblog_materialized.css library not found */';

					exit();
				;;
				default:
					exit();
			}

			error_log(' -> Compiling '.$_SERVER['REQUEST_URI']);

			header('Content-Type: text/css');

			foreach(array_diff(scandir(__DIR__.'/../templates/'.$assets_template.'/assets/'.$assets_dir), ['.', '..']) as $file)
				readfile(__DIR__.'/../templates/'.$assets_template.'/assets/'.$assets_dir.'/'.$file);

			exit();
		}

		session_start();

		include __DIR__.'/../middleware_form.php';

		if(isset($_GET['theme']) && ($_GET['theme'] === 'materialized'))
			$middleware_form=new middleware_form('materialized');
		else if(isset($_GET['theme']) && ($_GET['theme'] === 'dark'))
		{
			$middleware_form=new middleware_form();
			$middleware_form->add_config('middleware_form_style', 'middleware_form_default_dark.css');
		}
		else
			$middleware_form=new middleware_form();

		if(getenv('TEST_INLINE_STYLE') === 'yes')
			$middleware_form->add_config('inline_style', true);

		$middleware_form
			->add_config('title', 'Weryfikacja')
			->add_config('submit_button_label', 'Dalej');

		$middleware_form
			->add_field([
				'tag'=>'input',
				'type'=>'text',
				'name'=>'captcha_input',
				'placeholder'=>'Przepisz tekst z obrazka'
			])

			->add_field([
				'tag'=>'input',
				'type'=>'checkbox',
				'name'=>'captcha',
				'label'=>'Uncheck me',
				'checked'=>null
			])

			->add_field([
				'tag'=>null,
				'content'=>'<a href="?theme=bright">Bright theme here</a><br>'
			])
			->add_field([
				'tag'=>null,
				'content'=>'<a href="?theme=dark">Dark theme here</a><br>'
			])
			->add_field([
				'tag'=>null,
				'content'=>'<a href="?theme=materialized">Materialized template here</a><hr>'
			])

			->add_field([
				'tag'=>'input',
				'type'=>'radio',
				'name'=>'captcha',
				'label'=>'Check me...',
				'checked'=>null
			])
			->add_field([
				'tag'=>'input',
				'type'=>'radio',
				'name'=>'captcha',
				'label'=>'...or me'
			])

			->add_field([
				'tag'=>'input',
				'type'=>'slider',
				'slider_label'=>'Pokarz batona',
				'name'=>'i_am_bam',
				'checked'=>null
			])
			->add_field([
				'tag'=>'input',
				'type'=>'slider',
				'slider_label'=>'Pokarz batona',
				'name'=>'i_am_bam'
			]);

		if(isset($_POST['captcha_input']))
			$middleware_form->add_error_message($_POST['captcha_input']); // don't do that!

		$middleware_form->view();

		exit();
	}

	if(!isset($argv[1]))
	{
		echo 'Use "serve.php serve" to start built-in server'.PHP_EOL;
		exit();
	}
	if($argv[1] !== 'serve')
	{
		echo 'Use "serve.php serve" to start built-in server'.PHP_EOL;
		exit();
	}

	echo 'Starting PHP server...'.PHP_EOL.PHP_EOL;
	system(PHP_BINARY.' -S 127.0.0.1:8080  '.__FILE__);
?>