<?php
	/*
	 * str_contains() polyfill
	 *
	 * Sources:
	 *  https://www.php.net/manual/en/function.str-starts-with.php
	 *  https://github.com/symfony/polyfill/blob/1.x/src/Php80/Php80.php
	 * License: MIT
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
	 * str_ends_with() polyfill
	 *
	 * Sources:
	 *  https://www.php.net/manual/en/function.str-starts-with.php
	 *  https://github.com/symfony/polyfill/blob/1.x/src/Php80/Php80.php
	 * License: MIT
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