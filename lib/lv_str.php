<?php
	/*
	 * Laravel 10 string helpers
	 *
	 * Note:
	 *  throws an lv_str_exception on error
	 *
	 * Implemented functions:
	 *  class_basename() lv_str_class_basename()
	 *   returns the class name of the given class
	 *   with the class's namespace removed
			$class=class_basename('Foo\Bar\Baz'); // 'Baz'
			$class=class_basename(new Foo\Bar\Baz()); // 'Baz'
	 *  preg_replace_array() lv_str_preg_replace_array()
	 *   replaces a given pattern in the string sequentially using an array
			$string='The event will take place between :start and :end';
			$replaced=preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], $string);
			// The event will take place between 8:30 and 9:00
	 *  lv_str_after()
	 *   returns everything after the given value in a string
	 *   the entire string will be returned
	 *   if the value does not exist within the string
			$slice=lv_str_after('This is my name', 'This is');
			// ' my name'
	 *  lv_str_after_last()
	 *   returns everything after the last occurrence
	 *   of the given value in a string
	 *   the entire string will be returned
	 *   if the value does not exist within the string
			$slice=lv_str_after_last('App\Http\Controllers\Controller', '\\');
			// 'Controller'
	 *  lv_str_apa()
	 *   converts the given string to title case
	 *   following the APA guidelines (https://apastyle.apa.org/style-grammar-guidelines/capitalization/title-case)
			$converted=lv_str_apa('a nice title uses the correct case');
			// A Nice Title Uses the Correct Case
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_before()
	 *   returns everything before the given value in a string
			$slice=lv_str_before('This is my name', 'my name');
			// 'This is '
	 *  lv_str_before_last()
	 *   returns everything before the last occurrence
	 *   of the given value in a string
			$slice=lv_str_before_last('This is my name', 'is');
			// 'This '
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_between()
	 *   returns the portion of a string between two values
			$slice=lv_str_between('This is my name', 'This', 'name');
			// ' is my '
	 *   warning:
	 *    lv_str_after function is required
	 *    lv_str_before_last function is required
	 *  lv_str_between_first()
	 *   returns the smallest possible portion of a string between two values
			$slice=lv_str_between_first('[a] bc [d]', '[', ']');
			// 'a'
	 *   warning:
	 *    lv_str_after function is required
	 *    lv_str_before function is required
	 *  lv_str_camel(string_value)
	 *   converts the given string to camelCase
			$converted=lv_str_camel('foo_bar');
			// fooBar
	 *   warning:
	 *    lv_str_studly function is required
	 *  lv_str_char_at()
	 *   returns the character at the specified index
	 *   if the index is out of bounds, false is returned
			$character=lv_str_char_at('This is my name.', 6);
			// 's'
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_contains()
	 *   determines if the given string contains the given value
			$contains=lv_str_contains('This is my name', 'my');
			// true
	 *   you may also pass an array of values to determine
	 *   if the given string contains any of the values in the array
			$contains=lv_str_contains('This is my name', ['my', 'foo']);
			// true
	 *   note:
	 *    this method is case sensitive
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_contains_all()
	 *   determines if the given string contains all
	 *   of the values in a given array
			$contains_all=lv_str_contains_all('This is my name', ['my', 'name']);
			// true
	 *   warning:
	 *    lv_str_contains function is required
	 *  lv_str_ends_with()
	 *   determines if the given string ends with the given value
			$result=lv_str_ends_with('This is my name', 'name');
			// true
	 *   you may also pass an array of values to determine
	 *   if the given string ends with any of the values in the array
			$result=lv_str_ends_with('This is my name', ['name', 'foo']); // true
			$result=lv_str_ends_with('This is my name', ['this', 'foo']); // false
	 *  lv_str_excerpt()
	 *   extracts an excerpt from the string
	 *   that matches the first instance of a phrase within that string
			$excerpt=lv_str_excerpt('This is my name', 'my', [
				'radius'=>3
			]);
			// '...is my na...'
	 *   the radius option, which defaults to 100
	 *   allows you to define the number of characters
	 *   that should appear on each side of the truncated string
	 *   in addition, you may use the omission option to change
	 *   the string that will be prepended
	 *   and appended to the truncated string
			$excerpt=lv_str_excerpt('This is my name', 'name', [
				'radius'=>3,
				'omission'=>'(...) '
			]);
			// '(...) my name'
	 *   warning:
	 *    mbstring extension is required
	 *    lv_str_str function is required
	 *  lv_str_finish(string_value, string_cap)
	 *   adds a single instance of the given value to a string
	 *   if it does not already end with that value
			$adjusted=lv_str_finish('this/string', '/'); // this/string/
			$adjusted=lv_str_finish('this/string/', '/'); // this/string/
	 *  lv_str_headline(string_value)
	 *   convert strings delimited by casing, hyphens, or underscores
	 *   into a space delimited string with each word's first letter capitalized
			$headline=lv_str_headline('steve_jobs');
			// Steve Jobs
			$headline=lv_str_headline('EmailNotificationSent');
			// Email Notification Sent
	 *   warning:
	 *    lv_str_title function is required
	 *    lv_str_ucsplit function is required
	 *  lv_str_is(string_pattern, string_value)
	 *   determines if a given string matches a given pattern
	 *   asterisks may be used as wildcard values
			$matches=lv_str_is('foo*', 'foobar'); // true
			$matches=lv_str_is('baz*', 'foobar'); // false
	 *  lv_str_is_url(string_value, array_protocols)
	 *   determines if the given string is a valid URL
			$is_url=lv_str_is_url('http://example.com'); // true
			$is_url=lv_str_is_url('laravel'); // false
	 *  lv_str_is_match()
	 *   returns true if the string matches a given regular expression
			$result=lv_str_is_match('/foo (.*)/', 'foo bar'); // true
			$result=lv_str_is_match('/foo (.*)/', 'laravel'); // false
	 *  lv_str_kebab(string_value)
	 *   converts the given string to kebab-case
			$converted=lv_str_kebab('fooBar'); // foo-bar
	 *   warning:
	 *    lv_str_snake function is required
	 *  lv_str_lcfirst()
	 *   returns the given string with the first character lowercased
			$string=lv_str_lcfirst('Foo Bar'); // foo Bar
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_length()
	 *   returns the length of the given string
			$length=lv_str_length('Laravel'); // 7
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_limit()
	 *   truncates the given string to the specified length
			$truncated=lv_str_limit('The quick brown fox jumps over the lazy dog', 20);
			// The quick brown fox...
	 *   you may pass a third argument to the method
	 *   to change the string that will be appended
	 *   to the end of the truncated string
			$truncated=lv_str_limit('The quick brown fox jumps over the lazy dog', 20, ' (...)');
			// The quick brown fox (...)
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_lower()
	 *   converts the given string to lowercase
			$converted=lv_str_lower('LARAVEL'); // laravel
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_mask(string_string, string_character, int_index, int_length=null, string_encoding='UTF-8')
	 *   masks a portion of a string with a repeated character,
	 *   and may be used to obfuscate segments of strings
	 *   such as email addresses and phone numbers
			$string=lv_str_mask('taylor@example.com', '*', 3);
			// tay***************
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_match()
	 *   returns the portion of a string
	 *     that matches a given regular expression pattern
			$result=lv_str_match('/bar/', 'foo bar'); // 'bar'
			$result=lv_str_match('/foo (.*)/', 'foo bar'); // 'bar'
	 *   lv_str_match_all()
	 *    returns a collection containing the portions of a string
	 *    that match a given regular expression pattern
			$result=lv_str_match_all('/bar/', 'bar foo bar');
			// ['bar', 'bar']
	 *    if you specify a matching group within the expression
	 *    lv_str_match_all will return an array of that group's matches
			$result=lv_str_match_all('/f(\w*)/', 'bar fun bar fly');
			// ['un', 'ly']
	 *  lv_str_of()
	 *   get a new stringable object from the given string
	 *   see the lv_str_ingable class for more info
	 *   warning:
	 *    lv_str_ingable class is required
	 *  lv_str_pad_both()
	 *   wraps PHP's str_pad function, padding both sides
	 *   of a string with another string
	 *   until the final string reaches a desired length
			$padded=lv_str_pad_both('James', 10, '_'); // '__James___'
			$padded=lv_str_pad_both('James', 10); // '  James   '
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_pad_left()
	 *   wraps PHP's str_pad function, padding the left side
	 *   of a string with another string
	 *   until the final string reaches a desired length
			$padded=lv_str_pad_left('James', 10, '-='); // '-=-=-James'
			$padded=lv_str_pad_left('James', 10); // '     James'
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_pad_right()
	 *   wraps PHP's str_pad function, padding the right side
	 *   of a string with another string
	 *   until the final string reaches a desired length
			$padded=lv_str_pad_right('James', 10, '-'); // 'James-----'
			$padded=lv_str_pad_right('James', 10); // 'James     '
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_position()
	 *   returns the position of the first occurrence
	 *   of a substring in a string
	 *   if the substring does not exist within the string
	 *   false is returned
			$position=lv_str_position('Hello, World!', 'Hello'); // 0
			$position=lv_str_position('Hello, World!', 'W'); // 7
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_random()
	 *   generates a random string of the specified length
	 *   this function uses PHP's random_bytes function
			$random=lv_str_random(40);
	 *  lv_str_remove()
	 *   removes the given value or array of values from the string
			$string='Peter Piper picked a peck of pickled peppers.';
			$removed=lv_str_remove('e', $string);
			// Ptr Pipr pickd a pck of pickld ppprs.
	 *  lv_str_repeat()
	 *   repeats the given string
			$string='a';
			$repeat=lv_str_repeat($string, 5); // aaaaa
	 *  lv_str_replace()
	 *   replaces a given string within the string
			$string='Laravel 10.x';
			$replaced=lv_str_replace('10.x', '11.x', $string);
			// Laravel 11.x
	 *   the lv_str_replace function also accepts
	 *   a case_sensitive argument
	 *   by default, the lv_str_replace function is case sensitive
			lv_str_replace('Framework', 'Laravel', $string, false);
	 *  lv_str_replace_array()
	 *   replaces a given value in the string sequentially using an array
			$string='The event will take place between ? and ?';
			$replaced=lv_str_replace_array('?', ['8:30', '9:00'], $string);
			// The event will take place between 8:30 and 9:00
	 *  lv_str_replace_end()
	 *   replaces the last occurrence of the given value only
	 *   if the value appears at the end of the string
			$replaced=lv_str_replace_end('World', 'Laravel', 'Hello World'); // Hello Laravel
			$replaced=lv_str_replace_end('Hello', 'Laravel', 'Hello World'); // Hello World
	 *   warning:
	 *    lv_str_replace_last function is required
	 *    lv_str_starts_with function is required
	 *  lv_str_replace_first()
	 *   replaces the first occurrence of a given value in a string
			$replaced=lv_str_replace_first('the', 'a', 'the quick brown fox jumps over the lazy dog');
			// a quick brown fox jumps over the lazy dog
	 *  lv_str_replace_last()
	 *   replaces the last occurrence of a given value in a string
			$replaced=lv_str_replace_last('the', 'a', 'the quick brown fox jumps over the lazy dog');
			// the quick brown fox jumps over a lazy dog
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_replace_start()
	 *   replaces the first occurrence of the given value only
	 *   if the value appears at the start of the string
			$replaced=lv_str_replace_start('Hello', 'Laravel', 'Hello World'); // Laravel World
			$replaced=lv_str_replace_start('World', 'Laravel', 'Hello World'); // Hello World
	 *   warning:
	 *    lv_str_replace_first function is required
	 *    lv_str_starts_with function is required
	 *  lv_str_reverse()
	 *   reverses the given string
			$reversed=lv_str_reverse('Hello World');
			// dlroW olleH
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_snake(string_valuem string_delimiter)
	 *   converts the given string to snake_case
			$converted=lv_str_snake('fooBar'); // foo_bar
			$converted=lv_str_snake('fooBar', '-'); // foo-bar
	 *   warning:
	 *    ctype extension is required
	 *    mbstring extension is required
	 *  lv_str_starts_with()
	 *   determines if the given string begins with the given value
			$result=lv_str_starts_with('This is my name', 'This');
			// true
	 *   if an array of possible values is passed
	 *   the lv_str_starts_with function will return true
	 *   if the string begins with any of the given values
			$result=lv_str_starts_with('This is my name', ['This', 'That', 'There']);
			// true
	 *  lv_str_squish(string_value)
	 *   removes all extraneous white space from a string,
	 *   including extraneous white space between words
			$string=lv_str_squish('    laravel    framework    ');
			// laravel framework
	 *  lv_str_start(string_value, string_prefix)
	 *   adds a single instance of the given value to a string
	 *   if it does not already start with that value
			$adjusted=lv_str_start('this/string', '/');
			// /this/string
			$adjusted=lv_str_start('/this/string', '/');
			// /this/string
	 *  lv_str_str()
	 *   get a new stringable object from the given string
	 *   warning:
	 *    lv_str_of function is required
	 *  lv_str_studly(string_value)
	 *   converts the given string to StudlyCase
			$converted=lv_str_studly('foo_bar'); // FooBar
	 *   warning:
	 *    lv_str_ucfirst function is required
	 *  lv_str_substr()
	 *   returns the portion of string
	 *   specified by the start and length parameters
			$converted=lv_str_substr('The Laravel Framework', 4, 7);
			// Laravel
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_substr_count()
	 *   returns the number of occurrences
	 *   of a given value in the given string
			$count=lv_str_substr_count('If you like ice cream, you will like snow cones.', 'like');
			// 2
	 *  lv_str_substr_replace()
	 *   replaces text within a portion of a string
	 *   starting at the position specified by the third argument
	 *   and replacing the number of characters specified
	 *   by the fourth argument
	 *   passing 0 to the method's fourth argument will insert
	 *   the string at the specified position without replacing
	 *   any of the existing characters in the string
			$result=lv_str_substr_replace('1300', ':', 2); // 13:
			$result=lv_str_ssubstr_replace('1300', ':', 2, 0); // 13:00
	 *  lv_str_swap()
	 *   replaces multiple values in the given string
	 *   using PHP's strtr function
			$string=lv_str_swap([
				'Tacos'=>'Burritos',
				'great'=>'fantastic'
			], 'Tacos are great!');
			// Burritos are fantastic!
	 *  lv_str_title(string_value)
	 *   converts the given string to Title Case
			$converted=lv_str_title('a nice title uses the correct case');
			// A Nice Title Uses The Correct Case
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_ucfirst(string_string)
	 *   returns the given string with the first character capitalized
			$string=lv_str_ucfirst('foo bar'); // Foo bar
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_ucsplit(string_string)
	 *   splits the given string into an array by uppercase characters
			$segments=lv_str_ucsplit('FooBar');
			// [0=>'Foo', 1=>'Bar']
	 *  lv_str_unwrap()
	 *   removes the specified strings from the beginning
	 *   and end of a given string
			$string=lv_str_unwrap('-Laravel-', '-'); // Laravel
			$string=lv_str_unwrap('{framework: "Laravel"}', '{', '}'); // framework: "Laravel"
	 *   warning:
	 *    lv_str_length function is required
	 *    lv_str_starts_with function is required
	 *    lv_str_substr function is required
	 *  lv_str_upper()
	 *   converts the given string to uppercase
			$string=lv_str_upper('laravel'); // LARAVEL
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_word_count()
	 *   returns the number of words that a string contains
			lv_str_word_count('Hello, world!'); // 2
	 *  lv_str_word_wrap()
	 *   wraps a string to a given number of characters
			$text='The quick brown fox jumped over the lazy dog.';
			$string=lv_str_word_wrap($text, 20, "<br />\n");
			// The quick brown fox<br />
			// jumped over the lazy<br />
			// dog.
	 *  lv_str_words()
	 *   limits the number of words in a string
	 *   an additional string may be passed to this method
	 *   via its third argument to specify which string
	 *   should be appended to the end of the truncated string
			return lv_str_words('Perfectly balanced, as all things should be.', 3, ' >>>');
			// Perfectly balanced, as >>>
	 *   warning:
	 *    mbstring extension is required
	 *  lv_str_wrap()
	 *   wraps the given string with an additional string or pair of strings
			$string=lv_str_wrap('Laravel', '"'); // "Laravel"
			$string=lv_str_wrap('is', 'This ', ' Laravel!'); // This is Laravel!
	 *
	 * Implemented classes:
	 *  lv_str_ingable
	 *   implemented methods:
	 *    after()
	 *     returns everything after the given value in a string
	 *     the entire string will be returned
	 *     if the value does not exist within the string
			$slice=lv_str_of('This is my name')->after('This is');
			// ' my name'
	 *     warning:
	 *      lv_str_after function is required
	 *    after_last()
	 *     returns everything after the last occurrence
	 *     of the given value in a string
	 *     the entire string will be returned
	 *     if the value does not exist within the string
			$slice=lv_str_of('App\Http\Controllers\Controller')->after_last('\\');
			// 'Controller'
	 *     warning:
	 *      lv_str_after_last function is required
	 *    apa()
	 *     converts the given string to title case
	 *     following the APA guidelines
			$converted=lv_str_of('a nice title uses the correct case')->apa();
			// A Nice Title Uses the Correct Case
	 *     warning:
	 *      lv_str_apa function is required
	 *    append()
	 *     appends the given values to the string
			$string=lv_str_of('Taylor')->append(' Otwell');
			// 'Taylor Otwell'
	 *    basename()
	 *     returns the trailing name component of the given string
			$string=lv_str_of('/foo/bar/baz')->basename();
			// 'baz'
	 *     if needed, you may provide an "extension"
	 *     that will be removed from the trailing component
			$string=lv_str_of('/foo/bar/baz.jpg')->basename('.jpg');
			// 'baz'
	 *    before()
	 *     returns everything before the given value in a string
			$slice=lv_str_of('This is my name')->before('my name');
			// 'This is '
	 *     warning:
	 *      lv_str_before function is required
	 *    before_last()
	 *     returns everything before the last occurrence
	 *     of the given value in a string
			$slice=lv_str_of('This is my name')->before_last('is');
			// 'This '
	 *     warning:
	 *      lv_str_before_last function is required
	 *    between()
	 *     returns the portion of a string between two values
			$converted=lv_str_of('This is my name')->between('This', 'name');
			// ' is my '
	 *     warning:
	 *      lv_str_between function is required
	 *    between_first()
	 *     returns the smallest possible portion
	 *     of a string between two values
			$converted=lv_str_of('[a] bc [d]')->between_first('[', ']');
			// 'a'
	 *     warning:
	 *      lv_str_between_first function is required
	 *    camel()
	 *     converts the given string to camelCase
			$converted=lv_str_of('foo_bar')->camel();
			// 'fooBar'
	 *     warning:
	 *      lv_str_camel function is required
	 *    char_at()
	 *     returns the character at the specified index
	 *     if the index is out of bounds, false is returned
			$character=lv_str_of('This is my name.')->char_at(6);
			// 's'
	 *     warning:
	 *      lv_str_char_at function is required
	 *    class_basename()
	 *     returns the class name of the given class
	 *     with the class's namespace removed
			$class=lv_str_of('Foo\Bar\Baz')->class_basename();
			// 'Baz'
	 *     warning:
	 *      lv_str_class_basename function is required
	 *    contains()
	 *     determines if the given string contains the given value
			$contains=lv_str_of('This is my name')->contains('my');
			// true
	 *     you may also pass an array of values to determine
	 *     if the given string contains any of the values in the array
			$contains=lv_str_of('This is my name')->contains(['my', 'foo']);
			// true
	 *     note:
	 *      this method is case sensitive
	 *     warning:
	 *      lv_str_contains function is required
	 *    contains_all()
	 *     determines if the given string
	 *     contains all of the values in the given array
			$contains_all=lv_str_of('This is my name')->contains_all(['my', 'name']);
			// true
	 *     warning:
	 *      lv_str_contains_all function is required
	 *    dirname()
	 *     returns the parent directory portion of the given string
			$string=lv_str_of('/foo/bar/baz')->dirname();
			// '/foo/bar'
	 *     if necessary, you may specify how many directory levels
	 *     you wish to trim from the string
			$string=lv_str_of('/foo/bar/baz')->dirname(2);
			// '/foo'
	 *    ends_with()
	 *     determines if the given string ends with the given value
			$result=lv_str_of('This is my name')->ends_with('name');
			// true
	 *     you may also pass an array of values to determine
	 *     if the given string ends with any of the values in the array
			$result=lv_str_of('This is my name')->ends_with(['name', 'foo']); // true
			$result=lv_str_of('This is my name')->ends_with(['this', 'foo']); // false
	 *     warning:
	 *      lv_str_ends_with function is required
	 *    exactly()
	 *     determines if the given string
	 *     is an exact match with another string
			$result=lv_str_of('Laravel')->exactly('Laravel');
			// true
	 *    excerpt()
	 *     extracts an excerpt from the string
	 *     that matches the first instance of a phrase within that string
			$excerpt=lv_str_of('This is my name')->excerpt('my', [
				'radius'=>3
			]);
			// '...is my na...'
	 *     the radius option, which defaults to 100
	 *     allows you to define the number of characters
	 *     that should appear on each side of the truncated string
	 *     in addition, you may use the omission option to change
	 *     the string that will be prepended
	 *     and appended to the truncated string
			$excerpt=lv_str_of('This is my name')->excerpt('name', [
				'radius'=>3,
				'omission'=>'(...) '
			]);
			// '(...) my name'
	 *     warning:
	 *      lv_str_excerpt function is required
	 *    explode()
	 *     splits the string by the given delimiter
	 *     and returns an array containing each section of the split string
			$array=lv_str_of('foo bar baz')->explode(' ');
			// ['foo', 'bar', 'baz']
	 *    finish()
	 *     adds a single instance of the given value to a string
	 *     if it does not already end with that value
			$adjusted=lv_str_of('this/string')->finish('/'); // this/string/
			$adjusted=lv_str_of('this/string/')->finish('/'); // this/string/
	 *     warning:
	 *      lv_str_finish function is required
	 *    from_base64()
	 *     converts the given string from Base64
			$base64=lv_str_of('TGFyYXZlbA==')->from_base64();
			// Laravel
	 *    headline()
	 *     converts strings delimited by casing, hyphens, or underscores
	 *     into a space delimited string
	 *     with each word's first letter capitalized
			$headline=lv_str_of('taylor_otwell')->headline(); // Taylor Otwell
			$headline=lv_str_of('EmailNotificationSent')->headline(); // Email Notification Sent
	 *     warning:
	 *      lv_str_headline function is required
	 *    is()
	 *     determines if a given string matches a given pattern
	 *     asterisks may be used as wildcard values
			$matches=lv_str_of('foobar')->is('foo*'); // true
			$matches=lv_str_of('foobar')->is('baz*'); // false
	 *     warning:
	 *      lv_str_is function is required
	 *    is_empty()
	 *     determines if the given string is empty
			$result=lv_str_of('  ')->trim()->is_empty(); // true
			$result=lv_str_of('Laravel')->trim()->is_empty(); // false
	 *    is_match()
	 *     returns true if the string matches a given regular expression
			$result=lv_str_of('foo bar')->is_match('/foo (.*)/'); // true
			$result=lv_str_of('laravel')->is_match('/foo (.*)/'); // false
	 *     warning:
	 *      lv_str_is_match function is required
	 *    is_not_empty()
	 *     determines if the given string is not empty
			$result=lv_str_of('  ')->trim()->is_not_empty(); // false
			$result=lv_str_of('Laravel')->trim()->is_not_empty(); // true
	 *    is_url()
	 *     determines if a given string is a URL
			$result=lv_str_of('http://example.com')->is_url(); // true
			$result=lv_str_of('Taylor')->is_url(); // false
	 *     the is_url method considers a wide range of protocols as valid
	 *     however, you may specify the protocols that should be considered valid
	 *     by providing them to the is_url method
			$result=lv_str_of('http://example.com')->is_url(['http', 'https']);
	 *     warning:
	 *      lv_str_is_url function is required
	 *    kebab()
	 *     converts the given string to kebab-case
			$converted=lv_str_of('fooBar')->kebab();
			// foo-bar
	 *     warning:
	 *      lv_str_kebab function is required
	 *    lcfirst()
	 *     returns the given string with the first character lowercased
			$string=lv_str_of('Foo Bar')->lcfirst();
			// foo Bar
	 *     warning:
	 *      lv_str_lcfirst function is required
	 *    length()
	 *     returns the length of the given string
			$length=lv_str_of('Laravel')->length();
			// 7
	 *     warning:
	 *      lv_str_length function is required
	 *    limit()
	 *     truncates the given string to the specified length
			$truncated=lv_str_of('The quick brown fox jumps over the lazy dog')->limit(20);
			// The quick brown fox...
	 *     you may also pass a second argument
	 *     to change the string that will be appended to the end
	 *     of the truncated string
			$truncated=lv_str_of('The quick brown fox jumps over the lazy dog')->limit(20, ' (...)');
			// The quick brown fox (...)
	 *     warning:
	 *      lv_str_limit function is required
	 *    lower()
	 *     converts the given string to lowercase
			$result=lv_str_of('LARAVEL')->lower();
			// 'laravel'
	 *     warning:
	 *      lv_str_lower function is required
	 *    ltrim()
	 *     trims the left side of the string
			$string=lv_str_of('  Laravel  ')->ltrim(); // 'Laravel  '
			$string=lv_str_of('/Laravel/')->ltrim('/'); // 'Laravel/'
	 *    mask()
	 *     masks a portion of a string with a repeated character
	 *     and may be used to obfuscate segments of strings
	 *     such as email addresses and phone numbers
			$string=lv_str_of('taylor@example.com')->mask('*', 3);
			// tay***************
	 *     if needed, you may provide negative numbers
	 *     as the third or fourth argument to the mask method
	 *     which will instruct the method to begin masking
	 *     at the given distance from the end of the string
			$string=lv_str_of('taylor@example.com')->mask('*', -15, 3); // tay***@example.com
			$string=lv_str_of('taylor@example.com')->mask('*', 4, -4); // tayl**********.com
	 *     warning:
	 *      lv_str_mask function is required
	 *    match()
	 *     returns the portion of a string
	 *     that matches a given regular expression pattern
			$result=lv_str_of('foo bar')->match('/bar/'); // 'bar'
			$result=lv_str_of('foo bar')->match('/foo (.*)/'); // 'bar'
	 *     warning:
	 *      lv_str_match function is required
	 *    match_all()
	 *     returns an array containing the portions of a string
	 *     that match a given regular expression pattern
			$result=lv_str_of('bar foo bar')->match_all('/bar/');
			// ['bar', 'bar']
	 *     if you specify a matching group within the expression
	 *     match_all will return an array of that group's matches
			$result=lv_str_of('bar fun bar fly')->match_all('/f(\w*)/');
			// ['un', 'ly']
	 *    new_line()
	 *     appends an "end of line" character to a string
			$padded=lv_str_of('Laravel')->new_line()->append('Framework');
			// 'Laravel
			// Framework'
	 *     warning:
	 *      append method is required
	 *    pad_both()
	 *     wraps PHP's str_pad function, padding both sides
	 *     of a string with another string until the final string
	 *     reaches the desired length
			$padded=lv_str_of('James')->pad_both(10, '_'); // '__James___'
			$padded=lv_str_of('James')->pad_both(10); // '  James   '
	 *     warning:
	 *      lv_str_pad_both function is required
	 *    pad_left()
	 *     wraps PHP's str_pad function, padding the left side
	 *     of a string with another string until the final string
	 *     reaches the desired length
			$padded=lv_str_of('James')->pad_left(10, '-='); // '-=-=-James'
			$padded=lv_str_of('James')->pad_left(10); // '     James'
	 *     warning:
	 *      lv_str_pad_left function is required
	 *    pad_right()
	 *     wraps PHP's str_pad function, padding the right side
	 *     of a string with another string until the final string
	 *     reaches the desired length
			$padded=lv_str_of('James')->pad_right(10, '-'); // 'James-----'
			$padded=lv_str_of('James')->pad_right(10); // 'James     '
	 *     warning:
	 *      lv_str_pad_right function is required
	 *    pipe()
	 *     allows you to transform the string
	 *     by passing its current value to the given callable
			$hash=lv_str_of('Laravel')->pipe('md5')->prepend('Checksum: ');
			// 'Checksum: a5c95b86291ea299fcbe64458ed12702'
			$closure=lv_str_of('foo')->pipe(function(lv_str_ingable $str){
				return 'bar';
			});
			// 'bar'
	 *    position()
	 *     returns the position of the first occurrence
	 *     of a substring in a string
	 *     if the substring does not exist within the string
	 *     false is returned
			$position=lv_str_of('Hello, World!')->position('Hello'); // 0
			$position=lv_str_of('Hello, World!')->position('W'); // 7
	 *     warning:
	 *      lv_str_position function is required
	 *    prepend()
	 *     prepends the given values onto the string
			$string=lv_str_of('Framework')->prepend('Laravel ');
			// Laravel Framework
	 *    remove()
	 *     removes the given value or array of values from the string
			$string=lv_str_of('Arkansas is quite beautiful!')->remove('quite ');
			// Arkansas is beautiful!
	 *     you may also pass false as a second parameter
	 *     to ignore case when removing strings
	 *     warning:
	 *      lv_str_remove function is required
	 *    repeat()
	 *     repeats the given string
			$repeated=lv_str_of('a')->repeat(5);
			// aaaaa
	 *    replace()
	 *     replaces a given string within the string
			$replaced=lv_str_of('Laravel 6.x')->replace('6.x', '7.x');
			// Laravel 7.x
	 *     the replace method also accepts a case_sensitive argument
	 *     by default, the replace method is case sensitive
			$replaced=lv_str_of('macOS 13.x')->replace('macOS', 'iOS', false);
	 *     warning:
	 *      lv_str_replace function is required
	 *    replace_array()
	 *     replaces a given value in the string sequentially using an array
			$string='The event will take place between ? and ?';
			$replaced=lv_str_of($string)->replace_array('?', ['8:30', '9:00']);
			// The event will take place between 8:30 and 9:00
	 *     warning:
	 *      lv_str_replace_array function is required
	 *    replace_end()
	 *     replaces the last occurrence of the given value only
	 *     if the value appears at the end of the string
			$replaced=lv_str_of('Hello World')->replace_end('World', 'Laravel'); // Hello Laravel
			$replaced=lv_str_of('Hello World')->replace_end('Hello', 'Laravel'); // Hello World
	 *     warning:
	 *      lv_str_replace_end function is required
	 *    replace_first()
	 *     replaces the first occurrence of a given value in a string
			$replaced=lv_str_of('the quick brown fox jumps over the lazy dog')->replace_first('the', 'a');
			// a quick brown fox jumps over the lazy dog
	 *     warning:
	 *      lv_str_replace_first function is required
	 *    replace_last()
	 *     replaces the last occurrence of a given value in a string
			$replaced=lv_str_of('the quick brown fox jumps over the lazy dog')->replace_last('the', 'a');
			// the quick brown fox jumps over a lazy dog
	 *     warning:
	 *      lv_str_replace_last function is required
	 *    replace_matches()
	 *     replaces all portions of a string matching
	 *     a pattern with the given replacement string
			$replaced=lv_str_of('(+1) 501-555-1000')->replace_matches('/[^A-Za-z0-9]++/', '');
			// '15015551000'
	 *     the replace_matches method also accepts a closure
	 *     that will be invoked with each portion of the string
	 *     matching the given pattern, allowing you to perform
	 *     the replacement logic within the closure
	 *     and return the replaced value
			$replaced=lv_str_of('123')->replace_matches('/\d/', function(array $matches){
				return '['.$matches[0].']';
			});
			// '[1][2][3]'
	 *    replace_start()
	 *     replaces the first occurrence of the given value only
	 *     if the value appears at the start of the string
			$replaced=lv_str_of('Hello World')->replace_start('Hello', 'Laravel'); // Laravel World
			$replaced=lv_str_of('Hello World')->replace_start('World', 'Laravel'); // Hello World
	 *     warning:
	 *      lv_str_replace_start function is required
	 *    reverse()
	 *     reverses the given string
			$reversed=lv_str_of('Hello World')->reverse();
			// dlroW olleH
	 *     warning:
	 *      lv_str_reverse function is required
	 *    rtrim()
	 *     trims the right side of the given string
			$string=lv_str_of('  Laravel  ')->rtrim(); // '  Laravel'
			$string=lv_str_of('/Laravel/')->rtrim('/'); // '/Laravel'
	 *    scan()
	 *     parses input from a string into an array
	 *     according to a format supported by the sscanf PHP function
			$array=lv_str_of('filename.jpg')->scan('%[^.].%s');
			// ['filename', 'jpg']
	 *    snake()
	 *     converts the given string to snake_case
			$converted=lv_str_of('fooBar')->snake();
			// foo_bar
	 *     warning:
	 *      lv_str_snake function is required
	 *    split()
	 *     splits a string into an array using a regular expression
			$segments=lv_str_of('one, two, three')->split('/[\s,]+/');
			// ['one', 'two', 'three']
	 *     warning:
	 *      mbstring extension is required
	 *    squish()
	 *     removes all extraneous white space from a string
	 *     including extraneous white space between words
			$string=lv_str_of('    laravel    framework    ')->squish();
			// laravel framework
	 *     warning:
	 *      lv_str_squish function is required
	 *    start()
	 *     adds a single instance of the given value to a string
	 *     if it does not already start with that value
			$adjusted=lv_str_of('this/string')->start('/'); // /this/string
			$adjusted=lv_str_of('/this/string')->start('/'); // /this/string
	 *     warning:
	 *      lv_str_start function is required
	 *    starts_with()
	 *     determines if the given string begins with the given value
			$result=lv_str_of('This is my name')->starts_with('This');
			// true
	 *     warning:
	 *      lv_str_starts_with function is required
	 *    strip_tags()
	 *     removes all HTML and PHP tags from a string
			$result=lv_str_of('<a href="https://laravel.com">Taylor <b>Otwell</b></a>')->strip_tags();
			// Taylor Otwell
			$result=lv_str_of('<a href="https://laravel.com">Taylor <b>Otwell</b></a>')->strip_tags('<b>');
			// Taylor <b>Otwell</b>
	 *    studly()
	 *     converts the given string to StudlyCase
			$converted=lv_str_of('foo_bar')->studly();
			// FooBar
	 *     warning:
	 *      lv_str_studly function is required
	 *    substr()
	 *     returns the portion of the string specified
	 *     by the given start and length parameters
			$string=lv_str_of('Laravel Framework')->substr(8); // Framework
			$string=lv_str_of('Laravel Framework')->substr(8, 5); // Frame
	 *     warning:
	 *      lv_str_substr function is required
	 *    substr_count()
	 *     returns the number of occurrences
	 *     of a given value in the given string
			$count=lv_str_of('If you like ice cream, you will like snow cones.')->substr_count('like');
			// 2
	 *     warning:
	 *      lv_str_substr_count function is required
	 *    substr_replace()
	 *     replaces text within a portion of a string
	 *     starting at the position specified by the second argument
	 *     and replacing the number of characters specified by the third argument
	 *     passing 0 to the method's third argument will insert the string
	 *     at the specified position without replacing any
	 *     of the existing characters in the string
			$string=lv_str_of('1300')->substr_replace(':', 2); // '13:'
			$string=lv_str_of('The Framework')->substr_replace(' Laravel', 3, 0); // 'The Laravel Framework'
	 *     warning:
	 *      lv_str_substr_replace function is required
	 *    swap()
	 *     replaces multiple values in the string using PHP's strtr function
			$string=lv_str_of('Tacos are great!')->swap([
				'Tacos'=>'Burritos',
				'great'=>'fantastic'
			]);
			// Burritos are fantastic!
	 *    take()
	 *     returns a specified number of characters
	 *     from the beginning of the string
			$taken=lv_str_of('Build something amazing!')->take(5);
			// Build
	 *    tap()
	 *     passes the string to the given closure
	 *     allowing you to examine and interact with the string
	 *     while not affecting the string itself
	 *     the original string is returned by the tap method
	 *     regardless of what is returned by the closure
			$string=lv_str_of('Laravel')->append(' Framework')->tap(function(lv_str_ingable $string){
				// dump() is in symfony/var-dumper package
				dump('String after append: '.$string);
			})->upper();
			// LARAVEL FRAMEWORK
	 *    test()
	 *     determines if a string matches the given
	 *     regular expression pattern
			$result=lv_str_of('Laravel Framework')->test('/Laravel/');
			// true
	 *     warning:
	 *      is_match method is required
	 *    title()
	 *     converts the given string to Title Case
			$converted=lv_str_of('a nice title uses the correct case')->title();
			// A Nice Title Uses The Correct Case
	 *     warning:
	 *      lv_str_title function is required
	 *    to_base64()
	 *     converts the given string to Base64
			$base64=lv_str_of('Laravel')->to_base64();
			// TGFyYXZlbA==
	 *    to_boolean()
	 *     get the underlying string value as a boolean
	 *    to_float()
	 *     get the underlying string value as a float
	 *    to_integer()
	 *     get the underlying string value as an integer
	 *    to_string()
	 *     get the underlying string value
	 *    trim()
	 *     trims the given string
			$string=lv_str_of('  Laravel  ')->trim(); // 'Laravel'
			$string=lv_str_of('/Laravel/')->trim('/'); // 'Laravel'
	 *    ucfirst()
	 *     returns the given string with the first character capitalized
			$string=lv_str_of('foo bar')->ucfirst();
			// Foo bar
	 *     warning:
	 *      lv_str_ucfirst function is required
	 *    ucsplit()
	 *     splits the given string into an array by uppercase characters
			$string=lv_str_of('Foo Bar')->ucsplit();
			// ['Foo', 'Bar']
	 *     warning:
	 *      lv_str_ucsplit function is required
	 *    unless()
	 *     executes the given callback unless the first argument
	 *     given to the method evaluates to true
	 *     this method is not documented for stringable
	 *     the lv_str_excerpt function depends on this method
	 *     warning:
	 *      higher_order_when_proxy method is required
	 *    unwrap()
	 *     removes the specified strings
	 *     from the beginning and end of a given string
			$string=lv_str_of('-Laravel-')->unwrap('-'); // Laravel
			$string=lv_str_of('{framework: "Laravel"}')->unwrap('{', '}'); // framework: "Laravel"
	 *     warning:
	 *      lv_str_unwrap function is required
	 *    upper()
	 *     converts the given string to uppercase
			$adjusted=lv_str_of('laravel')->upper();
			// LARAVEL
	 *     warning:
	 *      lv_str_upper function is required
	 *    value()
	 *     alias for the to_string method
	 *     warning:
	 *      to_string method is required
	 *    when()
	 *     invokes the given closure if a given condition is true
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('Taylor')->when(true, function(lv_str_ingable $string){
				return $string->append(' Otwell');
			});
			// 'Taylor Otwell'
	 *     if necessary, you may pass another closure
	 *     as the third parameter to the when method
	 *     this closure will execute
	 *     if the condition parameter evaluates to false
	 *     warning:
	 *      higher_order_when_proxy method is required
	 *    when_contains()
	 *     invokes the given closure if the string contains the given value
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('tony stark')->when_contains('tony', function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Tony Stark'
	 *     if necessary, you may pass another closure
	 *     as the third parameter to the when method
	 *     this closure will execute
	 *     if the string does not contain the given value
	 *     you may also pass an array of values to determine
	 *     if the given string contains any of the values in the array
			$string=lv_str_of('tony stark')->when_contains(['tony', 'hulk'], function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Tony Stark'
	 *     warning:
	 *      contains method is required
	 *      when method is required
	 *    when_contains_all()
	 *     invokes the given closure if the string contains all of the given sub-strings
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('tony stark')->when_contains_all(['tony', 'stark'], function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Tony Stark'
	 *     if necessary, you may pass another closure
	 *     as the third parameter to the when method
	 *     this closure will execute
	 *     if the condition parameter evaluates to false
	 *     warning:
	 *      contains_all method is required
	 *      when method is required
	 *    when_empty()
	 *     invokes the given closure if the string is empty
	 *     if the closure returns a value
	 *     that value will also be returned by the when_empty method
	 *     if the closure does not return a value, the fluent string instance
	 *     will be returned
			$string=lv_str_of('  ')->trim()->when_empty(function(lv_str_ingable $string){
				return $string->prepend('Laravel');
			});
			// 'Laravel'
	 *     warning:
	 *      is_empty method is required
	 *      when method is required
	 *    when_ends_with()
	 *     invokes the given closure if the string
	 *     ends with the given sub-string
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('disney world')->when_ends_with('world', function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Disney World'
	 *     warning:
	 *      ends_with method is required
	 *      when method is required
	 *    when_exactly()
	 *     invokes the given closure if the string
	 *     exactly matches the given string
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('laravel')->when_exactly('laravel', function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Laravel'
	 *     warning:
	 *      exactly method is required
	 *      when method is required
	 *    when_is()
	 *     invokes the given closure if the string matches a given pattern
	 *     asterisks may be used as wildcard values
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('foo/bar')->when_is('foo/*', function(lv_str_ingable $string){
				return $string->append('/baz');
			});
			// 'foo/bar/baz'
	 *     warning:
	 *      is method is required
	 *      when method is required
	 *    when_not_empty()
	 *     invokes the given closure if the string is not empty
	 *     if the closure returns a value
	 *     that value will also be returned by the when_not_empty method
	 *     if the closure does not return a value, the fluent string instance
	 *     will be returned
			$string=lv_str_of('Framework')->when_not_empty(function(lv_str_ingable $string){
				return $string->prepend('Laravel ');
			});
			// 'Laravel Framework'
	 *     warning:
	 *      is_not_empty method is required
	 *      when method is required
	 *    when_not_exactly()
	 *     invokes the given closure if the string
	 *     does not exactly match the given string
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('framework')->when_not_exactly('laravel', function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Framework'
	 *     warning:
	 *      exactly method is required
	 *      when method is required
	 *    when_starts_with()
	 *     invokes the given closure if the string
	 *     starts with the given sub-string
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('disney world')->when_starts_with('disney', function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Disney World'
	 *     warning:
	 *      starts_with method is required
	 *      when method is required
	 *    when_test()
	 *     invokes the given closure
	 *     if the string matches the given regular expression
	 *     the closure will receive the fluent string instance
			$string=lv_str_of('laravel framework')->when_test('/laravel/', function(lv_str_ingable $string){
				return $string->title();
			});
			// 'Laravel Framework'
	 *     warning:
	 *      test method is required
	 *      when method is required
	 *    word_count()
	 *     returns the number of words that a string contains
			lv_str_of('Hello, world!')->word_count();
			// 2
	 *     warning:
	 *      lv_str_word_count function is required
	 *    word_wrap()
	 *     wraps a string to a given number of characters
			$text='The quick brown fox jumped over the lazy dog.';
			$string=lv_str_of($text)->word_wrap(20, "<br />\n");
			// The quick brown fox<br />
			// jumped over the lazy<br />
			// dog.
	 *     warning:
	 *      lv_str_word_wrap function is required
	 *    words()
	 *     limits the number of words in a string
	 *     if necessary, you may specify an additional string
	 *     that will be appended to the truncated string
			$string=lv_str_of('Perfectly balanced, as all things should be.')->words(3, ' >>>');
			// Perfectly balanced, as >>>
	 *     warning:
	 *      lv_str_words function is required
	 *    wrap()
	 *     wraps the given string with an additional string or pair of strings
			$string=lv_str_of('Laravel')->wrap('"'); // "Laravel"
			$string=lv_str_of('is')->wrap('This ', ' Laravel!'); // This is Laravel!
	 *     warning:
	 *      lv_str_wrap function is required
	 *   methods implemented in the lv_hlp component:
	 *    ascii()
	 *    dd()
	 *    dump()
	 *    inline_markdown()
	 *    is_ascii()
	 *    is_json()
	 *    is_ulid()
	 *    is_uuid()
	 *    macro()
	 *    markdown()
	 *    reverse()
	 *    slug()
	 *    transliterate()
	 *    when_is_ascii()
	 *    when_is_ulid()
	 *    when_is_uuid()
	 *   not implemented methods:
	 *    plural()
	 *    plural_studly()
	 *    singular()
	 *    to_date()
	 *    to_html_string()
	 *
	 * Functions implemented in the lv_hlp component:
	 *  lv_str_ascii()
	 *  lv_str_inline_markdown()
	 *  lv_str_is_ascii()
	 *  lv_str_is_json()
	 *  lv_str_is_ulid()
	 *  lv_str_is_uuid()
	 *  lv_str_markdown()
	 *  lv_str_ordered_uuid()
	 *  lv_str_password()
	 *  lv_str_reverse()
	 *  lv_str_slug()
	 *  lv_str_ulid()
	 *  lv_str_uuid()
	 *
	 * Not implemented functions:
	 *  lv_str_plural()
	 *  lv_str_plural_studly()
	 *  lv_str_singular()
	 *  lv_str_to_html_string()
	 *
	 * Sources:
	 *  https://laravel.com/docs/10.x/helpers
	 *  https://github.com/illuminate/support/blob/master/Str.php
	 *  https://github.com/laravel/framework/blob/10.x/src/Illuminate/Support/helpers.php
	 *  https://github.com/laravel/framework/blob/10.x/src/Illuminate/Support/Stringable.php
	 *  https://github.com/illuminate/conditionable/blob/master/Traits/Conditionable.php
	 *  https://github.com/illuminate/conditionable/blob/master/HigherOrderWhenProxy.php
	 *  https://github.com/laravel/framework/blob/10.x/src/Illuminate/Support/Traits/Tappable.php
	 *  https://github.com/laravel/framework/blob/10.x/src/Illuminate/Support/helpers.php
	 *  https://github.com/laravel/framework/blob/10.x/src/Illuminate/Support/HigherOrderTapProxy.php
	 * License: MIT
	 */

	class lv_str_exception extends Exception {}

	if(!function_exists('class_basename'))
	{
		function class_basename($class)
		{
			return lv_str_class_basename($class);
		}
	}
	if(!function_exists('preg_replace_array'))
	{
		function preg_replace_array($pattern, $replacements, $subject)
		{
			return lv_str_preg_replace_array($pattern, $replacements, $subject);
		}
	}

	function lv_str_after(string $subject, string $search)
	{
		if($search === '')
			return $subject;

		return array_reverse(explode($search, $subject, 2))[0];
	}
	function lv_str_after_last(string $subject, string $search)
	{
		if($search === '')
			return $subject;

		$position=strrpos($subject, (string)$search);

		if($position === false)
			return $subject;

		return substr($subject, $position+strlen($search));
	}
	function lv_str_before(string $subject, string $search)
	{
		if($search === '')
			return $subject;

		$result=strstr($subject, (string)$search, true);

		if($result === false)
			return $subject;

		return $result;
	}
	function lv_str_between(string $subject, string $from, string $to)
	{
		if(($from === '') || ($to === ''))
			return $subject;

		return lv_str_before_last(
			lv_str_after($subject, $from),
			$to
		);
	}
	function lv_str_between_first(string $subject, string $from, string $to)
	{
		if(($from === '') || ($to === ''))
			return $subject;

		return lv_str_before(
			lv_str_after($subject, $from),
			$to
		);
	}
	function lv_str_camel(string $value)
	{
		static $cache=[];

		if(isset($cache[$value]))
			return $cache[$value];

		$cache[$value]=lcfirst(lv_str_studly($value));

		return $cache[$value];
	}
	function lv_str_class_basename($class)
	{
		if(is_object($class))
			$class=get_class($class);

		return basename(str_replace('\\', '/', $class));
	}
	function lv_str_contains_all($haystack, $needles, $ignore_case=false)
	{
		foreach($needles as $needle)
			if(!lv_str_contains($haystack, $needle, $ignore_case))
				return false;

		return true;
	}
	function lv_str_ends_with(string $haystack, $needles)
	{
		if(!is_iterable($needles))
			$needles=(array)$needles;

		foreach($needles as $needle)
		{
			if($haystack === $needle)
				return true;

			if(
				((string)$needle !== '') &&
				(substr($haystack, -strlen($needle)) === (string)$needle)
			)
				return true;
		}

		return false;
	}
	function lv_str_finish(string $value, string $cap)
	{
		return preg_replace('/(?:'.preg_quote($cap, '/').')+$/u', '', $value).$cap;
	}
	function lv_str_headline(string $value)
	{
		$parts=explode(' ', $value);

		if(count($parts) > 1)
			$parts=array_map('lv_str_title', $parts);
		else
			$parts=array_map(
				'lv_str_title',
				lv_str_ucsplit(implode('_', $parts))
			);

		$collapsed=str_replace(
			['-', '_', ' '],
			'_',
			implode('_', $parts)
		);

		return implode(
			' ',
			array_filter(explode('_', $collapsed))
		);
	}
	function lv_str_is(string $pattern, string $value)
	{
		/*
		 * If the given value is an exact match we can of course return true right
		 * from the beginning. Otherwise, we will translate asterisks and do an
		 * actual pattern match against the two strings to see if they match.
		 */
		if($pattern === $value)
			return true;

		$pattern=preg_quote($pattern, '#');

		/*
		 * Asterisks are translated into zero-or-more regular expression wildcards
		 * to make it convenient to check if the strings starts with the given
		 * pattern such as "library/*", making any string check convenient.
		 */
		$pattern=str_replace('\*', '.*', $pattern);

		if(preg_match('#^'.$pattern.'\z#u', $value) === 1)
			return true;

		return false;
	}
	function lv_str_is_match($pattern, $value)
	{
		$value=(string)$value;

		if(!is_iterable($pattern))
			$pattern=[$pattern];

		foreach($pattern as $pattern)
		{
			$pattern=(string)$pattern;

			if(preg_match($pattern, $value) === 1)
				return true;
		}

		return false;
	}
	function lv_str_is_url(string $value, array $protocols=[])
	{
		if(empty($protocols))
			$protocol_list='aaa|aaas|about|acap|acct|acd|acr|adiumxtra|adt|afp|afs|aim|amss|android|appdata|apt|ark|attachment|aw|barion|beshare|bitcoin|bitcoincash|blob|bolo|browserext|calculator|callto|cap|cast|casts|chrome|chrome-extension|cid|coap|coap\+tcp|coap\+ws|coaps|coaps\+tcp|coaps\+ws|com-eventbrite-attendee|content|conti|crid|cvs|dab|data|dav|diaspora|dict|did|dis|dlna-playcontainer|dlna-playsingle|dns|dntp|dpp|drm|drop|dtn|dvb|ed2k|elsi|example|facetime|fax|feed|feedready|file|filesystem|finger|first-run-pen-experience|fish|fm|ftp|fuchsia-pkg|geo|gg|git|gizmoproject|go|gopher|graph|gtalk|h323|ham|hcap|hcp|http|https|hxxp|hxxps|hydrazone|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris\.beep|iris\.lwz|iris\.xpc|iris\.xpcs|isostore|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|leaptofrogans|lorawan|lvlt|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|mongodb|moz|ms-access|ms-browser-extension|ms-calculator|ms-drive-to|ms-enrollment|ms-excel|ms-eyecontrolspeech|ms-gamebarservices|ms-gamingoverlay|ms-getoffice|ms-help|ms-infopath|ms-inputapp|ms-lockscreencomponent-config|ms-media-stream-id|ms-mixedrealitycapture|ms-mobileplans|ms-officeapp|ms-people|ms-project|ms-powerpoint|ms-publisher|ms-restoretabcompanion|ms-screenclip|ms-screensketch|ms-search|ms-search-repair|ms-secondary-screen-controller|ms-secondary-screen-setup|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-connectabledevices|ms-settings-displays-topology|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|ms-spd|ms-sttoverlay|ms-transit-to|ms-useractivityset|ms-virtualtouchpad|ms-visio|ms-walk-to|ms-whiteboard|ms-whiteboard-cmd|ms-word|msnim|msrp|msrps|mss|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|ocf|oid|onenote|onenote-cmd|opaquelocktoken|openpgp4fpr|pack|palm|paparazzi|payto|pkcs11|platform|pop|pres|prospero|proxy|pwid|psyc|pttp|qb|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|s3|secondlife|service|session|sftp|sgn|shttp|sieve|simpleledger|sip|sips|skype|smb|sms|smtp|snews|snmp|soap\.beep|soap\.beeps|soldat|spiffe|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|tg|things|thismessage|tip|tn3270|tool|ts3server|turn|turns|tv|udp|unreal|urn|ut2004|v-event|vemmi|ventrilo|videotex|vnc|view-source|wais|webcal|wpid|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc\.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s';
		else
			$protocol_list=implode('|', $protocols);

		/*
		 * This pattern is derived from Symfony\Component\Validator\Constraints\UrlValidator (5.0.7).
		 *
		 * (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
		 */
		$pattern='~^
			(LARAVEL_PROTOCOLS):// # protocol
			(((?:[\_\.\pL\pN-]|%[0-9A-Fa-f]{2})+:)?((?:[\_\.\pL\pN-]|%[0-9A-Fa-f]{2})+)@)? # basic auth
			(
				([\pL\pN\pS\-\_\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
					| # or
				\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3} # an IP address
					| # or
				\[
					(?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
				\] # an IPv6 address
			)
			(:[0-9]+)? # a port (optional)
			(?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )* # a path
			(?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )? # a query (optional)
			(?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )? # a fragment (optional)
		$~ixu';

		return (preg_match(str_replace('LARAVEL_PROTOCOLS', $protocol_list, $pattern), $value) > 0);
	}
	function lv_str_kebab(string $value)
	{
		return lv_str_snake($value, '-');
	}
	function lv_str_match(string $pattern, string $subject)
	{
		preg_match($pattern, $subject, $matches);

		if(!$matches)
			return '';

		if(isset($matches[1]))
			return $matches[1];

		return $matches[0];
	}
	function lv_str_match_all(string $pattern, string $subject)
	{
		preg_match_all($pattern, $subject, $matches);

		if(empty($matches[0]))
			return [];

		if(isset($matches[1]))
			return $matches[1];

		return $matches[0];
	}
	function lv_str_of($string)
	{
		return new lv_str_ingable($string);
	}
	function lv_str_preg_replace_array(string $pattern, array $replacements, string $subject)
	{
		return preg_replace_callback(
			$pattern,
			function() use(&$replacements)
			{
				foreach($replacements as $value)
					return array_shift($replacements);
			},
			$subject
		);
	}
	function lv_str_random(int $length=16)
	{
		$string='';
		$len=0;

		while($len < $length)
		{
			$size=$length-$len;
			$bytes_size=(int)ceil($size/3)*3;
			$bytes=random_bytes($bytes_size);
			$string.=substr(
				str_replace(
					['/', '+', '='],
					'',
					base64_encode($bytes)
				),
				0,
				$size
			);
			$len=strlen($string);
		}

		return $string;
	}
	function lv_str_remove($search, $subject, bool $case_sensitive=true)
	{
		if($search instanceof Traversable)
			$search=iterator_to_array($search);

		if($case_sensitive)
			return str_replace($search, '', $subject);

		return str_ireplace($search, '', $subject);
	}
	function lv_str_repeat(string $string, int $times)
	{
		return str_repeat($string, $times);
	}
	function lv_str_replace(
		$search,
		$replace,
		$subject,
		bool $case_sensitive=true
	){
		if($search instanceof Traversable)
			$search=iterator_to_array($search);
		if($replace instanceof Traversable)
			$replace=iterator_to_array($replace);
		if($subject instanceof Traversable)
			$subject=iterator_to_array($subject);

		if($case_sensitive)
			return str_replace($search, $replace, $subject);

		return str_ireplace($search, $replace, $subject);
	}
	function lv_str_replace_array(string $search, $replace, string $subject)
	{
		$to_string_or=function($value, $fallback)
		{
			try {
				return (string)$value;
			} catch(Throwable $error) {
				return $fallback;
			}
		};

		if($replace instanceof Traversable)
			$replace=iterator_to_array($replace);

		$segments=explode($search, $subject);
		$result=array_shift($segments);

		foreach($segments as $segment)
			$result.=$to_string_or(array_shift($replace) ?? $search, $search).$segment;

		return $result;
	}
	function lv_str_replace_end(string $search, string $replace, string $subject)
	{
		$search=(string)$search;

		if($search === '')
			return $subject;

		if(lv_str_ends_with($subject, $search))
			return lv_str_replace_last($search, $replace, $subject);

		return $subject;
	}
	function lv_str_replace_first(string $search, string $replace, string $subject)
	{
		$search=(string)$search;

		if($search === '')
			return $subject;

		$position=strpos($subject, $search);

		if($position !== false)
			return substr_replace($subject, $replace, $position, strlen($search));

		return $subject;
	}
	function lv_str_replace_last(string $search, string $replace, string $subject)
	{
		$search=(string)$search;

		if($search === '')
			return $subject;

		$position=strrpos($subject, $search);

		if($position !== false)
			return substr_replace($subject, $replace, $position, strlen($search));

		return $subject;
	}
	function lv_str_replace_start(string $search, string $replace, string $subject)
	{
		$search=(string)$search;

		if($search === '')
			return $subject;

		if(lv_str_starts_with($subject, $search))
			return lv_str_replace_first($search, $replace, $subject);

		return $subject;
	}
	function lv_str_squish(string $value)
	{
		return preg_replace(
			'~(\s|\x{3164}|\x{1160})+~u',
			' ',
			preg_replace(
				'~^[\s\x{FEFF}]+|[\s\x{FEFF}]+$~u',
				'',
				$value
			)
		);
	}
	function lv_str_start(string $value, string $prefix)
	{
		return ''
		.	$prefix
		.	preg_replace(''
			.	'/^(?:'
			.	preg_quote($prefix, '/')
			.	')+/u',
				'',
				$value
			);
	}
	function lv_str_starts_with(string $haystack, $needles)
	{
		if(!is_iterable($needles))
			$needles=[$needles];

		foreach($needles as $needle)
			if(
				((string)$needle !== '') &&
				(strncmp($haystack, $needle, strlen($needle)) === 0)
			)
				return true;

		return false;
	}
	function lv_str_str($string=null)
	{
		if(func_num_args() === 0)
			return new class
			{
				public function __call($method, $parameters)
				{
					$method='lv_str_'.$method;
					return $method(...$parameters);
				}
				public function __toString()
				{
					return '';
				}
			};

		return lv_str_of($string);
	}
	function lv_str_studly(string $value)
	{
		static $cache=[];

		if(isset($cache[$value]))
			return $cache[$value];

		$words=explode(
			' ',
			str_replace(
				['-', '_'],
				' ',
				$value
			)
		);
		$studly_words=array_map('lv_str_ucfirst', $words);

		$cache[$value]=implode($studly_words);

		return $cache[$value];
	}
	function lv_str_substr_count(
		string $haystack,
		string $needle,
		int $offset=0,
		int $length=null
	){
		if(!is_null($length))
			return substr_count($haystack, $needle, $offset, $length);

		return substr_count($haystack, $needle, $offset);
	}
	function lv_str_substr_replace($string, $replace, $offset=0, $length=null)
	{
		if($length === null)
			$length=strlen($string);

		return substr_replace($string, $replace, $offset, $length);
	}
	function lv_str_swap(array $map, string $subject)
	{
		return strtr($subject, $map);
	}
	function lv_str_ucsplit(string $string)
	{
		return preg_split('/(?=\p{Lu})/u', $string, -1, PREG_SPLIT_NO_EMPTY);
	}
	function lv_str_unwrap(string $value, string $before, string $after=null)
	{
		if(lv_str_starts_with($value, $before))
			$value=lv_str_substr(
				$value,
				lv_str_length($before)
			);

		if(!isset($after))
			$after=$before;

		if(lv_str_ends_with($value, $after))
			$value=lv_str_substr($value, 0, -lv_str_length($after));

		return $value;
	}
	function lv_str_word_count(string $string, string $characters=null)
	{
		return str_word_count($string, 0, $characters);
	}
	function lv_str_word_wrap(
		string $string,
		int $characters=75,
		string $break="\n",
		bool $cut_long_words=false
	){
		return wordwrap($string, $characters, $break, $cut_long_words);
	}
	function lv_str_wrap(string $value, string $before, string $after=null)
	{
		if($after === null)
			return $before.$value.$before;

		return $before.$value.$after;
	}

	if(function_exists('mb_substr'))
	{
		function lv_str_apa(string $value)
		{
			if(trim($value) === '')
				return $value;

			$minor_words=[
				'and', 'as', 'but', 'for', 'if', 'nor', 'or', 'so', 'yet', 'a', 'an',
				'the', 'at', 'by', 'for', 'in', 'of', 'off', 'on', 'per', 'to', 'up', 'via',
				'et', 'ou', 'un', 'une', 'la', 'le', 'les', 'de', 'du', 'des', 'par', 'a'
			];
			$end_punctuation=['.', '!', '?', ':', '—', ','];
			$words=preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);

			for($i=0; $i<count($words); ++$i)
			{
				$lowercase_word=mb_strtolower($words[$i]);

				if(strpos($lowercase_word, '-') !== false)
				{
					$hyphenated_words=explode('-', $lowercase_word);
					$hyphenated_words=array_map(
						function($part) use($minor_words)
						{
							if(
								in_array($part, $minor_words) &&
								(mb_strlen($part) <= 3)
							)
								return $part;

							return ''
							.	mb_strtoupper(mb_substr($part, 0, 1))
							.	mb_substr($part, 1);
						},
						$hyphenated_words
					);
					$words[$i]=implode('-', $hyphenated_words);

					continue;
				}

				if(
					in_array($lowercase_word, $minor_words) &&
					(mb_strlen($lowercase_word) <= 3) &&
					(!(
						($i === 0) ||
						in_array(mb_substr(
							$words[$i-1],
							-1
						), $end_punctuation)
					))
				){
					$words[$i]=$lowercase_word;
					continue;
				}

				$words[$i]=''
				.	mb_strtoupper(mb_substr($lowercase_word, 0, 1))
				.	mb_substr($lowercase_word, 1);
			}

			return implode(' ', $words);
		}
		function lv_str_before_last(string $subject, string $search)
		{
			if($search === '')
				return $subject;

			$pos=mb_strrpos($subject, $search);

			if($pos === false)
				return $subject;

			return mb_substr($subject, 0, $pos, 'UTF-8');
		}
		function lv_str_char_at(string $subject, int $index)
		{
			$length=mb_strlen($subject);

			if(($index < 0) && ($index < -$length))
				return false;

			if($index > $length-1)
				return false;

			return mb_substr($subject, $index, 1);
		}
		function lv_str_contains(string $haystack, $needles, bool $ignore_case=false)
		{
			if($ignore_case)
				$haystack=mb_strtolower($haystack);

			if(!is_iterable($needles))
				$needles=(array)$needles;

			foreach($needles as $needle)
			{
				if($ignore_case)
					$needle=mb_strtolower($needle);

				if(($needle !== '') && (mb_strpos($haystack, $needle) !== false))
					return true;
			}

			return false;
		}
		function lv_str_excerpt(string $text, string $phrase='', array $options=[])
		{
			$radius=100;
			$omission='...';

			if(isset($options['radius']))
				$radius=$options['radius'];

			if(isset($options['omission']))
				$omission=$options['omission'];

			preg_match(
				'/^(.*?)('.preg_quote((string)$phrase, '/').')(.*)$/iu',
				(string)$text,
				$matches
			);

			if(empty($matches))
				return null;

			$start=ltrim($matches[1]);
			$start=lv_str_str(mb_substr(
				$start,
				max(
					mb_strlen($start, 'UTF-8')-$radius,
					0
				),
				$radius,
				'UTF-8'
			))
			->	ltrim()
			->	unless(
					function($start_with_radius) use($start)
					{
						return $start_with_radius->exactly($start);
					},
					function($start_with_radius) use($omission)
					{
						return $start_with_radius->prepend($omission);
					}
				);
			$end=rtrim($matches[3]);
			$end=lv_str_str(mb_substr(
				$end, 0, $radius, 'UTF-8'
			))
			->	rtrim()
			->	unless(
					function($end_with_radius) use($end)
					{
						return $end_with_radius->exactly($end);
					},
					function($end_with_radius) use($omission)
					{
						return $end_with_radius->append($omission);
					}
				);

			return $start
			->	append($matches[2], $end)
			->	to_string();
		}
		function lv_str_lcfirst(string $string)
		{
			return ''
			.	mb_strtolower(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8')
			.	mb_substr($string, 1, null, 'UTF-8');
		}
		function lv_str_length(string $value, string $encoding='UTF-8')
		{
			return mb_strlen($value, $encoding);
		}
		function lv_str_limit(string $value, int $limit=100, string $end='...')
		{
			if(mb_strwidth($value, 'UTF-8') <= $limit)
				return $value;

			return ''
			.	rtrim(mb_strimwidth(
					$value, 0, $limit, '', 'UTF-8'
				))
			.	$end;
		}
		function lv_str_lower(string $value)
		{
			return mb_strtolower($value, 'UTF-8');
		}
		function lv_str_mask(
			string $string,
			string $character,
			int $index,
			int $length=null,
			string $encoding='UTF-8'
		){
			if($character === '')
				return $string;

			$segment=mb_substr($string, $index, $length, $encoding);

			if($segment === '')
				return $string;

			$strlen=mb_strlen($string, $encoding);
			$start_index=$index;

			if($index < 0)
			{
				$start_index=0;

				if($index >= -$strlen)
					$start_index=$strlen+$index;
			}

			$start=mb_substr($string, 0, $start_index, $encoding);
			$segment_len=mb_strlen($segment, $encoding);
			$end=mb_substr($string, $start_index+$segment_len);

			return ''
			.	$start
			.	str_repeat(
					mb_substr($character, 0, 1, $encoding),
					$segment_len
				)
			.	$end;
		}
		function lv_str_pad_both(string $value, int $length, string $pad=' ')
		{
			$short=max(0, $length-mb_strlen($value));
			$short_left=floor($short/2);
			$short_right=ceil($short/2);

			return ''
			.	mb_substr(str_repeat($pad, $short_left), 0, $short_left)
			.	$value
			.	mb_substr(str_repeat($pad, $short_right), 0, $short_right);
		}
		function lv_str_pad_left(string $value, int $length, string $pad=' ')
		{
			$short=max(0, $length-mb_strlen($value));
			return mb_substr(str_repeat($pad, $short), 0, $short).$value;
		}
		function lv_str_pad_right(string $value, int $length, string $pad=' ')
		{
			$short=max(0, $length-mb_strlen($value));
			return $value.mb_substr(str_repeat($pad, $short), 0, $short);
		}
		function lv_str_position(string $haystack, $needle, int $offset=0, string $encoding=null)
		{
			if($encoding === null)
				$encoding=mb_internal_encoding();

			return mb_strpos($haystack, (string)$needle, $offset, $encoding);
		}
		function lv_str_substr(
			string $string,
			int $start,
			int $length=null,
			string $encoding='UTF-8'
		){
			return mb_substr($string, $start, $length, $encoding);
		}
		function lv_str_title(string $value)
		{
			return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
		}
		function lv_str_ucfirst(string $string)
		{
			return ''
			.	mb_strtoupper(
					mb_substr($string, 0, 1, 'UTF-8'),
					'UTF-8'
				)
			.	mb_substr($string, 1, null, 'UTF-8');
		}
		function lv_str_upper(string $value)
		{
			return mb_strtoupper($value, 'UTF-8');
		}
		function lv_str_words(string $value, int $words=100, string $end='...')
		{
			preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

			if(
				(!isset($matches[0])) ||
				(mb_strlen($value, 'UTF-8') === mb_strlen($matches[0], 'UTF-8'))
			)
				return $value;

			return rtrim($matches[0]).$end;
		}

		if(function_exists('ctype_lower'))
		{
			function lv_str_snake(string $value, string $delimiter='_')
			{
				static $cache=[];

				$key=$value;

				if(isset($cache[$key][$delimiter]))
					return $cache[$key][$delimiter];

				if(!ctype_lower($value))
					$value=mb_strtolower(
						preg_replace(
							'/(.)(?=[A-Z])/u',
							'$1'.$delimiter,
							preg_replace(
								'/\s+/u',
								'',
								ucwords($value)
							)
						),
						'UTF-8'
					);

				$cache[$key][$delimiter]=$value;

				return $value;
			}
		}
		else /* some boilerplate */
		{
			function lv_str_snake()
			{
				throw new lv_str_exception('ctype extension is not loaded');
			}
		}
	}
	else /* some boilerplate */
	{
		function lv_str_apa()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_before_last()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_char_at()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_contains()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_excerpt()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_lcfirst()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_length()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_limit()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_lower()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_mask()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_pad_both()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_pad_left()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_pad_right()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_snake()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_position()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_substr()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_title()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_ucfirst()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_upper()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
		function lv_str_words()
		{
			throw new lv_str_exception('mbstring extension is not loaded');
		}
	}

	class lv_str_ingable implements JsonSerializable, ArrayAccess
	{
		// use conditionable, tappable;

		/* trait conditionable */
		/* { */
			// dev note: this trait appears in the lv_arr.php library

			protected function higher_order_when_proxy($target)
			{
				return new class($target)
				{
					// class HigherOrderWhenProxy

					private $target;
					private $condition;
					private $has_condition=false;
					private $negate_condition_on_capture;

					public function __construct($target)
					{
						$this->target=$target;
					}
					public function __get($key)
					{
						if(!$this->has_condition)
						{
							$condition=$this->target->$key;

							if($this->negate_condition_on_capture)
								return $this->condition(!$condition);

							return $this->condition($condition);
						}

						if($this->condition)
							return $this->target->$key;

						return $this->target;
					}
					public function __call($method, $parameters)
					{
						if(!$this->has_condition)
						{
							$condition=$this->target->$method(...$parameters);

							if($this->negate_condition_on_capture)
								return $this->condition(!$condition);

							return $this->condition($condition);
						}

						if($this->condition)
							return $this->target->$method(...$parameters);

						return $this->target;
					}

					public function condition($condition)
					{
						$this->condition=$condition;
						$this->has_condition=true;

						return $this;
					}
					public function negate_condition_on_capture()
					{
						$this->negate_condition_on_capture=true;
						return $this;
					}
				};
			}

			public function unless($value=null, callable $callback=null, callable $default=null)
			{
				if($value instanceof Closure)
					$value=$value($this);

				if(func_num_args() === 0)
					return $this->higher_order_when_proxy($this)->negate_condition_on_capture();
				if(func_num_args() === 1)
					return $this->higher_order_when_proxy($this)->condition(!$value);

				if(!$value)
					return ($callback($this, $value) ?? $this);

				if($default)
					return ($default($this, $value) ?? $this);

				return $this;
			}
			public function when($value=null, callable $callback=null, callable $default=null)
			{
				if($value instanceof Closure)
					$value=$value($this);

				if(func_num_args() === 0)
					return new $this->higher_order_when_proxy($this);
				if(func_num_args() === 1)
					return $this->higher_order_when_proxy($this)->condition($value);

				if($value)
				{
					$callback_output=$callback($this, $value);

					if(isset($callback_output))
						return $callback_output;

					return $this;
				}

				if($default)
				{
					$callback_output=$default($this, $value);

					if(isset($callback_output))
						return $callback_output;

					return $this;
				}

				return $this;
			}
		/* } */

		/* trait tappable */
		/* { */
			protected function higher_order_tap_proxy($target)
			{
				return new class($target)
				{
					// class HigherOrderTapProxy

					private $target;

					public function __construct($target)
					{
						$this->target=$target;
					}
					public function __call($method, $parameters)
					{
						$this->target->$method(...$parameters);
						return $this->target;
					}
				};
			}

			public function tap(callable $callback=null)
			{
				if(is_null($callback))
					return $this->higher_order_tap_proxy($this);

				$callback($this);

				return $this;
			}
		/* } */

		protected $value;

		public function __construct($value='')
		{
			$this->value=(string)$value;
		}
		public function __get(string $key)
		{
			return $this->$key();
		}
		public function __toString()
		{
			return (string)$this->value;
		}

		public function jsonSerialize(): string
		{
			return $this->__toString();
		}
		public function offsetExists($offset): bool
		{
			return isset($this->value[$offset]);
		}
		public function offsetGet($offset): string
		{
			return $this->value[$offset];
		}
		public function offsetSet($offset, $value): void
		{
			$this->value[$offset]=$value;
		}
		public function offsetUnset($offset): void
		{
			unset($this->value[$offset]);
		}

		public function after(string $search)
		{
			return new static(lv_str_after($this->value, $search));
		}
		public function after_last(string $search)
		{
			return new static(lv_str_after_last($this->value, $search));
		}
		public function apa()
		{
			return new static(lv_str_apa($this->value));
		}
		public function append(...$values)
		{
			return new static($this->value.implode('', $values));
		}
		public function basename(string $suffix='')
		{
			return new static(basename($this->value, $suffix));
		}
		public function before(string $search)
		{
			return new static(lv_str_before($this->value, $search));
		}
		public function before_last(string $search)
		{
			return new static(lv_str_before_last($this->value, $search));
		}
		public function between(string $from, string $to)
		{
			return new static(lv_str_between($this->value, $from, $to));
		}
		public function between_first(string $from, string $to)
		{
			return new static(lv_str_between_first($this->value, $from, $to));
		}
		public function camel()
		{
			return new static(lv_str_camel($this->value));
		}
		public function char_at(int $index)
		{
			return lv_str_char_at($this->value, $index);
		}
		public function contains($needles, bool $ignore_case=false)
		{
			return lv_str_contains($this->value, $needles, $ignore_case);
		}
		public function contains_all($needles, bool $ignore_case=false)
		{
			return lv_str_contains_all($this->value, $needles, $ignore_case);
		}
		public function class_basename()
		{
			return new static(lv_str_class_basename($this->value));
		}
		public function dirname(int $levels=1)
		{
			return new static(dirname($this->value, $levels));
		}
		public function ends_with($needles)
		{
			return lv_str_ends_with($this->value, $needles);
		}
		public function exactly($value)
		{
			if($value instanceof lv_str_ingable)
				$value=$value->to_string();

			return ($this->value === $value);
		}
		public function excerpt(string $phrase='', array $options=[])
		{
			return lv_str_excerpt($this->value, $phrase, $options);
		}
		public function explode(string $delimiter, int $limit=PHP_INT_MAX)
		{
			return explode($delimiter, $this->value, $limit);
		}
		public function finish(string $cap)
		{
			return new static(lv_str_finish($this->value, $cap));
		}
		public function from_base64(bool $strict=false)
		{
			return new static(base64_decode($this->value, $strict));
		}
		public function headline()
		{
			return new static(lv_str_headline($this->value));
		}
		public function is($pattern)
		{
			return lv_str_is($pattern, $this->value);
		}
		public function is_empty()
		{
			return ($this->value === '');
		}
		public function is_match($pattern)
		{
			return lv_str_is_match($pattern, $this->value);
		}
		public function is_not_empty()
		{
			return (!$this->is_empty());
		}
		public function is_url()
		{
			return lv_str_is_url($this->value);
		}
		public function kebab()
		{
			return new static(lv_str_kebab($this->value));
		}
		public function lcfirst()
		{
			return new static(lv_str_lcfirst($this->value));
		}
		public function length(string $encoding='UTF-8')
		{
			return lv_str_length($this->value, $encoding);
		}
		public function limit(int $limit=100, string $end='...')
		{
			return new static(lv_str_limit($this->value, $limit, $end));
		}
		public function lower()
		{
			return new static(lv_str_lower($this->value));
		}
		public function ltrim(string $characters=null)
		{
			return new static(ltrim(...array_merge([$this->value], func_get_args())));
		}
		public function mask(string $character, int $index, int $length=null, string $encoding='UTF-8')
		{
			return new static(lv_str_mask($this->value, $character, $index, $length, $encoding));
		}
		public function match(string $pattern)
		{
			return new static(lv_str_match($pattern, $this->value));
		}
		public function match_all(string $pattern)
		{
			return lv_str_match_all($pattern, $this->value);
		}
		public function new_line(int $count=1)
		{
			return $this->append(str_repeat(PHP_EOL, $count));
		}
		public function pad_both(int $length, string $pad=' ')
		{
			return new static(lv_str_pad_both($this->value, $length, $pad));
		}
		public function pad_left(int $length, string $pad=' ')
		{
			return new static(lv_str_pad_left($this->value, $length, $pad));
		}
		public function pad_right(int $length, string $pad=' ')
		{
			return new static(lv_str_pad_right($this->value, $length, $pad));
		}
		public function pipe(callable $callback)
		{
			return new static($callback($this));
		}
		public function position(string $needle, int $offset=0, string $encoding=null)
		{
			return lv_str_position($this->value, $needle, $offset, $encoding);
		}
		public function prepend(...$values)
		{
			return new static(implode('', $values).$this->value);
		}
		public function remove($search, bool $case_sensitive=true)
		{
			return new static(lv_str_remove($search, $this->value, $case_sensitive));
		}
		public function repeat(int $times)
		{
			return new static(str_repeat($this->value, $times));
		}
		public function replace($search, $replace, bool $case_sensitive=true)
		{
			return new static(lv_str_replace($search, $replace, $this->value, $case_sensitive));
		}
		public function replace_array(string $search, $replace)
		{
			return new static(lv_str_replace_array($search, $replace, $this->value));
		}
		public function replace_end(string $search, string $replace)
		{
			return new static(lv_str_replace_end($search, $replace, $this->value));
		}
		public function replace_first(string $search, string $replace)
		{
			return new static(lv_str_replace_first($search, $replace, $this->value));
		}
		public function replace_last(string $search, string $replace)
		{
			return new static(lv_str_replace_last($search, $replace, $this->value));
		}
		public function replace_start(string $search, string $replace)
		{
			return new static(lv_str_replace_start($search, $replace, $this->value));
		}
		public function replace_matches($pattern, $replace, int $limit=-1)
		{
			if($replace instanceof Closure)
				return new static(preg_replace_callback($pattern, $replace, $this->value, $limit));

			return new static(preg_replace($pattern, $replace, $this->value, $limit));
		}
		public function rtrim(string $characters=null)
		{
			return new static(rtrim(...array_merge([$this->value], func_get_args())));
		}
		public function scan(string $format)
		{
			return sscanf($this->value, $format);
		}
		public function snake(string $delimiter='_')
		{
			return new static(lv_str_snake($this->value, $delimiter));
		}
		public function split($pattern, int $limit=-1, int $flags=0)
		{
			if(!function_exists('mb_str_split'))
				throw new lv_str_exception('mb_str_split function is not defined');

			if(filter_var($pattern, FILTER_VALIDATE_INT) !== false)
				return mb_str_split($this->value, $pattern);

			$segments=preg_split($pattern, $this->value, $limit, $flags);

			if(empty($segments))
				return [];

			return $segments;
		}
		public function squish()
		{
			return new static(lv_str_squish($this->value));
		}
		public function start(string $prefix)
		{
			return new static(lv_str_start($this->value, $prefix));
		}
		public function starts_with($needles)
		{
			return lv_str_starts_with($this->value, $needles);
		}
		public function strip_tags($allowed_tags=null)
		{
			return new static(strip_tags($this->value, $allowed_tags));
		}
		public function studly()
		{
			return new static(lv_str_studly($this->value));
		}
		public function substr(int $start, int $length=null, string $encoding='UTF-8')
		{
			return new static(lv_str_substr($this->value, $start, $length, $encoding));
		}
		public function substr_count(string $needle, int $offset=0, int $length=null)
		{
			return lv_str_substr_count($this->value, $needle, $offset, $length);
		}
		public function substr_replace($replace, $offset=0, $length=null)
		{
			return new static(lv_str_substr_replace($this->value, $replace, $offset, $length));
		}
		public function swap(array $map)
		{
			return new static(strtr($this->value, $map));
		}
		public function take(int $limit)
		{
			if($limit < 0)
				return $this->substr($limit);

			return $this->substr(0, $limit);
		}
		public function test(string $pattern)
		{
			return $this->is_match($pattern);
		}
		public function title()
		{
			return new static(lv_str_title($this->value));
		}
		public function to_base64()
		{
			return new static(base64_encode($this->value));
		}
		public function to_boolean()
		{
			return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
		}
		public function to_float()
		{
			return floatval($this->value);
		}
		public function to_integer(int $base=10)
		{
			return intval($this->value, $base);
		}
		public function to_string()
		{
			return $this->value;
		}
		public function trim(string $characters=null)
		{
			return new static(trim(...array_merge([$this->value], func_get_args())));
		}
		public function ucfirst()
		{
			return new static(lv_str_ucfirst($this->value));
		}
		public function ucsplit()
		{
			return lv_str_ucsplit($this->value);
		}
		public function unwrap(string $before, string $after=null)
		{
			return new static(lv_str_unwrap($this->value, $before, $after));
		}
		public function upper()
		{
			return new static(lv_str_upper($this->value));
		}
		public function value()
		{
			return $this->to_string();
		}
		public function when_contains($needles, callable $callback, callable $default=null)
		{
			return $this->when($this->contains($needles), $callback, $default);
		}
		public function when_contains_all(array $needles, callable $callback, callable $default=null)
		{
			return $this->when($this->contains_all($needles), $callback, $default);
		}
		public function when_empty(callable $callback, callable $default=null)
		{
			return $this->when($this->is_empty(), $callback, $default);
		}
		public function when_ends_with($needles, callable $callback, callable $default=null)
		{
			return $this->when($this->ends_with($needles), $callback, $default);
		}
		public function when_exactly(string $value, callable $callback, callable $default=null)
		{
			return $this->when($this->exactly($value), $callback, $default);
		}
		public function when_is($pattern, callable $callback, callable $default=null)
		{
			return $this->when($this->is($pattern), $callback, $default);
		}
		public function when_not_empty(callable $callback, callable $default=null)
		{
			return $this->when($this->is_not_empty(), $callback, $default);
		}
		public function when_not_exactly(string $value, callable $callback, callable $default=null)
		{
			return $this->when((!$this->exactly($value)), $callback, $default);
		}
		public function when_starts_with($needles, callable $callback, callable $default=null)
		{
			return $this->when($this->starts_with($needles), $callback, $default);
		}
		public function when_test(string $pattern, callable $callback, callable $default=null)
		{
			return $this->when($this->test($pattern), $callback, $default);
		}
		public function word_count(string $characters=null)
		{
			return lv_str_word_count($this->value, $characters);
		}
		public function word_wrap(int $characters=75, string $break="\n", bool $cut_long_words=false)
		{
			return new static(lv_str_word_wrap($this->value, $characters, $break, $cut_long_words));
		}
		public function words(int $words=100, string $end='...')
		{
			return new static(lv_str_words($this->value, $words, $end));
		}
		public function wrap(string $before, string $after=null)
		{
			return new static(lv_str_wrap($this->value, $before, $after));
		}
	}
?>