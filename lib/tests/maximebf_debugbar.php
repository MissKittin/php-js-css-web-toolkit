<?php
	/*
	 * maximebf_debugbar.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  php-debugbar/php-debugbar package is required
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  var_export_contains.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php',
			'var_export_contains.php'
		] as $library){
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
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

		echo ' -> Mocking classes';
			class Exception extends \Exception {}
			interface ArrayAccess extends \ArrayAccess {}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
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

		// !!! uses require --dev !!!
		if(
			(!file_exists(__DIR__.'/tmp/.composer/vendor/maximebf/debugbar')) &&
			(!file_exists(__DIR__.'/tmp/.composer/vendor/php-debugbar/php-debugbar'))
		){
			@mkdir(__DIR__.'/tmp');
			@mkdir(__DIR__.'/tmp/.composer');

			if(file_exists(__DIR__.'/../../bin/composer.phar'))
				$_composer_binary=__DIR__.'/../../bin/composer.phar';
			else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
				$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
			else if(file_exists(__DIR__.'/../../bin/get-composer.php'))
			{
				echo '  -> Downloading composer'.PHP_EOL;

				system(''
				.	'"'.PHP_BINARY.'" '
				.	'"'.__DIR__.'/../../bin/get-composer.php" '
				.	'"'.__DIR__.'/tmp/.composer"'
				);

				if(!file_exists(__DIR__.'/tmp/.composer/composer.phar'))
				{
					echo '  <- composer download failed [FAIL]'.PHP_EOL;
					exit(1);
				}

				$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
			}
			else
			{
				echo 'Error: get-composer.php tool not found'.PHP_EOL;
				exit(1);
			}

			$debugbar_version='';

			if(PHP_VERSION_ID < 70130) // symfony/var-dumper dependency bug in php-debugbar/php-debugbar
				$debugbar_version=':1.18.1';

			echo '  -> Installing php-debugbar/php-debugbar'.PHP_EOL;
				system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
				.	'--no-cache '
				.	'"--working-dir='.__DIR__.'/tmp/.composer" '
				.	'require --dev php-debugbar/php-debugbar'.$debugbar_version
				);
		}

		echo ' -> Including composer autoloader';
			if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;

		if(!class_exists('\DebugBar\DebugBar'))
		{
			echo ' <- php-debugbar/php-debugbar package is not installed [FAIL]'.PHP_EOL;
			exit(1);
		}

		echo ' -> Mocking classes and functions';
			} namespace Test\DebugBar {
				class DebugBar extends \DebugBar\DebugBar {}
			} namespace Test {
			class maximebf_debugbar_test extends maximebf_debugbar
			{
				public static function test_vendor_dir()
				{
					return static::$vendor_dir;
				}
			}
			$GLOBALS['header_output']='';
			function header($header)
			{
				$GLOBALS['header_output']=$header;
			}
			$GLOBALS['readfile_output']='';
			function readfile($file)
			{
				$GLOBALS['readfile_output']=$file;
			}
		echo ' [ OK ]'.PHP_EOL;

		$failed=false;

		echo ' -> Testing enable()';
			if(maximebf_debugbar_test::is_enabled())
			{
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			maximebf_debugbar_test::enable(false);
			if(maximebf_debugbar_test::is_enabled())
			{
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			maximebf_debugbar_test::enable(true);
			if(maximebf_debugbar_test::is_enabled())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing set_vendor_dir()';
			maximebf_debugbar_test::set_vendor_dir(__DIR__.'/tmp/maximebf_debugbar');
			if(maximebf_debugbar_test::test_vendor_dir() === __DIR__.'/tmp/maximebf_debugbar')
			{
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			maximebf_debugbar_test::set_vendor_dir(__DIR__.'/tmp/.composer/vendor');
			if(maximebf_debugbar_test::test_vendor_dir() === __DIR__.'/tmp/.composer/vendor')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			maximebf_debugbar_test::set_vendor_dir(__DIR__.'/tmp/.composer');
			if(maximebf_debugbar_test::test_vendor_dir() === __DIR__.'/tmp/.composer/vendor')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing route()'.PHP_EOL;
			echo '  -> base URL not set exception';
				try {
					maximebf_debugbar_test::route('/debugbar/debugbar.css');
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				} catch(Exception $error) {
					echo ' [ OK ]'.PHP_EOL;
				}
				maximebf_debugbar_test::set_base_url('/debugbar');
			echo '  -> path traversal protection';
				if(maximebf_debugbar_test::route('/debugbar/../debugbar/debugbar.css') === false)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> requested file does not exist';
				if(maximebf_debugbar_test::route('/debugbar/debugbar__NONEXISTENT__.css') === false)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> routing success';
				if(maximebf_debugbar_test::route('/debugbar/debugbar.css') === true)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($GLOBALS['header_output'] === 'Content-Type: text/css')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(basename($GLOBALS['readfile_output']) === 'debugbar.css')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> print requested file path';
				if(basename(maximebf_debugbar_test::route('/debugbar/debugbar.css', true)) === 'debugbar.css')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing get_instance()';
			if(maximebf_debugbar_test::get_instance() instanceof DebugBar\DebugBar)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing CSP methods';
			maximebf_debugbar_test
			::	set_csp_nonce('barnonce')
			::	add_csp_header('test-section', 'testvalue');
			//echo ' ('.var_export_contains(maximebf_debugbar_test::get_csp_headers(), '', true).')';
			if(var_export_contains(maximebf_debugbar_test::get_csp_headers(), "array('script-src'=>array(0=>'\'nonce-barnonce\'',),'img-src'=>array(0=>'data:',),'font-src'=>array(0=>'\'self\'',1=>'data:',),'test-section'=>array(0=>'testvalue',),)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing get_html_headers()';
			//echo ' ('.maximebf_debugbar_test::get_html_headers().')';
			if(strpos(maximebf_debugbar_test::get_html_headers(), '/debugbar/') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing get_page_content()';
			//echo ' ('.maximebf_debugbar_test::get_page_content().')';
			if(strpos(maximebf_debugbar_test::get_page_content(), 'nonce="barnonce"') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;


		if($failed)
			exit(1);
	}
?>