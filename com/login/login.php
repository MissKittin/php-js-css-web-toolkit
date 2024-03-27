<?php
	class login_com_exception extends Exception {}

	if(!function_exists('check_post'))
	{
		if(file_exists(__DIR__.'/lib/check_var.php'))
			require __DIR__.'/lib/check_var.php';
		else if(file_exists(__DIR__.'/../../lib/check_var.php'))
			require __DIR__.'/../../lib/check_var.php';
		else
			throw new login_com_exception('check_var.php library not found');
	}
	if(!class_exists('static_registry'))
	{
		if(file_exists(__DIR__.'/lib/registry.php'))
			require __DIR__.'/lib/registry.php';
		else if(file_exists(__DIR__.'/../../lib/registry.php'))
			require __DIR__.'/../../lib/registry.php';
		else
			throw new login_com_exception('registry.php library not found');
	}
	if(!function_exists('is_logged'))
	{
		if(file_exists(__DIR__.'/lib/sec_login.php'))
			require __DIR__.'/lib/sec_login.php';
		else if(file_exists(__DIR__.'/../../lib/sec_login.php'))
			require __DIR__.'/../../lib/sec_login.php';
		else
			throw new login_com_exception('sec_login.php library not found');
	}

	abstract class login_com_reg extends static_registry { protected static $registry=null; }
	abstract class login_com_reg_config extends static_registry { protected static $registry=null; }
	abstract class login_com_reg_view extends static_registry { protected static $registry=null; }
	abstract class login_com_reg_csp
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
	}

	login_com_reg_config::_()['method']=null; // login_single login_multi login_callback
	login_com_reg_config::_()['remember_cookie_lifetime']=31556926; // 1 year
	login_com_reg_config::_()['session_reload']=function($cookie_lifetime) // default session reloader
	{
		session_start([
			'cookie_lifetime'=>$cookie_lifetime
		]);
	};
	login_com_reg_config::_()['exit_after_login_prompt']=false; // exit after sending the login prompt
	login_com_reg_config::_()['on_login_prompt']=function(){};
	login_com_reg_config::_()['on_login_success']=function(){};
	login_com_reg_config::_()['on_login_failed']=function(){};
	login_com_reg_config::_()['on_logout']=function(){};

	login_com_reg_view::_()['template']='default';
	login_com_reg_view::_()['lang']='en';
	login_com_reg_view::_()['title']='Login'; // <title>
	login_com_reg_view::_()['assets_path']='/assets'; // login_com_reg_view::_()['assets_path']/login_com_reg_view::_()['login_style']
	login_com_reg_view::_()['login_style']='login_default_bright.css'; // login_com_reg_view::_()['assets_path']/login_com_reg_view::_()['login_style']
	login_com_reg_view::_()['inline_style']=false;
	login_com_reg_view::_()['html_headers']='';
	login_com_reg_view::_()['login_label']='Login';
	login_com_reg_view::_()['password_label']='Password';
	login_com_reg_view::_()['login_box_disabled']=false;
	login_com_reg_view::_()['password_box_disabled']=false;
	login_com_reg_view::_()['display_remember_me_checkbox']=true;
	login_com_reg_view::_()['remember_me_label']='Remember me';
	login_com_reg_view::_()['remember_me_box_disabled']=false;
	login_com_reg_view::_()['wrong_credentials_label']='Invalid username or password';
	login_com_reg_view::_()['submit_button_label']='Login';
	login_com_reg_view::_()['submit_button_disabled']=false;
	login_com_reg_view::_()['loading_title']='Loading'; // <title> for reload.php
	login_com_reg_view::_()['loading_label']='Loading...';

	login_com_reg_csp
	::	add('default-src', '\'none\'')
	::	add('script-src', '\'self\'')
	::	add('connect-src', '\'self\'')
	::	add('img-src', '\'self\'')
	::	add('style-src', '\'self\'')
	::	add('base-uri', '\'self\'')
	::	add('form-action', '\'self\'');

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

		if(!is_dir(__DIR__.'/templates/'.login_com_reg_view::_()['template']))
			throw new login_com_exception(login_com_reg_view::_()['template'].' template does not exist');

		if(
			(login_com_reg_view::_()['template'] === 'materialized') &&
			(login_com_reg_view::_()['login_style'] === 'login_default_bright.css')
		)
			login_com_reg_view::_()['login_style']='login_materialized.css';

		if(login_com_reg_view::_()['inline_style'])
			login_com_reg_csp::add('style-src', '\'nonce-mainstyle\'');

		if(!is_callable(login_com_reg_config::_()['on_login_prompt']))
			login_com_reg_config::_()['on_login_prompt']=function(){};
		if(!is_callable(login_com_reg_config::_()['on_login_success']))
			login_com_reg_config::_()['on_login_success']=function(){};
		if(!is_callable(login_com_reg_config::_()['on_login_failed']))
			login_com_reg_config::_()['on_login_failed']=function(){};
		if(!is_callable(login_com_reg_config::_()['on_logout']))
			login_com_reg_config::_()['on_logout']=function(){};

		if(csrf_check_token('post'))
		{
			if(logout(check_post('logout')))
			{
				login_com_reg_config::_()['on_logout']();
				login_refresh('file', __DIR__.'/templates/'.login_com_reg_view::_()['template'].'/views/reload.php');

				exit();
			}

			if(
				(check_post('login_prompt') !== null) &&
				(check_post('login') !== null) &&
				(check_post('password') !== null)
			){
				if(!isset(login_com_reg_config::_()['method']))
					throw new login_com_exception('Login method not specified');

				switch(login_com_reg_config::_()['method'])
				{
					case 'login_single':
						login_com_reg::_()['result']=login_single(
							check_post('login'),
							check_post('password'),
							login_com_reg::_()['credentials'][0],
							login_com_reg::_()['credentials'][1]
						);
					break;
					case 'login_multi':
						login_com_reg::_()['result']=login_multi(
							check_post('login'),
							check_post('password'),
							login_com_reg::_()['credentials']
						);
					break;
					case 'login_callback':
						login_com_reg::_()['result']=login_callback(
							check_post('login'),
							check_post('password'),
							login_com_reg::_()['callback'](check_post('login'))
						);
					break;
					default:
						throw new login_com_exception('Unknown login method');
				}

				if(login_com_reg::_()['result'])
				{
					if(check_post('remember_me') !== null)
						$_SESSION['_login_remember_me']=true;

					login_com_reg_config::_()['on_login_success']();
					login_refresh('file', __DIR__.'/templates/'.login_com_reg_view::_()['template'].'/views/reload.php');

					exit();
				}
				else
				{
					login_com_reg::_()['wrong_credentials']=true;
					login_com_reg_config::_()['on_login_failed']();
				}

				login_com_reg::_()['result']=null;
			}
		}

		if(!is_logged())
		{
			login_com_reg_config::_()['on_login_prompt']();
			require __DIR__.'/templates/'.login_com_reg_view::_()['template'].'/views/form.php';

			if(login_com_reg_config::_()['exit_after_login_prompt'])
				exit();
		}
		else if(check_session('_login_remember_me') === true)
		{
			session_write_close();
			login_com_reg_config::_()['session_reload'](
				login_com_reg_config::_()['remember_cookie_lifetime']
			);
		}
	}
	function login_com_reload(bool $exit=true)
	{
		login_refresh('file', __DIR__.'/templates/'.login_com_reg_view::_()['template'].'/views/reload.php');

		if($exit)
			exit();
	}
?>