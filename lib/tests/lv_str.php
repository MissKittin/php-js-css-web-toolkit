<?php
	/*
	 * lv_str.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Dev note:
	 *  you can change the lv_str_of function for stringable tests:
	 *   $lv_helpers_skip // do not test helpers, default: false
	 *   $lv_stringable_header // label, default: 'lv_str_ingable'
	 *   $lv_stringable_function // default: 'lv_str_of'
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  pf_mbstring.php library is required
	 *  var_export_contains.php library is required
	 *  ctype extension is recommended
	 *  mbstring extension is recommended
	 */

	namespace
	{
		foreach(['pf_mbstring.php', 'var_export_contains.php'] as $library)
		{
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

		if(class_exists('lv_str_exception'))
			echo ' -> Including '.basename(__FILE__).' [SKIP]'.PHP_EOL;
		else
		{
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
		}

		$failed=false;

		if(isset($lv_helpers_skip) && ($lv_helpers_skip === true))
			echo ' -> Skipping helpers test'.PHP_EOL;
		else
		{
			echo ' -> Testing class_basename';
				if(class_basename('Foo\Bar\Baz') === 'Baz')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing preg_replace_array';
				if(lv_str_preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], 'The event will take place between :start and :end'))
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
			echo ' -> Testing lv_str_apa';
				if(function_exists('mb_strpos'))
				{
					if(lv_str_apa('a nice title uses the correct case') === 'A Nice Title Uses the Correct Case')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_before';
				if(lv_str_before('This is my name', 'my name') === 'This is ')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_before_last';
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
				{
					if(lv_str_between('This is my name', 'This', 'name') === ' is my ')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_between_first';
				if(lv_str_between_first('[a] bc [d]', '[', ']') === 'a')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_camel';
				if(function_exists('mb_strpos')) // from lv_str_studly
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
			echo ' -> Testing lv_str_char_at';
				if(function_exists('mb_strpos'))
				{
					if(lv_str_char_at('This is my name.', 6) === 's')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_contains';
				if(function_exists('mb_substr'))
				{
					if(lv_str_contains('This is my name', 'my'))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_contains('This is my name', 'xdd'))
					{
						echo ' [FAIL]';
						$failed=true;
					}
					else
						echo ' [ OK ]';
					if(lv_str_contains('This is my name', ['my', 'foo']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_contains_all';
				if(function_exists('mb_substr'))
				{
					if(lv_str_contains_all('This is my name', ['my', 'name']))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_contains_all('This is my name', ['my', 'nameE']))
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
					else
						echo ' [ OK ]'.PHP_EOL;
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_ends_with';
				if(lv_str_ends_with('This is my name', 'name'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_ends_with('This is my name', ['name', 'foo']))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_ends_with('This is my name', ['this', 'foo']))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo ' -> Testing lv_str_excerpt';
				if(function_exists('mb_strpos'))
				{
					if(lv_str_excerpt('This is my name', 'my', [
							'radius'=>3
					]) === '...is my na...')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_excerpt('This is my name', 'name', [
						'radius'=>3,
						'omission'=>'(...) '
					]) === '(...) my name')
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
				if(function_exists('mb_strpos')) // from lv_str_title
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
			echo ' -> Testing lv_str_is_match';
				if(lv_str_is_match('/foo (.*)/', 'foo bar'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_is_match('/foo (.*)/', 'laravel'))
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
				if(function_exists('ctype_lower') && function_exists('mb_substr')) // from lv_str_snake
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
			echo ' -> Testing lv_str_is_match';
				if(lv_str_match('/bar/', 'foo bar') === 'bar')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_match('/foo (.*)/', 'foo bar') === 'bar')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_match_all';
				//echo ' ['.var_export_contains(lv_str_match_all('/bar/', 'bar foo bar'), '', true).']';
				if(var_export_contains(
					lv_str_match_all('/bar/', 'bar foo bar'),
					"array(0=>'bar',1=>'bar',)"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				//echo ' ['.var_export_contains(lv_str_match_all('/f(\w*)/', 'bar fun bar fly'), '', true).']';
				if(var_export_contains(
					lv_str_match_all('/f(\w*)/', 'bar fun bar fly'),
					"array(0=>'un',1=>'ly',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_of [LATR]'.PHP_EOL;
			echo ' -> Testing lv_str_pad_both';
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
			echo ' -> Testing lv_str_position';
				if(function_exists('mb_strpos'))
				{
					if(lv_str_position('Hello, World!', 'Hello') === 0)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_position('Hello, World!', 'W') === 7)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_random [LATR]'.PHP_EOL;
			echo ' -> Testing lv_str_remove';
				if(lv_str_remove('e', 'Peter Piper picked a peck of pickled peppers.') === 'Ptr Pipr pickd a pck of pickld ppprs.')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_repeat';
				if(lv_str_repeat('a', 5) === 'aaaaa')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_replace';
				if(lv_str_replace('10.x', '11.x', 'Laravel 10.x') === 'Laravel 11.x')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_replace_array';
				if(lv_str_replace_array('?', ['8:30', '9:00'], 'The event will take place between ? and ?') === 'The event will take place between 8:30 and 9:00')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_replace_end';
				if(lv_str_replace_end('World', 'Laravel', 'Hello World') === 'Hello Laravel')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_replace_end('Hello', 'Laravel', 'Hello World') === 'Hello World')
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
			echo ' -> Testing lv_str_replace_start';
				if(lv_str_replace_start('Hello', 'Laravel', 'Hello World') === 'Laravel World')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_replace_start('World', 'Laravel', 'Hello World') === 'Hello World')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_reverse';
				if(function_exists('mb_substr'))
				{
					if(lv_str_reverse('Hello World') === 'dlroW olleH')
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
				if(
					function_exists('ctype_lower') &&
					function_exists('mb_substr')
				){
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
			echo ' -> Testing lv_str_starts_with';
				if(lv_str_starts_with('This is my name', 'This'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_starts_with('This is my name', 'That'))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				if(lv_str_starts_with('This is my name', ['This', 'That', 'There']))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo ' -> Testing lv_str_str [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_studly';
				if(function_exists('mb_strpos')) // from lv_str_ucfirst
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
			echo ' -> Testing lv_str_unwrap';
				if(function_exists('mb_substr'))
				{
					if(lv_str_unwrap('-Laravel-', '-') === 'Laravel')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_unwrap('{framework: "Laravel"}', '{', '}') === 'framework: "Laravel"')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo ' -> Testing lv_str_upper';
				if(function_exists('mb_strpos'))
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
				if(function_exists('mb_strpos'))
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
		}

		if(!isset($lv_stringable_header))
			$lv_stringable_header='lv_str_ingable';
		echo ' -> Testing '.$lv_stringable_header.PHP_EOL;
			if(!isset($lv_stringable_function))
				$lv_stringable_function='lv_str_of';
			echo '  -> after';
				if($lv_stringable_function('This is my name')->after('This is')->to_string() === ' my name')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> after_last';
				if($lv_stringable_function('App\Http\Controllers\Controller')->after_last('\\')->to_string() === 'Controller')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> apa';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('a nice title uses the correct case')->apa()->to_string() === 'A Nice Title Uses the Correct Case')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> append';
				if($lv_stringable_function('Taylor')->append(' Otwell')->to_string() === 'Taylor Otwell')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> basename';
				if($lv_stringable_function('/foo/bar/baz')->basename()->to_string() === 'baz')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('/foo/bar/baz.jpg')->basename('.jpg')->to_string() === 'baz')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> before';
				if($lv_stringable_function('This is my name')->before('my name')->to_string() === 'This is ')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> before_last';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('This is my name')->before_last('is')->to_string() === 'This ')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> between';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('This is my name')->between('This', 'name')->to_string() === ' is my ')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> between_first';
				if($lv_stringable_function('[a] bc [d]')->between_first('[', ']')->to_string() === 'a')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> camel';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('foo_bar')->camel()->to_string() === 'fooBar')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> char_at';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('This is my name.')->char_at(6) === 's')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> class_basename';
				if($lv_stringable_function('Foo\Bar\Baz')->class_basename()->to_string() === 'Baz')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> contains';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('This is my name')->contains('my'))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('This is my name')->contains(['my', 'foo']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> contains_all';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('This is my name')->contains_all(['my', 'name']))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> dirname';
				if($lv_stringable_function('/foo/bar/baz')->dirname()->to_string() === '/foo/bar')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('/foo/bar/baz')->dirname(2)->to_string() === '/foo')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> ends_with';
				if($lv_stringable_function('This is my name')->ends_with('name'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('This is my name')->ends_with(['name', 'foo']))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('This is my name')->ends_with(['this', 'foo']))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> exactly';
				if($lv_stringable_function('Laravel')->exactly('Laravel'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> excerpt';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('This is my name')->excerpt('name', [
						'radius'=>3,
						'omission'=>'(...) '
					]) === '(...) my name')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('This is my name')->excerpt('my', [
						'radius'=>3
					]) === '...is my na...')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> explode (lv_str_of)';
				//echo ' ['.var_export_contains(lv_str_of('foo bar baz')->explode(' '), '', true).']';
				if(var_export_contains(
					lv_str_of('foo bar baz')->explode(' '),
					"array(0=>'foo',1=>'bar',2=>'baz',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> finish';
				if($lv_stringable_function('this/string')->finish('/')->to_string() === 'this/string/')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('this/string/')->finish('/')->to_string() === 'this/string/')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> from_base64';
				if($lv_stringable_function('TGFyYXZlbA==')->from_base64()->to_string() === 'Laravel')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> headline';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('taylor_otwell')->headline()->to_string() === 'Taylor Otwell')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('EmailNotificationSent')->headline()->to_string() === 'Email Notification Sent')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> is';
				if($lv_stringable_function('foobar')->is('foo*'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('foobar')->is('baz*'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> trim/is_empty';
				if($lv_stringable_function('  ')->trim()->is_empty())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('Laravel')->trim()->is_empty())
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> is_match';
				if($lv_stringable_function('foo bar')->is_match('/foo (.*)/'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('laravel')->is_match('/foo (.*)/'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> trim/is_not_empty';
				if($lv_stringable_function('  ')->trim()->is_not_empty())
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				if($lv_stringable_function('Laravel')->trim()->is_not_empty())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> is_url';
				if($lv_stringable_function('http://example.com')->is_url())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('Taylor')->is_url())
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> kebab';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('fooBar')->kebab()->to_string() === 'foo-bar')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lcfirst';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Foo Bar')->lcfirst()->to_string() === 'foo Bar')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> length';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Laravel')->length() === 7)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> limit';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('The quick brown fox jumps over the lazy dog')->limit(20)->to_string() === 'The quick brown fox...')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('The quick brown fox jumps over the lazy dog')->limit(20, ' (...)')->to_string() === 'The quick brown fox (...)')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lower';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('LARAVEL')->lower()->to_string() === 'laravel')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> ltrim';
				if($lv_stringable_function('  Laravel  ')->ltrim()->to_string() === 'Laravel  ')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('/Laravel/')->ltrim('/')->to_string() === 'Laravel/')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> mask';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('taylor@example.com')->mask('*', 3)->to_string() === 'tay***************')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('taylor@example.com')->mask('*', -15, 3)->to_string() === 'tay***@example.com')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('taylor@example.com')->mask('*', 4, -4)->to_string() === 'tayl**********.com')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> match';
				if($lv_stringable_function('foo bar')->match('/bar/')->to_string() === 'bar')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('foo bar')->match('/foo (.*)/')->to_string() === 'bar')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> match_all (lv_str_of)';
				//echo ' ['.var_export_contains(lv_str_of('bar foo bar')->match_all('/bar/'), '', true).']';
				if(var_export_contains(
					lv_str_of('bar foo bar')->match_all('/bar/'),
					"array(0=>'bar',1=>'bar',)"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				//echo ' ['.var_export_contains(lv_str_of('bar fun bar fly')->match_all('/f(\w*)/'), '', true).']';
				if(var_export_contains(
					lv_str_of('bar fun bar fly')->match_all('/f(\w*)/'),
					"array(0=>'un',1=>'ly',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> new_line/append';
				if($lv_stringable_function('Laravel')->new_line()->append('Framework')->to_string() === 'Laravel'.PHP_EOL.'Framework')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> pad_both';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('James')->pad_both(10, '_')->to_string() === '__James___')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('James')->pad_both(10)->to_string() === '  James   ')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> pad_left';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('James')->pad_left(10, '-=')->to_string() === '-=-=-James')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('James')->pad_left(10)->to_string() === '     James')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> pad_right';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('James')->pad_right(10, '-')->to_string() === 'James-----')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('James')->pad_right(10)->to_string() === 'James     ')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> pipe/prepend';
				if($lv_stringable_function('Laravel')->pipe('md5')->prepend('Checksum: ')->to_string() === 'Checksum: a5c95b86291ea299fcbe64458ed12702')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('foo')->pipe(function($str){
					return 'bar';
				})->to_string() === 'bar')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> position';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Hello, World!')->position('Hello') === 0)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('Hello, World!')->position('W') === 7)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> prepend';
				if($lv_stringable_function('Framework')->prepend('Laravel ')->to_string() === 'Laravel Framework')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> remove';
				if($lv_stringable_function('Arkansas is quite beautiful!')->remove('quite ')->to_string() === 'Arkansas is beautiful!')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> repeat';
				if($lv_stringable_function('a')->repeat(5)->to_string() === 'aaaaa')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace';
				if($lv_stringable_function('Laravel 6.x')->replace('6.x', '7.x')->to_string() === 'Laravel 7.x')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace_array';
				if($lv_stringable_function('The event will take place between ? and ?')->replace_array('?', ['8:30', '9:00'])->to_string() === 'The event will take place between 8:30 and 9:00')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace_end';
				if($lv_stringable_function('Hello World')->replace_end('World', 'Laravel')->to_string() === 'Hello Laravel')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('Hello World')->replace_end('Hello', 'Laravel')->to_string() === 'Hello World')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace_first';
				if($lv_stringable_function('the quick brown fox jumps over the lazy dog')->replace_first('the', 'a')->to_string() === 'a quick brown fox jumps over the lazy dog')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace_last';
				if($lv_stringable_function('the quick brown fox jumps over the lazy dog')->replace_last('the', 'a')->to_string() === 'the quick brown fox jumps over a lazy dog')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace_matches';
				if($lv_stringable_function('(+1) 501-555-1000')->replace_matches('/[^A-Za-z0-9]++/', '')->to_string() === '15015551000')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('123')->replace_matches('/\d/', function(array $matches){
					return '['.$matches[0].']';
				})->to_string() === '[1][2][3]')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> replace_start';
				if($lv_stringable_function('Hello World')->replace_start('Hello', 'Laravel')->to_string() === 'Laravel World')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('Hello World')->replace_start('World', 'Laravel')->to_string() === 'Hello World')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> reverse';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('Hello World')->reverse()->to_string() === 'dlroW olleH')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> rtrim';
				if($lv_stringable_function('  Laravel  ')->rtrim()->to_string() === '  Laravel')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('/Laravel/')->rtrim('/')->to_string() === '/Laravel')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> scan (lv_str_of)';
				//echo ' ['.var_export_contains(lv_str_of('filename.jpg')->scan('%[^.].%s'), '', true).']';
				if(var_export_contains(
					lv_str_of('filename.jpg')->scan('%[^.].%s'),
					"array(0=>'filename',1=>'jpg',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> snake';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('fooBar')->snake()->to_string() === 'foo_bar')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> split (lv_str_of)';
				if(function_exists('mb_substr') && function_exists('mb_str_split'))
				{
					//echo ' ['.var_export_contains(lv_str_of('one, two, three')->split('/[\s,]+/'), '', true).']';
					if(var_export_contains(
						lv_str_of('one, two, three')->split('/[\s,]+/'),
						"array(0=>'one',1=>'two',2=>'three',)"
					))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> squish';
				if($lv_stringable_function('    laravel    framework    ')->squish()->to_string() === 'laravel framework')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> start';
				if($lv_stringable_function('this/string')->start('/')->to_string() === '/this/string')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('/this/string')->start('/')->to_string() === '/this/string')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> starts_with';
				if($lv_stringable_function('This is my name')->starts_with('This'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> strip_tags';
				if($lv_stringable_function('<a href="https://laravel.com">Taylor <b>Otwell</b></a>')->strip_tags()->to_string() === 'Taylor Otwell')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('<a href="https://laravel.com">Taylor <b>Otwell</b></a>')->strip_tags('<b>')->to_string() === 'Taylor <b>Otwell</b>')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> studly';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('foo_bar')->studly()->to_string() === 'FooBar')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> substr';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Laravel Framework')->substr(8)->to_string() === 'Framework')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('Laravel Framework')->substr(8, 5)->to_string() === 'Frame')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> substr_count';
				if($lv_stringable_function('If you like ice cream, you will like snow cones.')->substr_count('like') === 2)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> substr_replace';
				if($lv_stringable_function('1300')->substr_replace(':', 2)->to_string() === '13:')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('The Framework')->substr_replace(' Laravel', 3, 0)->to_string() === 'The Laravel Framework')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> swap';
				if($lv_stringable_function('Tacos are great!')->swap([
					'Tacos'=>'Burritos',
					'great'=>'fantastic'
				])->to_string() === 'Burritos are fantastic!')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> take';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Build something amazing!')->take(5)->to_string() === 'Build')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> append/tap/upper';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Laravel')->append(' Framework')->tap(function($string) use(&$failed){
						if($string->to_string() === 'Laravel Framework')
							echo ' [ OK ]';
						else
						{
							echo ' [FAIL]';
							$failed=true;
						}
					})->upper()->to_string() === 'LARAVEL FRAMEWORK')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> test';
				if($lv_stringable_function('Laravel Framework')->test('/Laravel/'))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> title';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('a nice title uses the correct case')->title()->to_string() === 'A Nice Title Uses The Correct Case')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> title';
				if($lv_stringable_function('Laravel')->to_base64()->to_string() === 'TGFyYXZlbA==')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> to_boolean [SKIP]'.PHP_EOL;
			echo '  -> to_float [SKIP]'.PHP_EOL;
			echo '  -> to_integer [SKIP]'.PHP_EOL;
			echo '  -> to_string [WAS]'.PHP_EOL;
			echo '  -> trim';
				if($lv_stringable_function('  Laravel  ')->trim()->to_string() === 'Laravel')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('/Laravel/')->trim('/')->to_string() === 'Laravel')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> ucfirst';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('foo bar')->ucfirst()->to_string() === 'Foo bar')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> ucsplit (lv_str_of)';
				//echo ' ['.var_export_contains(lv_str_of('Foo Bar')->ucsplit(), '', true).']';
				if(var_export_contains(
					lv_str_of('Foo Bar')->ucsplit(),
					"array(0=>'Foo',1=>'Bar',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> unless [SKIP]'.PHP_EOL;
			echo '  -> unwrap';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('-Laravel-')->unwrap('-')->to_string() === 'Laravel')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('{framework: "Laravel"}')->unwrap('{', '}')->to_string() === 'framework: "Laravel"')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> upper';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('laravel')->upper()->to_string() === 'LARAVEL')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> value [SKIP]'.PHP_EOL;
			echo '  -> when/append';
				if($lv_stringable_function('Taylor')->when(true, function($string){
					return $string->append(' Otwell');
				})->to_string() === 'Taylor Otwell')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> when_empty/trim/prepend';
				if($lv_stringable_function('  ')->trim()->when_empty(function($string){
					return $string->prepend('Laravel');
				})->to_string() === 'Laravel')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> when_contains/title';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('tony stark')->when_contains('tony', function($string){
						return $string->title();
					})->to_string() === 'Tony Stark')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if($lv_stringable_function('tony stark')->when_contains(['tony', 'hulk'], function(lv_str_ingable $string){
						return $string->title();
					})->to_string() === 'Tony Stark')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_contains_all/title';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('tony stark')->when_contains_all(['tony', 'stark'], function(lv_str_ingable $string){
						return $string->title();
					})->to_string() === 'Tony Stark')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_ends_with/title';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('disney world')->when_ends_with('world', function(lv_str_ingable $string){
						return $string->title();
					})->to_string() === 'Disney World')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_exactly/title';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('laravel')->when_exactly('laravel', function($string){
						return $string->title();
					})->to_string() === 'Laravel')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_is/append';
				if($lv_stringable_function('foo/bar')->when_is('foo/*', function($string){
					return $string->append('/baz');
				})->to_string() === 'foo/bar/baz')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> when_not_empty/prepend';
				if($lv_stringable_function('Framework')->when_not_empty(function($string){
					return $string->prepend('Laravel ');
				})->to_string() === 'Laravel Framework')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> when_not_exactly/title';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('framework')->when_not_exactly('laravel', function($string){
						return $string->title();
					})->to_string() === 'Framework')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_starts_with/title';
				if(function_exists('mb_substr'))
				{
					if($lv_stringable_function('disney world')->when_starts_with('disney', function(lv_str_ingable $string){
						return $string->title();
					})->to_string() === 'Disney World')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_test/title';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('laravel framework')->when_test('/laravel/', function($string){
						return $string->title();
					})->to_string() === 'Laravel Framework')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> word_count';
				if($lv_stringable_function('Hello, world!')->word_count() === 2)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> word_wrap';
				if($lv_stringable_function('The quick brown fox jumped over the lazy dog.')->word_wrap(20, "<br />")->to_string() === 'The quick brown fox<br />jumped over the lazy<br />dog.')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> words';
				if(function_exists('mb_strpos'))
				{
					if($lv_stringable_function('Perfectly balanced, as all things should be.')->words(3, ' >>>')->to_string() === 'Perfectly balanced, as >>>')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> wrap';
				if($lv_stringable_function('Laravel')->wrap('"')->to_string() === '"Laravel"')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if($lv_stringable_function('is')->wrap('This ', ' Laravel!')->to_string() === 'This is Laravel!')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
	}
	namespace Test
	{
		if(isset($lv_helpers_skip) && ($lv_helpers_skip === true))
			echo ' -> Testing lv_str_random [SKIP]'.PHP_EOL;
		else
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

			foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
			{
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

			echo ' -> Mocking functions';
				interface ArrayAccess extends \ArrayAccess {}
				interface JsonSerializable extends \JsonSerializable {}
				class Exception extends \Exception {}
				function random_bytes()
				{
					return base64_decode('dZlYfCxWHWo=');
				}
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

			echo ' -> Testing lv_str_random';
				if(lv_str_random(8) === 'dZlYfCxW')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
		}

		if($failed)
			exit(1);
	}
?>