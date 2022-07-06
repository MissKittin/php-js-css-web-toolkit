<?php
	/*
	 * add ?theme=dark in url to apply middleware_form_dark.css
	 */

	if(php_sapi_name() === 'cli-server')
	{
		error_log('Request '.$_SERVER['REQUEST_URI']);

		if(
			($_SERVER['REQUEST_URI'] === '/assets/middleware_form_bright.css') ||
			($_SERVER['REQUEST_URI'] === '/assets/middleware_form_dark.css')
		){
			error_log(' -> Compiling '.$_SERVER['REQUEST_URI']);

			header('Content-Type: text/css');

			foreach(array_slice(scandir(__DIR__.'/../'.$_SERVER['REQUEST_URI']), 2) as $file)
				readfile(__DIR__.'/../'.$_SERVER['REQUEST_URI'].'/'.$file);

			exit();
		}

		session_start();

		include __DIR__.'/../middleware_form.php';

		$middleware_form=new middleware_form();

		if(
			isset($_GET['theme']) &&
			($_GET['theme'] === 'dark')
		)
			$middleware_form->add_config('middleware_form_style', 'middleware_form_dark.css');
		else
			$middleware_form->add_config('middleware_form_style', 'middleware_form_bright.css');

		$middleware_form
			->add_config('title', 'Weryfikacja')
			->add_config('submit_button_label', 'Dalej');

		$middleware_form
			->add_field([
				'tag'=>'input',
				'type'=>'text',
				'name'=>'captcha',
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
				'content'=>'<a href="?theme=dark">Dark theme here</a><hr>'
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