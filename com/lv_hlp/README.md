# Laravel helpers
Extension of `lv_arr.php` and `lv_str.php` libraries  
This file only describes new and modified functions/methods  
The rest of the documentation is located in the `lv_arr.php` and `lv_str.php` libraries  
  
**This component is licensed under the MIT License, see [LICENSE.md](https://github.com/laravel/framework/blob/11.x/LICENSE.md)**

## Required libraries
* `ascii.php`
* `lv_arr.php`
* `lv_macroable.php`
* `lv_str.php`
* `ocw_slugify.php`
* `ulid.php`
* `uuid.php`
* `pf_json_validate.php` (for PHP older than 8.3)
* `pf_get_debug_type.php` (for PHP older than 8.0)
* `pf_ValueError.php` (for PHP older than 8.0)
* `pf_mbstring.php` (for PHP older than 7.4)
* `pf_is_countable.php` (for PHP older than 7.3)
* `has_php_close_tag.php` (for tests)
* `include_into_namespace.php` (for tests)
* `var_export_contains.php` (for tests)
* `bin/get-composer.php` (for tests)
* `tests/lv_arr.php` (for tests)
* `curl_file_updown.php` (for `get-composer.php` tool)

## Suggested extensions
* `intl`
* `mbstring`

## Suggested packages
* `doctrine/inflector`
* `league/commonmark`
* `nesbot/carbon`
* `symfony/var-dumper` (for development)
```
php composer.phar --optimize-autoloader require doctrine/inflector league/commonmark nesbot/carbon
php composer.phar --optimize-autoloader require --dev symfony/var-dumper
```
composer.json:
```
{
    "require": {
        "doctrine/inflector": "*",
        "league/commonmark": "*",
        "nesbot/carbon": "*"
    },
    "require-dev": {
        "symfony/var-dumper": "*"
    },
    "config": {
        "optimize-autoloader": true
    }
}

```

## Note
Throws an `lv_hlp_exception` on error

## Usage
Just include the component:
```
require './com/lv_hlp/main.php';
```

## Hint
The names of the functions and classes are quite long. To shorten them:
```
use lv_hlp_collection as collection;
use lv_hlp_pluralizer as pluralizer;
use function lv_str_kebab as kebab;
```

## String helpers
* `lv_hlp_of`  
	Get a new stringable object from the given string  
	See the `lv_hlp_ingable` class for more info  
	**Warning:**  
	`lv_hlp_ingable` class is required
* `lv_hlp_match_all`  
	Returns a collection containing the portions of a string that match a given regular expression pattern:

		$result=lv_hlp_match_all('/bar/', 'bar foo bar');
		// lv_hlp_collect(['bar', 'bar'])

	If you specify a matching group within the expression, `lv_hlp_match_all` will return a collection of that group's matches:

		$result=lv_hlp_match_all('/f(\w*)/', 'bar fun bar fly');
		// lv_hlp_collect(['un', 'ly'])

	**Warning:**  
	`lv_hlp_collect` function is required
* `lv_hlp_str`  
	Get a new stringable object from the given string  
	**Warning:**  
	`lv_hlp_of` function is required
* `lv_str_ascii` `lv_hlp_ascii`  
	Will attempt to transliterate the string into an ASCII value:

		$ascii=lv_str_ascii('ü');
		// u

* `lv_str_inline_markdown` `lv_hlp_inline_markdown`  
	Converts GitHub flavored Markdown into inline HTML using CommonMark.  
	However, unlike the `lv_str_markdown` function, it does not wrap all generated HTML in a block-level element:

		$html=lv_str_inline_markdown('**Laravel**');
		// <strong>Laravel</strong>

	**Markdown Security**  
	By default, Markdown supports raw HTML, which will expose Cross-Site Scripting (XSS) vulnerabilities when used with raw user input.  
	As per the CommonMark Security documentation, you may use the `html_input` option to either escape or strip raw HTML,  
	and the `allow_unsafe_links` option to specify whether to allow unsafe links.  
	If you need to allow some raw HTML, you should pass your compiled Markdown through an HTML Purifier:

		lv_str_inline_markdown('Inject: <script>alert("Hello XSS!");</script>', [
			'html_input'=>'strip',
			'allow_unsafe_links'=>false
		]);
		// Inject: alert(&quot;Hello XSS!&quot;);

	**Note:**  
	tested on CommonMark 1.6.7 and 2.5.3  
	**Warning:**  
	`league/commonmark` package is required
* `lv_str_is_ascii` `lv_hlp_is_ascii`  
	Determines if a given string is 7 bit ASCII:

		$is_ascii=lv_str_is_ascii('Taylor'); // true
		$is_ascii=lv_str_is_ascii('ü'); // false

	**Warning:**  
	`intl` extension is required
* `lv_str_is_json` `lv_hlp_is_json`  
	Determines if the given string is valid JSON:

		$result=lv_str_is_json('[1,2,3]'); // true
		$result=lv_str_is_json('{"first": "John", "last": "Doe"}'); // true
		$result=lv_str_is_json('{first: "John", last: "Doe"}'); // false

* `lv_str_is_ulid` `lv_hlp_is_ulid`  
	Determines if the given string is a valid ULID:

		$is_ulid=lv_str_is_ulid('01gd6r360bp37zj17nxb55yv40'); // true
		$is_ulid=lv_str_is_ulid('laravel'); // false

* `lv_str_is_uuid` `lv_hlp_is_uuid`  
	Determines if the given string is a valid UUID:

		$is_uuid=lv_str_is_uuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de'); // true
		$is_uuid=lv_str_is_uuid('laravel'); // false

* `lv_str_markdown` `lv_hlp_markdown`  
	Converts GitHub flavored Markdown into HTML using CommonMark:

		$html=lv_str_markdown('# Laravel');
		// <h1>Laravel</h1>
		$html=lv_str_markdown('# Taylor <b>Otwell</b>', [
			'html_input'=>'strip'
		]);
		// <h1>Taylor Otwell</h1>

	**Markdown Security**  
	By default, Markdown supports raw HTML, which will expose Cross-Site Scripting (XSS) vulnerabilities when used with raw user input.  
	As per the CommonMark Security documentation, you may use the `html_input` option to either escape or strip raw HTML,  
	and the `allow_unsafe_links` option to specify whether to allow unsafe links.  
	If you need to allow some raw HTML, you should pass your compiled Markdown through an HTML Purifier:

		lv_str_markdown('Inject: <script>alert("Hello XSS!");</script>', [
			'html_input'=>'strip',
			'allow_unsafe_links'=>false
		]);
		// Inject: alert(&quot;Hello XSS!&quot;);

	**Note:**  
	tested on CommonMark 1.6.7 and 2.5.3  
	**Warning:**  
	`league/commonmark` package is required
* `lv_str_ordered_uuid` `lv_hlp_ordered_uuid`  
	Generates a "timestamp first" UUID that may be efficiently stored in an indexed database column.  
	Each UUID that is generated using this method will be sorted after UUIDs previously generated using the method:

		return (string)lv_str_ordered_uuid();

* `lv_str_password` `lv_hlp_password`  
	May be used to generate a secure, random password of a given length.  
	The password will consist of a combination of letters, numbers, symbols, and spaces.  
	By default, passwords are 32 characters long:

		$password=lv_str_password(); // 'EbJo2vE-AS:U,$%_gkrV4n,q~1xy/-_4'
		$password=lv_str_password(12); // 'qwuar>#V|i]N'

	**Warning:**  
	`lv_hlp_collection` class is required
* `lv_str_plural` `lv_hlp_plural`  
	Converts a singular word string to its plural form.  
	This function supports any of the languages supported by Doctrine Inflector:

		$plural=lv_str_plural('car'); // cars
		$plural=lv_str_plural('child'); // children

	You may provide an integer as a second argument to the function to retrieve the singular or plural form of the string:

		$plural=lv_str_plural('child', 2); // children
		$singular=lv_str_plural('child', 1); // child

	You can also specify the language and list of uncountable words:

		$plural=lv_str_plural('child', 2, 'english', ['recommended', 'related']); // children
		$singular=lv_str_plural('child', 1, 'english', ['recommended', 'related']); // child

	**Note:**  
	tested on Inflector 1.4.4 and 2.0.10  
	**Warning:**  
	`mbstring` extension is required  
	`doctrine/inflector` package is required
* `lv_str_plural_studly` `lv_hlp_plural_studly`  
	Converts a singular word string formatted in studly caps case to its plural form.  
	This function supports any of the languages supported by Doctrine Inflector:

		$plural=lv_str_plural_studly('VerifiedHuman'); // VerifiedHumans
		$plural=lv_str_plural_studly('UserFeedback'); // UserFeedback

	You may provide an integer as a second argument to the function to retrieve the singular or plural form of the string:

		$plural=lv_str_plural_studly('VerifiedHuman', 2); // VerifiedHumans
		$singular=lv_str_plural_studly('VerifiedHuman', 1); // VerifiedHuman

	You can also specify the language and list of uncountable words:

		$plural=lv_str_plural_studly('VerifiedHuman', 2, 'english', ['recommended', 'related']); // VerifiedHumans
		$singular=lv_str_plural_studly('VerifiedHuman', 1, 'english', ['recommended', 'related']); // VerifiedHuman

	**Note:**  
	tested on Inflector 1.4.4 and 2.0.10  
	**Warning:**  
	`lv_str_plural` function is required
* `lv_str_singular` `lv_hlp_singular`  
	Converts a string to its singular form.  
	This function supports any of the languages supported by Doctrine Inflector:

		$singular=lv_str_singular('cars'); // car
		$singular=lv_str_singular('children'); // child

	You can also specify the language:

		$singular=lv_str_singular('cars', 'english'); // car
		$singular=lv_str_singular('children', 'english'); // child

	**Note:**  
	tested on Inflector 1.4.4 and 2.0.10  
	**Warning:**  
	`mbstring` extension is required  
	`doctrine/inflector` package is required
* `lv_str_slug` `lv_hlp_slug`  
	Generates a URL friendly "slug" from the given string:

		$slug=lv_str_slug('Laravel 5 Framework', '-');
		// laravel-5-framework

	**Warning:**  
	`mbstring` extension is required
* `lv_str_ulid` `lv_hlp_ulid`  
	Generates a ULID, which is a compact, time-ordered unique identifier:

		return (string)lv_str_ulid();
		// 01gd6r360bp37zj17nxb55yv40

* `lv_str_uuid` `lv_hlp_uuid`  
	Generates a UUID (version 4):

		return (string)lv_str_uuid();


## Array helpers
* `lv_arr_to_css_styles` `lv_hlp_to_css_styles`  
	Conditionally compiles a CSS style string.  
	The function accepts an array of classes where the array key contains the class or classes you wish to add, while the value is a boolean expression.  
	If the array element has a numeric key, it will always be included in the rendered class list:

		$has_color=true;
		$array=['background-color: blue', 'color: blue'=>$has_color];
		$classes=lv_arr_to_css_styles($array);
		// 'background-color: blue; color: blue;'

	**Warning:**  
	`lv_arr_wrap` function is required  
	`lv_str_finish` function is required
* `lv_hlp_collect`  
	A complementary function to `lv_arr_collect`  
	Returns an instance of `lv_hlp_collection`
* `lv_hlp_sort`  
	A complementary function to `lv_arr_sort`  
	**Warning:**  
	`lv_hlp_collection` class is required
* `lv_hlp_sort_desc`  
	A complementary function to `lv_arr_sort_desc`  
	**Warning:**  
	`lv_hlp_collection` class is required
* `lv_hlp_lazy_collect`  
	A complementary function to `lv_arr_lazy_collect`  
	Returns an instance of `lv_hlp_lazy_collection`

## Pluralizer
If you need a better solution than single plural/singular functions, use the `lv_hlp_pluralizer` class:
```
// you can define uncountable words
lv_hlp_pluralizer::$uncountable=['recommended', 'related'];

// you can change the language
lv_hlp_pluralizer::use_language('english');

// just like lv_str_plural
$plural=lv_hlp_pluralizer::plural('car');
$plural=lv_hlp_pluralizer::plural('car', 2);
$singular=lv_hlp_pluralizer::plural('car', 1);

// just like lv_str_plural_studly
$plural_studly=lv_hlp_pluralizer::plural_studly('VerifiedHuman');
$plural_studly=lv_hlp_pluralizer::plural_studly('VerifiedHuman', 2);
$singular=lv_hlp_pluralizer::plural_studly('VerifiedHuman', 1);

// just like lv_str_singular
$singular=lv_hlp_pluralizer::singular('cars');

// you can get an instance of Inflector
$inflector=lv_hlp_pluralizer::inflector();
```
For more info, see `lv_str_plural`, `lv_str_plural_studly` and `lv_str_singular` [string helpers](#string-helpers).  
**Note:**  
tested on Inflector 1.4.4 and 2.0.10  
**Warning:**  
`mbstring` extension is required  
`doctrine/inflector` package is required

## Fluent strings
The component extends the `lv_str_ingable` class to `lv_hlp_ingable`  
* updated methods
	* `excerpt`  
		Uses `lv_hlp_excerpt()` instead of `lv_str_excerpt()`
	* `explode`  
		Splits the string by the given delimiter and returns a collection containing each section of the split string:

			$collection=lv_hlp_of('foo bar baz')->explode(' ');
			// lv_hlp_collect(['foo', 'bar', 'baz'])

		**Warning:**  
		`lv_hlp_collect` function is required
	* `match_all`  
		Returns a collection containing the portions of a string that match a given regular expression pattern:

			$result=lv_hlp_of('bar foo bar')->match_all('/bar/');
			// lv_hlp_collect(['bar', 'bar'])

		If you specify a matching group within the expression, `match_all` will return a collection of that group's matches:

			$result=lv_hlp_of('bar fun bar fly')->match_all('/f(\w*)/');
			// lv_hlp_collect(['un', 'ly'])

		**Warning:**  
		`lv_hlp_match_all` function is required
	* `scan`  
		Parses input from a string into a collection according to a format supported by the `sscanf` PHP function:

			$collection=lv_hlp_of('filename.jpg')->scan('%[^.].%s');
			// lv_hlp_collect(['filename', 'jpg'])

		**Warning:**  
		`lv_hlp_collect` function is required
	* `split`  
		Splits a string into a collection using a regular expression:

			$segments=lv_hlp_of('one, two, three')->split('/[\s,]+/');
			// lv_hlp_collect(['one', 'two', 'three'])

		**Warning:**  
		`mbstring` extension is required  
		`lv_hlp_collect` function is required
	* `ucsplit`  
		Splits the given string into a collection by uppercase characters:

			$string=lv_hlp_of('Foo Bar')->ucsplit();
			// lv_hlp_collect(['Foo', 'Bar'])

		**Warning:**  
		`lv_hlp_collect` function is required  
		`lv_str_ucsplit` function is required
* new methods
	* `ascii`  
		Transliterates the string into an ASCII value:

			$string=lv_hlp_of('ü')->ascii();
			// 'u'

		**Warning:**  
		`lv_str_ascii` function is required
	* `dd`  
		Dumps the stringable and ends execution of the script  
		For more info, see [Collections](#collections)  
		**Note:**  
		if you do not want to stop executing the script, use the `dump` method instead  
		**Warning:**  
		`dump` method is required
	* `dump`  
		Dumps the stringable  
		For more info, see [Collections](#collections)  
		**Note:**  
		if you want to stop executing the script after dumping the stringable, use the `dd` method instead  
		**Warning:**  
		`symfony/var-dumper` package is required
	* `inline_markdown`  
		Converts GitHub flavored Markdown into inline HTML using CommonMark.  
		However, unlike the `markdown` method, it does not wrap all generated HTML in a block-level element:

			$html=lv_hlp_of('**Laravel**')->inline_markdown();
			// <strong>Laravel</strong>

		**Markdown Security**  
		By default, Markdown supports raw HTML, which will expose Cross-Site Scripting (XSS) vulnerabilities when used with raw user input.  
		As per the CommonMark Security documentation, you may use the `html_input` option to either escape or strip raw HTML,  
		and the `allow_unsafe_links` option to specify whether to allow unsafe links.  
		If you need to allow some raw HTML, you should pass your compiled Markdown through an HTML Purifier:

			lv_hlp_of('Inject: <script>alert("Hello XSS!");</script>')->markdown([
				'html_input'=>'strip',
				'allow_unsafe_links'=>false
			]);
			// Inject: alert(&quot;Hello XSS!&quot;);

		**Note:**  
		tested on CommonMark 1.6.7 and 2.5.3  
		**Warning:**  
		`lv_str_inline_markdown` function is required
	* `is_ascii`  
		Determines if a given string is an ASCII string:

			$result=lv_hlp_of('Taylor')->is_ascii(); // true
			$result=lv_hlp_of('ü')->is_ascii(); // false

		**Warning:**  
		`lv_str_is_ascii` function is required
	* `is_json`  
		Determines if a given string is valid JSON:

			$result=lv_hlp_of('[1,2,3]')->is_json(); // true
			$result=lv_hlp_of('{"first": "John", "last": "Doe"}')->is_json(); // true
			$result=lv_hlp_of('{first: "John", last: "Doe"}')->is_json(); // false

		**Warning:**  
		`lv_str_is_json` function is required
	* `is_ulid`  
		Determines if a given string is a ULID:

			$result=lv_hlp_of('01gd6r360bp37zj17nxb55yv40')->is_ulid(); // true
			$result=lv_hlp_of('Taylor')->is_ulid(); // false

		**Warning:**  
		`lv_str_is_ulid` function is required
	* `is_uuid`  
		Determines if a given string is a UUID:

			$result=lv_hlp_of('5ace9ab9-e9cf-4ec6-a19d-5881212a452c')->is_uuid(); // true
			$result=lv_hlp_of('Taylor')->is_uuid(); // false

		**Warning:**  
		`lv_str_is_uuid` function is required
	* `macro`  
		Stringable is also "macroable", which allows you to add additional methods to the `lv_hlp_ingable` class at run time.  
		The `lv_hlp_ingable` class' macro method accepts a closure that will be executed when your macro is called.  
		The macro closure may access the collection's other methods via `$this`, just as if it were a real method of the stringable class.  
		For more info, see [Collections](#collections)
	* `markdown`  
		Converts GitHub flavored Markdown into HTML:

			$html=lv_hlp_of('# Laravel')->markdown();
			// <h1>Laravel</h1>
			$html=lv_hlp_of('# Taylor <b>Otwell</b>')->markdown([
				'html_input'=>'strip'
			]);
			// <h1>Taylor Otwell</h1>

		**Markdown Security**  
		By default, Markdown supports raw HTML, which will expose Cross-Site Scripting (XSS) vulnerabilities when used with raw user input.  
		As per the CommonMark Security documentation, you may use the `html_input` option to either escape or strip raw HTML,  
		and the `allow_unsafe_links` option to specify whether to allow unsafe links.  
		If you need to allow some raw HTML, you should pass your compiled Markdown through an HTML Purifier:

			lv_hlp_of('Inject: <script>alert("Hello XSS!");</script>')->markdown([
				'html_input'=>'strip',
				'allow_unsafe_links'=>false
			]);
			// Inject: alert(&quot;Hello XSS!&quot;);

		**Note:**  
		tested on CommonMark 1.6.7 and 2.5.3  
		**Warning:**  
		`lv_str_markdown` function is required
	* `plural`  
		Converts a singular word string to its plural form.  
		This function supports any of the languages support by Doctrine Inflector:

			$plural=lv_hlp_of('car')->plural(); // cars
			$plural=lv_hlp_of('child')->plural(); // children

		You may provide an integer as a second argument to the function to retrieve the singular or plural form of the string:

			$plural=lv_hlp_of('child')->plural(2); // children
			$plural=lv_hlp_of('child')->plural(1); // child

		**Note:**  
		tested on Inflector 1.4.4 and 2.0.10  
		**Warning:**  
		`lv_hlp_pluralizer` class is required
	* `plural_studly`  
		Converts a singular word string formatted in studly caps case to its plural form.  
		This function supports any of the languages support by Doctrine Inflector:

			$plural=lv_hlp_of('VerifiedHuman')->plural_studly(); // VerifiedHumans
			$plural=lv_hlp_of('UserFeedback')->plural_studly(); // UserFeedback

		You may provide an integer as a second argument to the function to retrieve the singular or plural form of the string:

			$plural=lv_hlp_of('VerifiedHuman')->plural_studly(2); // VerifiedHumans
			$singular=lv_hlp_of('VerifiedHuman')->plural_studly(1); // VerifiedHuman

		**Note:**  
		tested on Inflector 1.4.4 and 2.0.10  
		**Warning:**  
		`lv_hlp_pluralizer` class is required
	* `singular`  
		Converts a string to its singular form.  
		This function supports any of the languages support by Doctrine Inflector:

			$singular=lv_hlp_of('cars')->singular(); // car
			$singular=lv_hlp_of('children')->singular(); // child

		**Note:**  
		tested on Inflector 1.4.4 and 2.0.10  
		**Warning:**  
		`lv_hlp_pluralizer` class is required
	* `slug`  
		Generates a URL friendly "slug" from the given string:

			$slug=lv_hlp_of('Laravel Framework')->slug('-');
			// laravel-framework

		**Warning:**  
		`lv_str_slug` function is required
	* `to_date`  
		Get the underlying string value as a Carbon instance.

			lv_hlp_of('12-31-2001')->to_date('m-d-Y');

		**Note:**  
		tested on Carbon 1.39.1, 2.72.5 and 3.8.0  
		**Warning:**  
		`nesbot/carbon` package is required
	* `transliterate`  
		Alias for the `ascii` method  
		**Warning:**  
		`ascii` method is required
	* `when_is_ascii`  
		Invokes the given closure if the string is 7 bit ASCII.  
		The closure will receive the fluent string instance:

			$string=lv_hlp_of('laravel')->when_is_ascii(function(lv_hlp_ingable $string){
				return $string->title();
			});
			// 'Laravel'

		**Warning:**  
		`is_ascii` method is required  
		`when` method is required
	* `when_is_ulid`  
		Invokes the given closure if the string is a valid ULID.  
		The closure will receive the fluent string instance:

			$string=lv_hlp_of('01gd6r360bp37zj17nxb55yv40')->when_is_ulid(function(lv_hlp_ingable $string){
				return $string->substr(0, 8);
			});
			// '01gd6r36'

		**Warning:**  
		`is_ulid` method is required  
		`when` method is required
	* `when_is_uuid`  
		Invokes the given closure if the string is a valid UUID.  
		The closure will receive the fluent string instance:

			$string=lv_hlp_of('a0a2a2d2-0b87-4a18-83f2-2529882be2de')->when_is_uuid(function(lv_hlp_ingable $string){
				return $string->substr(0, 8);
			});
			// 'a0a2a2d2'

		**Warning:**  
		`is_uuid` method is required  
		`when` method is required

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

			$collection=lv_hlp_collect(['John Doe', 'Jane Doe']);
			$collection->dd();
			// Collection {
			//  #items: array:2 [
			//   0 => "John Doe"
			//   1 => "Jane Doe"
			//  ]
			// }

		**Note:**  
		if you do not want to stop executing the script, use the `dump` method instead  
		**Warning:**  
		`dump` method is required
	* `dump`  
		Dumps the collection's items:

			$collection=lv_hlp_collect(['John Doe', 'Jane Doe']);
			$collection->dump();
			// Collection {
			//  #items: array:2 [
			//   0 => "John Doe"
			//   1 => "Jane Doe"
			//  ]
			// }

		**Note:**  
		if you want to stop executing the script after dumping the collection, use the `dd` method instead  
		**Warning:**  
		`symfony/var-dumper` package is required
	* `ensure`  
		May be used to verify that all elements of a collection are of a given type or list of types:

			return $collection->ensure(User::class);
			return $collection->ensure([User::class, Customer::class]);

		Primitive types such as `string`, `int`, `float`, `bool`, and `array` may also be specified:

			return $collection->ensure('int');

		**Warning:**  
		does not guarantee that elements of different types  
		    will not be added to the collection at a later time  
		`each` method is required
	* `macro`  
		Collections are "macroable", which allows you to add additional methods to the `lv_hlp_collection` class at run time.  
		The `lv_hlp_collection` class' macro method accepts a closure that will be executed when your macro is called.  
		The macro closure may access the collection's other methods via `$this`, just as if it were a real method of the collection class.  
		For example, the following code adds a `to_upper` method to the `lv_hlp_collection` class:

			lv_hlp_collection::macro('to_upper', function(){
				return $this->map(function(string $value){
					return strtoupper($value);
				});
			});
			$collection=lv_hlp_collect(['first', 'second']);
			$upper=$collection->to_upper();
			// ['FIRST', 'SECOND']

		If necessary, you may define macros, that accept additional arguments:

			lv_hlp_collection::macro('to_locale', function(string $locale){
				return $this->map(function(string $value) use($locale){
					return lang::get($value, [], $locale);
				});
			});
			$collection=lv_hlp_collect(['first', 'second']);
			$translated=$collection->to_locale('es');


## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.  
Also you can copy tests to the `./lib/tests` and tools to the `./bin` directory.

## Sources
[Traits/Dumpable.php](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Dumpable.php)  
[Pluralizer.php](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Support/Pluralizer.php)
