<?php
	/*
	 * Error polyfill
	 */

	if(!class_exists('Error'))
	{
		class Error extends Exception {}
	}

	/*
	 * ValueError polyfill
	 */

	if(!class_exists('ValueError'))
	{
		class ValueError extends Error {}
	}

	/*
	 * json_validate() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php83/blob/1.x/Php83.php
	 * License: MIT
	 */

	if(!function_exists('json_validate'))
	{
		function json_validate(string $json, int $depth=512, int $flags=0)
		{
			if(
				($flags !== 0) &&
				defined('JSON_INVALID_UTF8_IGNORE') &&
				($flags !== JSON_INVALID_UTF8_IGNORE)
			)
				throw new ValueError(__METHOD__.'(): Argument #3 ($flags) must be a valid flag (allowed flags: JSON_INVALID_UTF8_IGNORE)');

			if($depth <= 0)
				throw new ValueError(__METHOD__.'(): Argument #2 ($depth) must be greater than 0');

			if($depth > 0x7FFFFFFF /* JSON_MAX_DEPTH */)
				throw new ValueError(sprintf(__METHOD__.'(): Argument #2 ($depth) must be less than %d', 0x7FFFFFFF));

			json_decode($json, null, $depth, $flags);

			return (json_last_error() === JSON_ERROR_NONE);
		}
	}
?>