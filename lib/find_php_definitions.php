<?php
	class find_php_definitions_exception extends Exception {}
	function find_php_definitions(string $source, bool $ignore_errors=false)
	{
		/*
		 * Look up the definition of functions,
		 * classes, interfaces and traits in the source code
		 *
		 * Warning:
		 *  tokenizer extension is required
		 *
		 * Note:
		 *  supports namespaces without open-close braces
		 *  throws an find_php_definitions_exception on error
		 *
		 * Usage:
			$tokens=find_php_definitions(file_get_contents('file.php'));
			$tokens=find_php_definitions(file_get_contents('file.php'), false); // disable "already exists" errors (only the first hit will be qualified)
		 *  returns array('classes'=>array, 'functions'=>array, 'interfaces'=>array, 'traits'=>array)
		 *
		 * Source:
		 *  https://stackoverflow.com/questions/2197851/function-list-of-php-file/8728411
		 */

		if(!function_exists('token_get_all'))
			throw new find_php_definitions_exception(
				'tokenizer extension is not loaded'
			);

		$return_array=[
			'classes'=>[],
			'functions'=>[],
			'interfaces'=>[],
			'traits'=>[]
		];

		$next_string_is_namespace=false;
		$next_string_is_interface=false;
		$next_string_is_class=false;
		$next_string_is_trait=false;
		$next_string_is_function=false;
		$is_in_class=false;
		$braces_count=0;
		$current_namespace='';
		$check_return_array=function($token) use($ignore_errors, &$return_array)
		{
			if($ignore_errors)
				return;

			if(in_array(
				$token,
				$return_array['classes']
			))
				throw new find_php_definitions_exception(
					$token.' already exists in classes'
				);

			if(in_array(
				$token,
				$return_array['interfaces']
			))
				throw new find_php_definitions_exception(
					$token.' already exists in interfaces'
				);

			if(in_array(
				$token,
				$return_array['traits']
			))
				throw new find_php_definitions_exception(
					$token.' already exists in traits'
				);
		};

		foreach(token_get_all($source) as $token)
			switch($token[0])
			{
				case T_NAMESPACE:
					$next_string_is_namespace=true;
				break;
				case T_INTERFACE:
					$next_string_is_interface=true;
					$is_in_class=true;
				break;
				case T_TRAIT:
					$next_string_is_trait=true;
					$is_in_class=true;
				break;
				case T_CLASS:
					$next_string_is_class=true;
					$is_in_class=true;
				break;
				case T_FUNCTION:
					if(!$is_in_class)
						$next_string_is_function=true;
				break;
				case T_STRING:
					switch(true)
					{
						case $next_string_is_namespace:
							$current_namespace='\\'.$token[1].'\\';
							$next_string_is_namespace=false;
						break;
						case $next_string_is_interface:
							$check_return_array($current_namespace.$token[1]);

							$return_array['interfaces'][]=$current_namespace.$token[1];
							$next_string_is_interface=false;
						break;
						case $next_string_is_trait:
							$check_return_array($current_namespace.$token[1]);

							$return_array['traits'][]=$current_namespace.$token[1];
							$next_string_is_trait=false;
						break;
						case $next_string_is_class:
							$check_return_array($current_namespace.$token[1]);

							$return_array['classes'][]=$current_namespace.$token[1];
							$next_string_is_class=false;
						break;
						case $next_string_is_function:
							if(
								in_array(
									$current_namespace.$token[1],
									$return_array['functions']
								) &&
								(!$ignore_errors)
							)
								throw new find_php_definitions_exception(
									$current_namespace.$token[1].' already exists in functions'
								);

							$return_array['functions'][]=$current_namespace.$token[1];
							$next_string_is_function=false;
					}
				break;
				case '(':
				case ';':
					// anonymous functions
					$next_string_is_namespace=false;
					$next_string_is_interface=false;
					$next_string_is_class=false;
					$next_string_is_trait=false;
					$next_string_is_function=false;
				break;
				case T_CURLY_OPEN:
				case '{':
					// anonymous classes (return new class {})
					if($next_string_is_class)
						$next_string_is_class=false;

					if($is_in_class)
						++$braces_count;
				break;
				case '}':
					if($is_in_class)
					{
						--$braces_count;

						if($braces_count === 0)
							$is_in_class=false;
					}
			}

		return $return_array;
	}
?>