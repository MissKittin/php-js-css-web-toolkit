<?php
	/*
	 * get_debug_type() polyfill
	 *
	 * Source:
	 *  https://php.watch/versions/8.0/get_debug_type
	 */

	if(!function_exists('get_debug_type'))
	{
		function get_debug_type($value)
		{
			switch(true)
			{
				case ($value === null):
					return 'null';
				case is_bool($value):
					return 'bool';
				case is_string($value):
					return 'string';
				case is_array($value):
					return 'array';
				case is_int($value):
					return 'int';
				case is_float($value):
					return 'float';
				case is_object($value):
				break;
				case ($value instanceof __PHP_Incomplete_Class):
				case (substr(var_export($value, true), 0, 24) === '__PHP_Incomplete_Class::'): // PHP 7.1
					return '__PHP_Incomplete_Class';
				default:
						$type=get_resource_type($value);

						if($type === null)
							return 'unknown';

						if($type === 'Unknown')
							$type='closed';

						return 'resource ('.$type.')';
			}

			$class=get_class($value);

			if(strpos($class, '@') === false)
				return $class;

			$return=get_parent_class($class);

			if($return !== false)
				return $return.'@anonymous';

			$return=key(class_implements($class));

			if($return !== null)
				return $return.'@anonymous';

			return 'class@anonymous';
		}
	}
?>