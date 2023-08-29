<?php
	class find_php_definitions_exception extends Exception {}
	function find_php_definitions(string $source)
	{
		/*
		 * Look up the definition of functions,
		 * classes, interfaces and traits in the source code
		 *
		 * Warning:
		 *  supports namespaces without open-close braces
		 *  tokenizer extension is required
		 *
		 * Note:
		 *  throws an find_php_definitions_exception on error
		 *
		 * Usage:
		 *  find_php_definitions(file_get_contents('file.php'))
		 *  returns array('classes'=>array, 'functions'=>array, 'interfaces'=>array, 'traits'=>array)
		 *
		 * Source:
		 *  https://stackoverflow.com/questions/2197851/function-list-of-php-file/8728411
		 */

		if(!extension_loaded('tokenizer'))
			throw new find_php_definitions_exception('tokenizer extension is not loaded');

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
					if($next_string_is_namespace)
					{
						$current_namespace='\\'.$token[1].'\\';
						$next_string_is_namespace=false;
					}
					else if($next_string_is_interface)
					{
						if(in_array($current_namespace.$token[1], $return_array['classes']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in classes');
						if(in_array($current_namespace.$token[1], $return_array['interfaces']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in interfaces');
						if(in_array($current_namespace.$token[1], $return_array['traits']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in traits');

						$return_array['interfaces'][]=$current_namespace.$token[1];
						$next_string_is_interface=false;
					}
					else if($next_string_is_trait)
					{
						if(in_array($current_namespace.$token[1], $return_array['classes']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in classes');
						if(in_array($current_namespace.$token[1], $return_array['interfaces']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in interfaces');
						if(in_array($current_namespace.$token[1], $return_array['traits']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in traits');

						$return_array['traits'][]=$current_namespace.$token[1];
						$next_string_is_trait=false;
					}
					else if($next_string_is_class)
					{
						if(in_array($current_namespace.$token[1], $return_array['classes']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in classes');
						if(in_array($current_namespace.$token[1], $return_array['interfaces']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in interfaces');
						if(in_array($current_namespace.$token[1], $return_array['traits']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in traits');

						$return_array['classes'][]=$current_namespace.$token[1];
						$next_string_is_class=false;
					}
					else if($next_string_is_function)
					{
						if(in_array($current_namespace.$token[1], $return_array['functions']))
							throw new find_php_definitions_exception($current_namespace.$token[1].' already exists in functions');

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