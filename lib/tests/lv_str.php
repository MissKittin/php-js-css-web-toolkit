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
	echo ' -> Testing lv_str_after';
		if(lv_str_after('This is my name', 'This is') === ' my name')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_after_last';
		if(lv_str_after_last('App\Http\Controllers\Controller', '\\') === 'Controller')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_before';
		if(lv_str_before('This is my name', 'my name') === 'This is ')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_before_last';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_before_last('This is my name', 'is') === 'This ')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_between';
		if(lv_str_between('This is my name', 'This', 'name') === ' is my ')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_between_first';
		if(lv_str_between_first('[a] bc [d]', '[', ']') === 'a')
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
	echo ' -> Testing lv_str_lcfirst';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_lcfirst('Foo Bar') === 'foo Bar')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_length';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_length('Laravel') === 7)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_limit';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_limit('The quick brown fox jumps over the lazy dog', 20) === 'The quick brown fox...')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_limit('The quick brown fox jumps over the lazy dog', 20, ' (...)') === 'The quick brown fox (...)')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_lower';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_lower('LARAVEL') === 'laravel')
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
	echo ' -> Testing lv_str_pad_both';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_pad_both('James', 10, '_') === '__James___')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_pad_both('James', 10) === '  James   ')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_pad_left';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_pad_left('James', 10, '-=') === '-=-=-James')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_pad_left('James', 10) === '     James')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_pad_right';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_pad_right('James', 10, '-') === 'James-----')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_pad_right('James', 10) === 'James     ')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_random: ';
		echo lv_str_random(8).PHP_EOL;
	echo ' -> Testing lv_str_repeat';
		if(lv_str_repeat('a', 5) === 'aaaaa')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_replace_first';
		if(lv_str_replace_first('the', 'a', 'the quick brown fox jumps over the lazy dog') === 'a quick brown fox jumps over the lazy dog')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_replace_last';
		if(lv_str_replace_last('the', 'a', 'the quick brown fox jumps over the lazy dog') === 'the quick brown fox jumps over a lazy dog')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
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
	echo ' -> Testing lv_str_substr';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_substr('The Laravel Framework', 4, 7) === 'Laravel')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_substr_count';
		if(lv_str_substr_count('If you like ice cream, you will like snow cones.', 'like') === 2)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_substr_replace';
		if(lv_str_substr_replace('1300', ':', 2) === '13:')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_str_substr_replace('1300', ':', 2, 0) === '13:00')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_swap';
		if(lv_str_swap(['Tacos'=>'Burritos', 'great'=>'fantastic'], 'Tacos are great!') === 'Burritos are fantastic!')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
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
	echo ' -> Testing lv_str_upper';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_upper('laravel') === 'LARAVEL')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_word_count';
		if(lv_str_word_count('Hello, world!') === 2)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_word_wrap';
		if(lv_str_word_wrap('The quick brown fox jumped over the lazy dog.', 20, '<br />') === 'The quick brown fox<br />jumped over the lazy<br />dog.')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_str_words';
		if(extension_loaded('mbstring'))
		{
			if(lv_str_words('Perfectly balanced, as all things should be.', 3, ' >>>') === 'Perfectly balanced, as >>>')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_str_wrap';
		if(lv_str_wrap('Laravel', '"') === '"Laravel"')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_str_wrap('is', 'This ', ' Laravel!') === 'This is Laravel!')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>