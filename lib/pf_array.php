<?php
	/*
	 * array_all() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php84/blob/1.x/Php84.php
	 * License: MIT https://github.com/symfony/polyfill-php84/blob/1.x/LICENSE
	 */

	if(!function_exists('array_all'))
	{
		function array_all(array $array, callable $callback)
		{
			foreach($array as $key=>$value)
				if(!$callback($value, $key))
					return false;

			return true;
		}
	}

	/*
	 * array_any() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php84/blob/1.x/Php84.php
	 * License: MIT https://github.com/symfony/polyfill-php84/blob/1.x/LICENSE
	 */

	if(!function_exists('array_any'))
	{
		function array_any(array $array, callable $callback)
		{
			foreach($array as $key=>$value)
				if($callback($value, $key))
					return true;

			return false;
		}
	}

	/*
	 * array_find() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php84/blob/1.x/Php84.php
	 * License: MIT https://github.com/symfony/polyfill-php84/blob/1.x/LICENSE
	 */

	if(!function_exists('array_find'))
	{
		function array_find(array $array, callable $callback)
		{
			foreach($array as $key=>$value)
				if($callback($value, $key))
					return $value;

			return null;
		}
	}

	/*
	 * array_find_key() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php84/blob/1.x/Php84.php
	 * License: MIT https://github.com/symfony/polyfill-php84/blob/1.x/LICENSE
	 */

	if(!function_exists('array_find_key'))
	{
		function array_find_key(array $array, callable $callback)
		{
			foreach($array as $key=>$value)
				if($callback($value, $key))
					return $key;

			return $key;
		}
	}

	/*
	 * array_is_list() polyfill
	 *
	 * Source: https://www.php.net/manual/en/function.array-is-list.php
	 */

	if(!function_exists('array_is_list'))
	{
		function array_is_list(array $array)
		{
			$i=0;

			foreach($array as $key=>$value)
				if($key !== $i++)
					return false;

			return true;
		}
	}

	/*
	 * array_key_first() polyfill
	 *
	 * Source: https://www.php.net/manual/en/function.array-key-first.php
	 */

	if(!function_exists('array_key_first'))
	{
		function array_key_first(array $array)
		{
			foreach($array as $key=>$value)
				return $key;

			return null;
		}
	}

	/*
	 * array_key_last() polyfill
	 *
	 * Source: https://www.php.net/manual/en/function.array-key-last.php
	 */

	if(!function_exists('array_key_last'))
	{
		function array_key_last(array $array)
		{
			if(!empty($array))
				return key(array_slice($array, -1, 1, true));

			return null;
		}
	}
?>