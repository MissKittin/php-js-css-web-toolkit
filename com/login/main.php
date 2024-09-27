<?php
	class login_com_exception extends Exception {}

	(function($libraries){
		foreach($libraries as $check_function=>$library_meta)
			foreach($library_meta as $library_file=>$library_function)
				if(!$check_function($library_function))
				{
					if(file_exists(__DIR__.'/lib/'.$library_file))
						require __DIR__.'/lib/'.$library_file;
					else if(file_exists(__DIR__.'/../../lib/'.$library_file))
						require __DIR__.'/../../lib/'.$library_file;
					else
						throw new login_com_exception($library_file.' library not found');
				}
	})([
		'class_exists'=>[
			'registry.php'=>'static_registry'
		],
		'function_exists'=>[
			'check_var.php'=>'check_post',
			'sec_login.php'=>'is_logged'
		]
	]);

	final class login_com_reg extends static_registry
	{
		protected static $registry=null;
		private function __construct() {}
	}
	final class login_com_reg_config extends static_registry
	{
		protected static $registry=null;
		private function __construct() {}
	}
	final class login_com_reg_view extends static_registry
	{
		protected static $registry=null;
		private function __construct() {}
	}
	final class login_com_reg_csp
	{
		protected static $registry=[];

		public static function add(string $policy, string $rule)
		{
			static::$registry[$policy][]=$rule;
			return static::class;
		}
		public static function get()
		{
			return static::$registry;
		}

		private function __construct() {}
	}

	(function(){
		foreach([
			'method'=>null, // login_single login_multi login_callback
			'remember_cookie_lifetime'=>31556926, // 1 year
			'session_reload'=>function($cookie_lifetime) // default session reloader
			{
				session_start([
					'cookie_lifetime'=>$cookie_lifetime
				]);
			},
			'on_login_prompt'=>function(){},
			'on_login_success'=>function(){},
			'on_login_failed'=>function(){},
			'on_logout'=>function(){},
			'reload_by_http'=>true
		] as $key=>$value)
			login_com_reg_config::_()[$key]=$value;

		foreach([
			'template'=>'default',
			'templates_dir'=>__DIR__.'/templates',
			'lang'=>'en',
			'title'=>'Login', // <title>
			'assets_path'=>'/assets', // 'assets_path']/'login_style']
			'login_style'=>'login_default_bright.css', // 'assets_path']/'login_style']
			'inline_style'=>false,
			'html_headers'=>'',
			'favicon'=>null,
			'login_label'=>'Login',
			'password_label'=>'Password',
			'login_box_disabled'=>false,
			'password_box_disabled'=>false,
			'display_remember_me_checkbox'=>true,
			'remember_me_label'=>'Remember me',
			'remember_me_box_disabled'=>false,
			'wrong_credentials_label'=>'Invalid username or password',
			'submit_button_label'=>'Login',
			'submit_button_disabled'=>false,
			'loading_title'=>'Loading', // <title> for reload.php
			'loading_label'=>'Loading...'
		] as $key=>$value)
			login_com_reg_view::_()[$key]=$value;

		login_com_reg_csp
		::	add('default-src', '\'none\'')
		::	add('script-src', '\'self\'')
		::	add('connect-src', '\'self\'')
		::	add('img-src', '\'self\'')
		::	add('style-src', '\'self\'')
		::	add('base-uri', '\'self\'')
		::	add('form-action', '\'self\'');
	})();

	function login_com()
	{
		if(!function_exists('csrf_check_token'))
		{
			if(file_exists(__DIR__.'/lib/sec_csrf.php'))
				require __DIR__.'/lib/sec_csrf.php';
			else if(file_exists(__DIR__.'/../../lib/sec_csrf.php'))
				require __DIR__.'/../../lib/sec_csrf.php';
			else
				throw new login_com_exception('sec_csrf.php library not found');
		}

		if(!is_dir(login_com_reg_view::_()['templates_dir']))
			throw new login_com_exception(login_com_reg_view::_()['templates_dir'].' is not a directory');

		if(!is_dir(login_com_reg_view::_()['templates_dir'].'/'.login_com_reg_view::_()['template']))
			throw new login_com_exception(login_com_reg_view::_()['template'].' template does not exist');

		if(login_com_reg_view::_()['inline_style'])
		{
			if(!function_exists('rand_str_secure'))
			{
				if(file_exists(__DIR__.'/lib/rand_str.php'))
					require __DIR__.'/lib/rand_str.php';
				else if(file_exists(__DIR__.'/../../lib/rand_str.php'))
					require __DIR__.'/../../lib/rand_str.php';
				else
					throw new login_com_exception('rand_str.php library not found');
			}

			login_com_reg::_()['inline_style_nonce']=rand_str_secure(32);
			login_com_reg_csp::add('style-src', '\'nonce-'.login_com_reg::_()['inline_style_nonce'].'\'');
		}

		if(
			(login_com_reg_view::_()['template'] === 'materialized') &&
			(login_com_reg_view::_()['login_style'] === 'login_default_bright.css')
		)
			login_com_reg_view::_()['login_style']='login_materialized.css';

		foreach(['on_login_prompt', 'on_login_success', 'on_login_failed', 'on_logout'] as $callback)
			if(!is_callable(login_com_reg_config::_()[$callback]))
				login_com_reg_config::_()[$callback]=function(){};

		if(csrf_check_token('post'))
		{
			if(check_post('logout') !== null)
			{
				logout();
				login_com_reg_config::_()['on_logout']();
				login_com_reload();

				return true;
			}

			$login=check_post('login');
			$password=check_post('password');

			if(
				(check_post('login_prompt') !== null) &&
				($login !== null) &&
				($password !== null)
			){
				if(!isset(login_com_reg_config::_()['method']))
					throw new login_com_exception('Login method not specified');

				switch(login_com_reg_config::_()['method'])
				{
					case 'login_single':
						if(!is_array(login_com_reg::_()['credentials']))
						{
							login_com_reg::_()['result']=false;
							break;
						}

						login_com_reg::_()['result']=login_single(
							$login,
							$password,
							login_com_reg::_()['credentials'][0],
							login_com_reg::_()['credentials'][1]
						);
					break;
					case 'login_multi':
						login_com_reg::_()['result']=login_multi(
							$login,
							$password,
							login_com_reg::_()['credentials']
						);
					break;
					case 'login_callback':
						login_com_reg::_()['result']=login_callback(
							$login,
							$password,
							login_com_reg::_()['callback'](check_post('login'))
						);
					break;
					default:
						throw new login_com_exception('Unknown login method');
				}

				if(login_com_reg::_()['result'])
				{
					if(check_post('remember_me') !== null)
						$_SESSION['_com_login_remember_me']=true;

					login_com_reg_config::_()['on_login_success']();
					login_com_reload();

					return true;
				}

				login_com_reg::_()['wrong_credentials']=true;
				login_com_reg_config::_()['on_login_failed']();
				login_com_reg::_()['result']=null;
			}
		}

		if(!is_logged())
		{
			if(
				(login_com_reg_view::_()['favicon'] !== null) &&
				(!file_exists(login_com_reg_view::_()['favicon']))
			)
				throw new login_com_exception(login_com_reg_view::_()['favicon'].' does not exist');

			login_com_reg_config::_()['on_login_prompt']();
			require login_com_reg_view::_()['templates_dir'].'/'.login_com_reg_view::_()['template'].'/views/form.php';

			return true;
		}

		if(check_session('_com_login_remember_me') === true)
		{
			session_write_close();
			login_com_reg_config::_()['session_reload'](
				login_com_reg_config::_()['remember_cookie_lifetime']
			);
		}

		return false;
	}
	function login_com_reload()
	{
		header('Cache-Control: no-store, no-cache, must-revalidate', true);
		header('Expires: 0', true);

		if(login_com_reg_config::_()['reload_by_http'] === true)
			return header('Location: '.$_SERVER['REQUEST_URI'], true, 302);

		if(
			(login_com_reg_view::_()['favicon'] !== null) &&
			(!file_exists(login_com_reg_view::_()['favicon']))
		)
			throw new login_com_exception(login_com_reg_view::_()['favicon'].' does not exist');

		if(login_com_reg_view::_()['inline_style'])
		{
			if(!function_exists('rand_str_secure'))
			{
				if(file_exists(__DIR__.'/lib/rand_str.php'))
					require __DIR__.'/lib/rand_str.php';
				else if(file_exists(__DIR__.'/../../lib/rand_str.php'))
					require __DIR__.'/../../lib/rand_str.php';
				else
					throw new login_com_exception('rand_str.php library not found');
			}

			login_com_reg::_()['inline_style_nonce']=rand_str_secure(32);
		}

		login_refresh('require-file', login_com_reg_view::_()['templates_dir'].'/'.login_com_reg_view::_()['template'].'/views/reload.php');
	}
?>