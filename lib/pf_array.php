<?php
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