<?php
	include './app/shared/samples/default_http_headers.php';

	if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
		ob_start('ob_gzhandler');

	include './app/shared/samples/session_start.php';
	// set custom session reloader
	if(class_exists('lv_cookie_session_handler'))
		$GLOBALS['login']['config']['session_reload']=function($lifetime)
		{
			lv_cookie_session_handler::session_start([
				'cookie_lifetime'=>$lifetime
			]);
		};

	include './app/models/samples/login_component_test_credentials.php';
	include './app/shared/samples/login_config.php';
	include './components/login/login.php'; // display login prompt

	// save fails to journal
	if(isset($GLOBALS['login']['login_failed']))
	{
		@mkdir('./var');
		@mkdir('./var/log');

		include './lib/logger.php';
		$log=new log_to_txt([
			'app_name'=>'login-component-test',
			'file'=>'./var/log/faillog.txt',
			'lock_file'=>'./var/log/faillog.txt.lock'
		]);
		$log->log('INFO', $_SERVER['REMOTE_ADDR']);
	}

	if(is_logged())
	{
		// credentials are valid, check if gd extension is installed for sec_captcha.php

		if(!extension_loaded('gd'))
		{
			$log->log('WARN', 'gd extension not installed - CAPTCHA test disabled');
			$_SESSION['captcha_verified']=true;
		}

		// if gd installed, do captcha now

		if(!isset($_SESSION['captcha_verified']))
		{
			include './lib/sec_captcha.php';

			if((!isset($_POST['captcha'])) || (!captcha_check($_POST['captcha'])))
			{
				include './components/middleware_form/middleware_form.php';
				$captcha_form=new middleware_form();

				$captcha_form
					->add_html_header(
						'<script>document.addEventListener(\'DOMContentLoaded\', function(){'
							.file_get_contents('./lib/disableEnterOnForm.js')
							.file_get_contents('./app/views/samples/login-component-test/middleware_form.js')
						.'})</script>'
					)
					->add_csp_header('script-src', '\'sha256-i7O9RlEhU3xgPLwptAzsYt/FTWOe7Q8NrYrH0zecJvk=\'');

				$captcha_form
					->add_csp_header('img-src', 'data:')
					->add_csp_header('style-src', '\'unsafe-hashes\'')
					->add_csp_header('style-src', '\'sha256-N6tSydZ64AHCaOWfwKbUhxXx2fRFDxHOaL3e3CO7GPI=\'');

				$captcha_form
					->add_config('middleware_form_style', 'middleware_form_bright.css')
					->add_config('title', 'Weryfikacja')
					->add_config('submit_button_label', 'Dalej');

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
						'placeholder'=>'Przepisz tekst z obrazka'
					])
					->add_field([
						'tag'=>'input',
						'type'=>'slider',
						'slider_label'=>'Pokarz batona',
						'name'=>'i_am_bam'
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

		// captcha test passed, change password on first login

		// check-update functions
		function change_password_requested()
		{
			return !file_exists('./var/lib/login_component_test_new_password.php');
		}
		function are_passwords_valid($old_password, $new_password)
		{
			if($old_password === $new_password)
				return false;
			if(password_verify($new_password, $GLOBALS['login']['credentials'][1]))
				return false;

			return password_verify($old_password, $GLOBALS['login']['credentials'][1]);
		}
		function save_new_password($old_password, $new_password)
		{
			@mkdir('./var');
			@mkdir('./var/lib');

			file_put_contents(
				'./var/lib/login_component_test_new_password.php',
				"<?php \$GLOBALS['login']['credentials'][1]='".password_hash($new_password, PASSWORD_BCRYPT)."' ?>"
			);
		}

		if(change_password_requested())
		{
			include './components/middleware_form/middleware_form.php';
			$change_password_form=new middleware_form();

			if(
				($change_password_form->is_form_sent()) &&
				are_passwords_valid($_POST['old_password'], $_POST['new_password'])
			){
				save_new_password($_POST['old_password'], $_POST['new_password']);

				include './components/login/reload.php'; // display reload page
				exit();
			}
			else
			{
				$change_password_form
					->add_config('title', 'Zmiana hasła')
					->add_config('submit_button_label', 'Zmień hasło');

				$change_password_form
					->add_field([
						'tag'=>'input',
						'type'=>'password',
						'name'=>'old_password',
						'placeholder'=>'Stare hasło'
					])
					->add_field([
						'tag'=>'input',
						'type'=>'password',
						'name'=>'new_password',
						'placeholder'=>'Nowe hasło'
					]);

					$change_password_form->view();
					exit();
			}
		}

		// password changed, you can see the content

		include './app/templates/samples/default/default_template.php';
		default_template::quick_view('./app/views/samples/login-component-test');
	}
?>