<?php
	/*
	 * str_contains() polyfill
	 *
	 * Sources:
	 *  https://www.php.net/manual/en/function.str-starts-with.php
	 *  https://github.com/symfony/polyfill/blob/1.x/src/Php80/Php80.php
	 * License: MIT https://github.com/symfony/polyfill/blob/1.x/LICENSE
	 */

	if(!function_exists('str_contains'))
	{
		function str_contains(string $haystack, string $needle)
		{
			if($needle === '')
				return true;

			if(function_exists('mb_strpos'))
				return (mb_strpos($haystack, $needle) !== false);

			return (strpos($haystack, $needle) !== false);
		}
	}

	/*
	 * str_decrement() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php83/blob/1.x/Php83.php
	 * License: MIT https://github.com/symfony/polyfill/blob/1.x/LICENSE
	 */

	if(!function_exists('str_decrement'))
	{
		function str_decrement(string $string)
		{
			if($string === '')
				throw new ValueError('str_decrement(): Argument #1 ($string) cannot be empty');

			if(!preg_match('/^[a-zA-Z0-9]+$/', $string))
				throw new ValueError('str_decrement(): Argument #1 ($string) must be composed only of alphanumeric ASCII characters');

			if(preg_match('/\A(?:0[aA0]?|[aA])\z/', $string))
				throw new ValueError(sprintf(
					'str_decrement(): Argument #1 ($string) "%s" is out of decrement range',
					$string
				));

			if(!in_array(
				substr($string, -1),
				['A', 'a', '0'],
				true
			))
				return implode('',
					array_slice(str_split($string), 0, -1))
					.chr(
						ord(
							substr($string, -1)
						)-1
					);

			$carry='';
			$decremented='';

			for($i=strlen($string)-1; $i>=0; --$i)
			{
				$char=$string[$i];

				switch($char)
				{
					case 'A':
						if($carry !== '')
						{
							$decremented=$carry.$decremented;
							$carry='';
						}

						$carry='Z';
					break;
					case 'a':
						if($carry !== '')
						{
							$decremented=$carry.$decremented;
							$carry='';
						}

						$carry='z';
					break;
					case '0':
						if($carry !== '')
						{
							$decremented=$carry.$decremented;
							$carry='';
						}

						$carry='9';
					break;
					case '1':
						if($carry !== '')
						{
							$decremented=$carry.$decremented;
							$carry='';
						}

						break;
					default:
						if($carry !== '')
						{
							$decremented=$carry.$decremented;
							$carry='';
						}

						if(!in_array(
							$char,
							['A', 'a', '0'],
							true
						))
							$decremented=chr(ord($char)-1).$decremented;
				}
			}

			return $decremented;
		}
	}

	/*
	 * str_ends_with() polyfill
	 *
	 * Sources:
	 *  https://www.php.net/manual/en/function.str-starts-with.php
	 *  https://github.com/symfony/polyfill/blob/1.x/src/Php80/Php80.php
	 * License: MIT https://github.com/symfony/polyfill/blob/1.x/LICENSE
	 */

	if(!function_exists('str_ends_with'))
	{
		function str_ends_with(string $haystack, string $needle)
		{
			if($haystack === '')
				return false;

			if(($needle === '') || ($haystack === $needle))
				return true;

			return (substr($haystack, -strlen($needle)) === $needle);
		}
	}

	/*
	 * str_increment() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php83/blob/1.x/Php83.php
	 * License: MIT https://github.com/symfony/polyfill/blob/1.x/LICENSE
	 */

	if(!function_exists('str_increment'))
	{
		function str_increment(string $string)
		{
			if($string === '')
				throw new ValueError('str_increment(): Argument #1 ($string) cannot be empty');

			if(!preg_match('/^[a-zA-Z0-9]+$/', $string))
				throw new ValueError('str_increment(): Argument #1 ($string) must be composed only of alphanumeric ASCII characters');

			if(is_numeric($string))
			{
				$offset=stripos($string, 'e');

				if($offset !== false)
				{
					$char=$string[$offset];
					++$char;

					$string[$offset]=$char;
					++$string;

					switch($string[$offset])
					{
						case 'f':
							$string[$offset]='e';
						break;
						case 'F':
							$string[$offset]='E';
						break;
						case 'g':
							$string[$offset]='f';
						break;
						case 'G':
							$string[$offset]='F';
					}

					return $string;
				}
			}

			return ++$string;
		}
	}

	/*
	 * str_starts_with() polyfill
	 *
	 * Source: https://www.php.net/manual/en/function.str-starts-with.php
	 */

	if(!function_exists('str_starts_with'))
	{
		function str_starts_with(string $haystack, string $needle)
		{
			if($needle === '')
				return true;

			return (strncmp($haystack, $needle, strlen($needle)) === 0);
		}
	}
?>