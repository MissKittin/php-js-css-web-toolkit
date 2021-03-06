<?php
	include './app/shared/samples/default_http_headers.php';

	if(
		isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
		(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
	)
		ob_start('ob_gzhandler');

	include './app/shared/samples/session_start.php';
	// set custom session reloader
	if(class_exists('lv_cookie_session_handler'))
		$GLOBALS['_login']['config']['session_reload']=function($lifetime)
		{
			lv_cookie_session_handler::session_start([
				'cookie_lifetime'=>$lifetime
			]);
		};

	include './lib/logger.php';
	$log_fails=new log_to_txt([
		'app_name'=>'login-component-test',
		'file'=>'./var/log/fails.log',
		'lock_file'=>'./var/log/fails.log.lock'
	]);
	$log_infos=new log_to_txt([
		'app_name'=>'login-component-test',
		'file'=>'./var/log/infos.log',
		'lock_file'=>'./var/log/infos.log.lock'
	]);

	include './app/models/samples/login_component_test_credentials.php';

	// configure the login component
	$GLOBALS['_login']['config']['method']='login_single';
	$GLOBALS['_login']['view']['lang']='pl';
	$GLOBALS['_login']['view']['title']='Logowanie';
	$GLOBALS['_login']['view']['login_style']='login_bright.css';
	$GLOBALS['_login']['view']['login_label']='Nazwa użytkownika';
	$GLOBALS['_login']['view']['password_label']='Hasło';
	$GLOBALS['_login']['view']['remember_me_label']='Zapamiętaj mnie';
	$GLOBALS['_login']['view']['wrong_credentials_label']='Nieprawidłowa nazwa użytkownika lub hasło';
	$GLOBALS['_login']['view']['submit_button_label']='Zaloguj';
	$GLOBALS['_login']['view']['loading_title']='Ładowanie...';
	$GLOBALS['_login']['view']['loading_label']='Ładowanie...';
	// this cookie is from app/templates/samples/default/assets/default.js/darkTheme.js
	if(
		isset($_COOKIE['app_dark_theme']) &&
		($_COOKIE['app_dark_theme'] === 'true')
	)
		$GLOBALS['_login']['view']['login_style']='login_dark.css';

	// define callbacks for the login component
	$GLOBALS['_login']['config']['on_login_prompt']=function() use($log_infos)
	{
		$log_infos->info('Login prompt requested');
	};
	$GLOBALS['_login']['config']['on_login_success']=function() use($log_infos)
	{
		$log_infos->info('User logged in');
	};
	$GLOBALS['_login']['config']['on_login_failed']=function() use($log_fails)
	{
		$log_fails->info($_SERVER['REMOTE_ADDR'].' login failed');
	};
	$GLOBALS['_login']['config']['on_logout']=function() use($log_infos)
	{
		$log_infos->info('User logged out');
	};

	// display login prompt
	include './components/login/login.php';

	if(is_logged())
	{
		// credentials are valid, check if gd extension is installed for sec_captcha.php

		if(!extension_loaded('gd'))
		{
			$log_fails->warn('gd extension not installed - CAPTCHA test disabled');
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

				// this cookie is from app/templates/samples/default/assets/default.js/darkTheme.js
				if(
					isset($_COOKIE['app_dark_theme']) &&
					($_COOKIE['app_dark_theme'] === 'true')
				)
					$captcha_form->add_config('middleware_form_style', 'middleware_form_dark.css');
				else
					$captcha_form->add_config('middleware_form_style', 'middleware_form_bright.css');

				$captcha_form
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
			return (!file_exists('./var/lib/login_component_test_new_password.php'));
		}
		function are_passwords_valid($old_password, $new_password, $change_password_form)
		{
			if($old_password === $new_password)
			{
				$change_password_form->add_error_message('Nowe hasło nie może być takie samo jak stare');
				return false;
			}

			if(password_verify($new_password, $GLOBALS['_login']['credentials'][1]))
			{
				$change_password_form->add_error_message('Nowe hasło nie może być takie samo jak stare');
				return false;
			}

			if(!password_verify($old_password, $GLOBALS['_login']['credentials'][1]))
			{
				$change_password_form->add_error_message('Stare hasło jest nieprawidłowe');
				return false;
			}

			return true;
		}
		function save_new_password($old_password, $new_password)
		{
			@mkdir('./var');
			@mkdir('./var/lib');

			file_put_contents(
				'./var/lib/login_component_test_new_password.php',
				"<?php \$GLOBALS['_login']['credentials'][1]='".password_hash($new_password, PASSWORD_BCRYPT)."' ?>"
			);
		}

		if(change_password_requested())
		{
			include './components/middleware_form/middleware_form.php';
			$change_password_form=new middleware_form();

			if(
				$change_password_form->is_form_sent() &&
				are_passwords_valid(
					$_POST['old_password'],
					$_POST['new_password'],
					$change_password_form
				)
			){
				save_new_password($_POST['old_password'], $_POST['new_password']);
				$log_infos->info('Password updated');

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