<?php
	/*
	 * lv_str.php library test
	 *
	 * Warning:
	 *  ctype extension is recommended
	 *  mbstring extension is recommended
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
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

	$failed=false;

	echo ' -> Testing preg_replace_array';
		if(preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], 'The event will take place between :start and :end'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_camel';
		if(extension_loaded('mbstring')) // from lv_str_studly
		{
			if(lv_str_camel('foo_bar') === 'fooBar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_finish';
		if(lv_str_finish('this/string', '/') === 'this/string/')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_str_finish('this/string/', '/') === 'this/string/')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_headline';
		if(extension_loaded('mbstring')) // from lv_str_title
		{
			if(lv_str_headline('steve_jobs') === 'Steve Jobs')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_headline('EmailNotificationSent') === 'Email Notification Sent')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_is';
		if(lv_str_is('foo*', 'foobar'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_str_is('baz*', 'foobar'))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Testing lv_str_is_url';
		if(lv_str_is_url('http://example.com'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_str_is_url('laravel'))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Testing lv_str_kebab';
		if(extension_loaded('ctype') && extension_loaded('mbstring')) // from lv_str_snake
		{
			if(lv_str_kebab('fooBar') === 'foo-bar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_mask';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_mask('taylor@example.com', '*', 3) === 'tay***************')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_snake';
		if(extension_loaded('ctype') && extension_loaded('mbstring'))
		{
			if(lv_str_snake('fooBar') === 'foo_bar')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_snake('fooBar', '-') === 'foo-bar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_squish';
		if(lv_str_squish('    laravel    framework    ') === 'laravel framework')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_start';
		if(lv_str_start('this/string', '/') === '/this/string')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_str_start('/this/string', '/') === '/this/string')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_studly';
		if(extension_loaded('mbstring')) // from lv_str_ucfirst
		{
			if(lv_str_studly('foo_bar') === 'FooBar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_title';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_title('a nice title uses the correct case') === 'A Nice Title Uses The Correct Case')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_ucfirst';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_ucfirst('foo bar') === 'Foo bar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_ucsplit';
		if((lv_str_ucsplit('FooBar')[0] === 'Foo') && (lv_str_ucsplit('FooBar')[1] === 'Bar'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>