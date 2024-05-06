# Laravel helpers
Extension of `lv_arr.php` and `lv_str.php` libraries  
This file only describes new and modified functions/methods  
The rest of the documentation is located in the `lv_arr.php` and `lv_str.php` libraries  
This component is licensed under the MIT License

## Required libraries
* `ascii.php`
* `lv_arr.php`
* `lv_macroable.php`
* `lv_str.php`
* `ocw_slugify.php`
* `ulid.php`
* `uuid.php`
* `pf_json_validate.php` (for PHP older than 8.3)
* `pf_array.php` (for PHP older than 8.1)
* `pf_get_debug_type.php` (for PHP older than 8.0)
* `pf_str.php` (for PHP older than 8.0)
* `pf_ValueError.php` (for PHP older than 8.0)
* `pf_mbstring.php` (for PHP older than 7.4)
* `var_export_contains.php` (for tests)
* `bin/get-composer.php` (for tests)
* `tests/lv_arr.php` (for tests)
* `curl_file_updown.php` (for get-composer.php tool)

## Suggested extensions
* `intl`
* `mbstring`

## Suggested packages
* `league/commonmark`
* `symfony/var-dumper` (for development)

## Note
Throws an `lv_hlp_exception` on error

## String helpers
* `lv_str_ascii`  
	Will attempt to transliterate the string into an ASCII value:
	```
	$ascii=lv_str_ascii('ü'); // u
	```
* `lv_str_contains`  
	Determines if the given string contains the given value:
	```
	$contains=lv_str_contains('This is my name', 'my');
	// true
	```
	You may also pass an array of values to determine if the given string contains any of the values in the array:
	```
	$contains=lv_str_contains('This is my name', ['my', 'foo']);
	// true
	```
	**Note:**  
	this method is case sensitive  
	**Warning:**  
	mbstring extension is required
* `lv_str_contains_all`  
	Determines if the given string contains all of the values in a given array:
	```
	$contains_all=lv_str_contains_all('This is my name', ['my', 'name']);
	// true
	```
	**Warning:**  
	lv_str_contains function is required
* `lv_str_ends_with`  
	Determines if the given string ends with the given value:
	```
	$result=lv_str_ends_with('This is my name', 'name');
	// true
	```
	You may also pass an array of values to determine if the given string ends with any of the values in the array:
	```
	$result=lv_str_ends_with('This is my name', ['name', 'foo']);
	// true
	$result=lv_str_ends_with('This is my name', ['this', 'foo']);
	// false
	```
* `lv_str_inline_markdown`  
	Converts GitHub flavored Markdown into inline HTML using CommonMark.  
	However, unlike the markdown method, it does not wrap all generated HTML in a block-level element:
	```
	$html=lv_str_inline_markdown('**Laravel**');
	// <strong>Laravel</strong>
	```
	**Markdown Security**  
	By default, Markdown supports raw HTML, which will expose Cross-Site Scripting (XSS) vulnerabilities when used with raw user input.  
	As per the CommonMark Security documentation, you may use the html_input option to either escape or strip raw HTML,  
	and the allow_unsafe_links option to specify whether to allow unsafe links.  
	If you need to allow some raw HTML, you should pass your compiled Markdown through an HTML Purifier:
	```
	lv_str_inline_markdown('Inject: <script>alert("Hello XSS!");</script>', [
		'html_input'=>'strip',
		'allow_unsafe_links'=>false
	]);
	// Inject: alert(&quot;Hello XSS!&quot;);
	```
	**Note:**  
	tested on CommonMark 1.6.7  
	**Warning:**  
	league/commonmark package is required
* `lv_str_is_ascii`  
	Determines if a given string is 7 bit ASCII:
	```
	$is_ascii=lv_str_is_ascii('Taylor'); // true
	$is_ascii=lv_str_is_ascii('ü'); // false
	```
	**Warning:**  
	intl extension is required
* `lv_str_is_json`  
	Determines if the given string is valid JSON:
	```
	$result=lv_str_is_json('[1,2,3]'); // true
	$result=lv_str_is_json('{"first": "John", "last": "Doe"}'); // true
	$result=lv_str_is_json('{first: "John", last: "Doe"}'); // false
	```
* `lv_str_is_ulid`  
	Determines if the given string is a valid ULID:
	```
	$is_ulid=lv_str_is_ulid('01gd6r360bp37zj17nxb55yv40');
	// true
	$is_ulid=lv_str_is_ulid('laravel');
	// false
	```
* `lv_str_is_uuid`  
	Determines if the given string is a valid UUID:
	```
	$is_uuid=lv_str_is_uuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de');
	// true
	$is_uuid=lv_str_is_uuid('laravel');
	// false
	```
* `lv_str_markdown`  
	Converts GitHub flavored Markdown into HTML using CommonMark:
	```
	$html=lv_str_markdown('# Laravel');
	// <h1>Laravel</h1>
	$html=lv_str_markdown('# Taylor <b>Otwell</b>', [
		'html_input'=>'strip'
	]);
	// <h1>Taylor Otwell</h1>
	```
	**Markdown Security**  
	By default, Markdown supports raw HTML, which will expose Cross-Site Scripting (XSS) vulnerabilities when used with raw user input.  
	As per the CommonMark Security documentation, you may use the html_input option to either escape or strip raw HTML,  
	and the allow_unsafe_links option to specify whether to allow unsafe links.  
	If you need to allow some raw HTML, you should pass your compiled Markdown through an HTML Purifier:
	```
	lv_str_markdown('Inject: <script>alert("Hello XSS!");</script>', [
		'html_input'=>'strip',
		'allow_unsafe_links'=>false
	]);
	// Inject: alert(&quot;Hello XSS!&quot;);
	```
	**Note:**  
	tested on CommonMark 1.6.7  
	**Warning:**  
	league/commonmark package is required
* `lv_str_ordered_uuid`  
	Generates a "timestamp first" UUID that may be efficiently stored in an indexed database column.  
	Each UUID that is generated using this method will be sorted after UUIDs previously generated using the method:
	```
	return (string)lv_str_ordered_uuid();
	```
* `lv_str_password`  
	May be used to generate a secure, random password of a given length.  
	The password will consist of a combination of letters, numbers, symbols, and spaces.  
	By default, passwords are 32 characters long:
	```
	$password=lv_str_password();
	// 'EbJo2vE-AS:U,$%_gkrV4n,q~1xy/-_4'
	$password=lv_str_password(12);
	// 'qwuar>#V|i]N'
	```
	**Warning:**  
	lv_hlp_collection class is required
* `lv_str_remove`  
	Removes the given value or array of values from the string:
	```
	$string='Peter Piper picked a peck of pickled peppers.';
	$removed=lv_str_remove('e', $string);
	// Ptr Pipr pickd a pck of pickld ppprs.
	```
* `lv_str_replace`  
	Replaces a given string within the string:
	```
	$string='Laravel 10.x';
	$replaced=lv_str_replace('10.x', '11.x', $string);
	// Laravel 11.x
	```
	The replace method also accepts a caseSensitive argument.  
	By default, the replace method is case sensitive:
	```
	lv_str_replace('Framework', 'Laravel', $string, false);
	```
* `lv_str_replace_array`  
	Replaces a given value in the string sequentially using an array:
	```
	$string='The event will take place between ? and ?';
	$replaced=lv_str_replace_array('?', ['8:30', '9:00'], $string);
	// The event will take place between 8:30 and 9:00
	```
* `lv_str_replace_end`  
	Replaces the last occurrence of the given value only if the value appears at the end of the string:
	```
	$replaced=lv_str_replace_end('World', 'Laravel', 'Hello World');
	// Hello Laravel
	$replaced=lv_str_replace_end('Hello', 'Laravel', 'Hello World');
	// Hello World
	```
	**Warning:**  
	lv_str_replace_last function is required  
	lv_str_starts_with function is required
* `lv_str_replace_start`  
	Replaces the first occurrence of the given value only if the value appears at the start of the string:
	```
	$replaced=lv_str_replace_start('Hello', 'Laravel', 'Hello World');
	// Laravel World
	$replaced=lv_str_replace_start('World', 'Laravel', 'Hello World');
	// Hello World
	```
	**Warning:**  
	lv_str_replace_first function is required  
	lv_str_starts_with function is required
* `lv_str_reverse`  
	Reverses the given string:
	```
	$reversed=lv_str_reverse('Hello World'); // dlroW olleH
	```
	**Warning:**  
	mbstring extension is required
* `lv_str_slug`  
	Generates a URL friendly "slug" from the given string:
	```
	$slug=lv_str_slug('Laravel 5 Framework', '-');
	// laravel-5-framework
	```
	**Warning:**  
	mbstring extension is required
* `lv_str_starts_with`  
	Determines if the given string begins with the given value:
	```
	$result=lv_str_starts_with('This is my name', 'This');
	// true
	```
	If an array of possible values is passed, the startsWith method will return true if the string begins with any of the given values:
	```
	$result=lv_str_starts_with('This is my name', ['This', 'That', 'There']);
	// true
	```
* `lv_str_ulid`  
	Generates a ULID, which is a compact, time-ordered unique identifier:
	```
	return (string)lv_str_ulid();
	// 01gd6r360bp37zj17nxb55yv40
	```
* `lv_str_uuid`  
	Generates a UUID (version 4):
	```
	return (string)lv_str_uuid();
	```

## Array helpers
* `lv_arr_is_assoc`  
	Returns true if the given array is an associative array.  
	An array is considered "associative" if it doesn't have sequential numerical keys beginning with zero:
	```
	$is_assoc=lv_arr_is_assoc(['product'=>['name'=>'Desk', 'price'=>100]]);
	// true
	$is_assoc=lv_arr_is_assoc([1, 2, 3]);
	// false
	```
* `lv_arr_is_list`  
	Returns true if the given array's keys are sequential integers beginning from zero:
	```
	$is_list=lv_arr_is_list(['foo', 'bar', 'baz']);
	// true
	$is_list=lv_arr_is_list(['product'=>['name'=>'Desk', 'price'=>100]]);
	// false
	```
* `lv_arr_sort_recursive`  
	Recursively sorts an array using the sort function for numerically indexed sub-arrays and the ksort function for associative sub-arrays:
	```
	$array=[
		['Roman', 'Taylor', 'Li'],
		['PHP', 'Ruby', 'JavaScript'],
		['one'=>1, 'two'=>2, 'three'=>3]
	];
	$sorted=lv_arr_sort_recursive($array);
	// [
	//  ['JavaScript', 'PHP', 'Ruby'],
	//  ['one'=>1, 'three'=>3, 'two'=>2],
	//  ['Li', 'Roman', 'Taylor']
	// ]
	```
	if you would like the results sorted in descending order, you may use the lv_arr_sort_recursive_desc function
	```
	$sorted=lv_arr_sort_recursive_desc($array);
	```
* `lv_arr_sort_recursive_desc`  
	**Warning:**  
	lv_arr_sort_recursive function is required
* `lv_arr_to_css_styles`  
	Conditionally compiles a CSS style string.  
	The method accepts an array of classes where the array key contains the class or classes you wish to add, while the value is a boolean expression.  
	If the array element has a numeric key, it will always be included in the rendered class list:
	```
	$has_color=true;
	$array=['background-color: blue', 'color: blue'=>$has_color];
	$classes=lv_arr_to_css_styles($array);
	// 'background-color: blue; color: blue;'
	```
	**Warning:**  
	lv_arr_wrap function is required  
	lv_str_finish function is required
* `lv_hlp_collect`  
	A complementary function to `lv_arr_collect`  
	Returns an instance of `lv_hlp_collection`
* `lv_hlp_is_assoc`  
	Alias to `lv_arr_is_assoc` function
* `lv_hlp_is_list`  
	Alias to `lv_arr_is_list` function
* `lv_hlp_sort`  
	A complementary function to `lv_arr_sort`  
	**Warning:**  
	lv_hlp_collection class is required
* `lv_hlp_sort_desc`  
	A complementary function to `lv_arr_sort_desc`  
	**Warning:**  
	lv_hlp_collection class is required
* `lv_hlp_sort_recursive`  
	Alias to `lv_arr_sort_recursive` function
* `lv_hlp_sort_recursive_desc`  
	Alias to `lv_arr_sort_recursive_desc` function
* `lv_hlp_lazy_collect`  
	A complementary function to `lv_arr_lazy_collect`  
	Returns an instance of `lv_hlp_lazy_collection`
* `lv_hlp_to_css_styles`  
	Alias to `lv_arr_to_css_styles` function

## Collections
Component extends collection classes:  
`lv_arr_collection` to `lv_hlp_collection`  
and `lv_arr_lazy_collection` to `lv_hlp_lazy_collection`
* `lv_hlp_collection`
	* `lazy`  
		Returns an instance of `lv_hlp_lazy_collection`
* `lv_hlp_lazy_collection`
	* **[protected]** `chunk_while_collection`  
		Returns an instance of `lv_hlp_collection`
* new methods (for both)
	* `dd`  
		Dumps the collection's items and ends execution of the script:
		```
		$collection=lv_hlp_collect(['John Doe', 'Jane Doe']);
		$collection->dd();
		// Collection {
		//  #items: array:2 [
		//   0 => "John Doe"
		//   1 => "Jane Doe"
		//  ]
		// }
		```
		**Note:**  
		if you do not want to stop executing the script, use the dump method instead  
		**Warning:**  
		dump method is required
	* `dump`  
		Dumps the collection's items:
		```
		$collection=lv_hlp_collect(['John Doe', 'Jane Doe']);
		$collection->dump();
		// Collection {
		//  #items: array:2 [
		//   0 => "John Doe"
		//   1 => "Jane Doe"
		//  ]
		// }
		```
		**Note:**  
		if you want to stop executing the script after dumping the collection, use the dd method instead  
		**Warning:**  
		symfony/var-dumper package is required
	* `ensure`  
		May be used to verify that all elements of a collection are of a given type or list of types:
		```
		return $collection->ensure(User::class);
		return $collection->ensure([User::class, Customer::class]);
		```
		Primitive types such as string, int, float, bool, and array may also be specified:
		```
		return $collection->ensure('int');
		```
		**Warning:**  
		does not guarantee that elements of different types  
		    will not be added to the collection at a later time  
		each method is required
	* `macro`  
		Collections are "macroable", which allows you to add additional methods to the `lv_hlp_collection` class at run time.  
		The `lv_hlp_collection` class' macro method accepts a closure that will be executed when your macro is called.  
		The macro closure may access the collection's other methods via `$this`, just as if it were a real method of the collection class.  
		For example, the following code adds a to_upper method to the `lv_hlp_collection` class:
		```
		lv_hlp_collection::macro('to_upper', function(){
			return $this->map(function(string $value){
				return strtoupper($value);
			});
		});
		$collection=lv_hlp_collect(['first', 'second']);
		$upper=$collection->to_upper();
		// ['FIRST', 'SECOND']
		```
		If necessary, you may define macros, that accept additional arguments:
		```
		lv_hlp_collection::macro('to_locale', function(string $locale){
			return $this->map(function(string $value) use($locale){
				return lang::get($value, [], $locale);
			});
		});
		$collection=lv_hlp_collect(['first', 'second']);
		$translated=$collection->to_locale('es');
		```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.  
Also you can copy tests to the `./lib/tests` and tools to the `./bin` directory.

## Sources
[Traits/Dumpable.php](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Dumpable.php)
