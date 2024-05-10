<?php
	/*
	 * add ?theme=dark in url to apply login_default_dark.css
	 * add ?theme=materialized in url to apply login_materialized.css
	 * set TEST_INLINE_STYLE=yes to test inline styles option
	 */

	if(php_sapi_name() === 'cli-server')
	{
		error_log('Request '.$_SERVER['REQUEST_URI']);

		if(substr($_SERVER['REQUEST_URI'], 0, 8) === '/assets/')
		{
			switch($_SERVER['REQUEST_URI'])
			{
				case '/assets/login_default_bright.css':
					$assets_template='default';
					$assets_dir='login_default_bright.css';
				break;
				case '/assets/login_default_dark.css':
					$assets_template='default';
					$assets_dir='login_default_dark.css';
				break;
				case '/assets/login_materialized.css':
					$assets_template='materialized';
					$assets_dir='login_materialized.css';
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

		include __DIR__.'/../main.php';

		if(getenv('TEST_INLINE_STYLE') === 'yes')
			login_com_reg_view::_()['inline_style']=true;

		session_start();

		if(isset($_GET['theme']))
			switch($_GET['theme'])
			{
				case 'dark':
					login_com_reg_view::_()['template']='default';
					login_com_reg_view::_()['login_style']='login_default_dark.css';
				break;
				case 'materialized':
					login_com_reg_view::_()['template']='materialized';
					login_com_reg_view::_()['login_style']='login_materialized.css';
			}

		login_com();

		exit();
	}

	if(!isset($argv[1]))
	{
		echo 'Use "serve.php serve" to start built-in server'.PHP_EOL;
		echo 'Note:'.PHP_EOL;
		echo ' set TEST_INLINE_STYLE=yes to test inline styles option'.PHP_EOL;
		exit();
	}
	if($argv[1] !== 'serve')
	{
		echo 'Use "serve.php serve" to start built-in server'.PHP_EOL;
		echo 'Note:'.PHP_EOL;
		echo ' set TEST_INLINE_STYLE=yes to test inline styles option'.PHP_EOL;
		exit();
	}

	echo 'Starting PHP server...'.PHP_EOL.PHP_EOL;
	system('"'.PHP_BINARY.'" -S 127.0.0.1:8080  '.__FILE__);
?>