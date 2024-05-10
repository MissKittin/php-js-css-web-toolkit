<?php
	/*
	 * Laravel 10 string helpers
	 *
	 * Note:
	 *  throws an lv_str_exception on error
	 *
	 * Implemented functions:
	 *  preg_replace_array(string_pattern, array_replacements, string_subject)
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
	 *  lv_str_random()
	 *   generates a random string of the specified length
	 *   this function uses PHP's random_bytes function
			$random=lv_str_random(40);
	 *  lv_str_repeat()
	 *   repeats the given string
			$string='a';
			$repeat=lv_str_repeat($string, 5); // aaaaa
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
	 *  lv_str_snake(string_valuem string_delimiter)
	 *   converts the given string to snake_case
			$converted=lv_str_snake('fooBar'); // foo_bar
			$converted=lv_str_snake('fooBar', '-'); // foo-bar
	 *   warning:
	 *    ctype extension is required
	 *    mbstring extension is required
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
			lv_str_word_wrap($text, 20, "<br />\n");
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
			lv_str_wrap('Laravel', '"'); // "Laravel"
			lv_str_wrap('is', 'This ', ' Laravel!'); // This is Laravel!
	 *
	 * Functions implemented in the lv_hlp component:
	 *  lv_str_ascii()
	 *  lv_str_contains()
	 *  lv_str_contains_all()
	 *  lv_str_ends_with()
	 *  lv_str_inline_markdown()
	 *  lv_str_is_ascii()
	 *  lv_str_is_json()
	 *  lv_str_is_ulid()
	 *  lv_str_is_uuid()
	 *  lv_str_markdown()
	 *  lv_str_ordered_uuid()
	 *  lv_str_password()
	 *  lv_str_remove()
	 *  lv_str_replace()
	 *  lv_str_replace_array()
	 *  lv_str_replace_end()
	 *  lv_str_replace_start()
	 *  lv_str_reverse()
	 *  lv_str_slug()
	 *  lv_str_starts_with()
	 *  lv_str_ulid()
	 *  lv_str_uuid()
	 *
	 * Not implemented functions:
	 *  lv_str_excerpt()
	 *  lv_str_plural()
	 *  lv_str_plural_studly()
	 *  lv_str_singular()
	 *  lv_str_to_html_string()
	 *
	 * Sources:
	 *  https://laravel.com/docs/10.x/helpers
	 *  https://github.com/illuminate/support/blob/master/Str.php
	 *  https://github.com/laravel/framework/blob/10.x/src/Illuminate/Support/helpers.php
	 * License: MIT
	 */

	class lv_str_exception extends Exception {}

	if(!function_exists('preg_replace_array'))
	{
		function preg_replace_array(
			string $pattern,
			array $replacements,
			string $subject
		){
			return preg_replace_callback(
				$pattern,
				function() use(&$replacements){
					foreach($replacements as $value)
						return array_shift($replacements);
				},
				$subject
			);
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
	function lv_str_repeat(string $string, int $times)
	{
		return str_repeat($string, $times);
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
		function lv_str_before_last(string $subject, string $search)
		{
			if($search === '')
				return $subject;

			$pos=mb_strrpos($subject, $search);

			if($pos === false)
				return $subject;

			return mb_substr($subject, 0, $pos, 'UTF-8');
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

			return rtrim(mb_strimwidth(
				$value, 0, $limit, '', 'UTF-8'
			)).$end;
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
				if($index < -$strlen)
					$start_index=0;
				else
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
			return
				mb_strtoupper(
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
		else
		{
			function lv_str_snake()
			{
				throw new lv_str_exception('ctype extension is not loaded');
			}
		}
	}
	else /* some boilerplate */
	{
		function lv_str_before_last()
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
?>