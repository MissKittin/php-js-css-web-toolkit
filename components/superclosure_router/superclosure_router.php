<?php
	if(!class_exists('uri_router'))
	{
		if(file_exists(__DIR__.'/lib/uri_router.php'))
			include __DIR__.'/lib/uri_router.php';
		else if(file_exists(__DIR__.'/../../lib/uri_router.php'))
			include __DIR__.'/../../lib/uri_router.php';
		else
			throw new Exception('uri_router.php library not found');
	}
	if(!class_exists('superclosure'))
	{
		if(file_exists(__DIR__.'/lib/superclosure.php'))
			include __DIR__.'/lib/superclosure.php';
		else if(file_exists(__DIR__.'/../../lib/superclosure.php'))
			include __DIR__.'/../../lib/superclosure.php';
		else
			throw new Exception('superclosure.php library not found');
	}

	class superclosure_meta extends superclosure
	{
		public function _flush()
		{
			$this->closure_vars=null;
			$this->closure_body=null;
		}
		public function get_closure_vars()
		{
			return $this->closure_vars;
		}
		public function get_closure_body()
		{
			return $this->closure_body;
		}
	}

	abstract class superclosure_router extends uri_router
	{
		private static $source_variable=null;
		private static $request_method_variable=null;
		private static $cache_registry=array();

		public static function set_source_variable(string $variable)
		{
			static::$source_variable=$variable;
		}
		public static function set_request_method_variable(string $variable)
		{
			static::$request_method_variable=$variable;
		}
		public static function set_default_route(callable $callback)
		{
			static::$default_route['callback']=new superclosure_meta($callback);
		}

		public static function add_to_cache(string $variable, string $value)
		{
			if(strpos($variable, '\'') !== false)
				throw new Exception('an apostrophe is not allowed here');
			static::$cache_registry[$variable]=$value;
		}
		public static function read_from_cache(string $variable)
		{
			if(!isset(static::$cache_registry[$variable]))
				throw new Exception('the '.$variable.' variable is not set in the cache');
			return '$__superclosure_router_cache[\''.$variable.'\']';
		}

		public static function add(
			array $source,
			callable $callback,
			bool $use_regex=false,
			$request_method=null
		){
			parent::add(
				$source,
				new superclosure_meta($callback),
				$use_regex,
				$request_method
			);
		}

		public static function dump_cache(string $cache_file)
		{
			if(static::$source_variable === null)
				throw new Exception('source variable undefined');

			$output_file=fopen($cache_file, 'w');
			fwrite($output_file, '<?php ');
			$first_if=true;

			if(!empty(static::$cache_registry))
			{
				$first_cache_element=true;
				fwrite($output_file, '$__superclosure_router_cache=[');
				foreach(static::$cache_registry as $cache_key=>$cache_value)
				{
					if(!$first_cache_element)
						fwrite($output_file, ',');
					fwrite($output_file, '\''.$cache_key.'\'=>'.$cache_value);
					$first_cache_element=false;
				}
				fwrite($output_file, '];');
			}

			foreach(static::$routing_table as $routing_element)
			{
				$first_condition=true;
				$routing_element[1]->__sleep();

				if(!$first_if)
					fwrite($output_file, 'else ');
				fwrite($output_file, 'if((');

				foreach($routing_element[0] as $routing_path)
				{
					if(!$first_condition)
						fwrite($output_file, '||');
					$first_condition=false;

					if($routing_element[2])
						fwrite($output_file, 'preg_match(\'#^'.static::$base_path.$routing_path.'$#\','.static::$source_variable.')');
					else
						fwrite($output_file, '(\''.static::$base_path.$routing_path.'\'==='.static::$source_variable.')');
				}

				fwrite($output_file, ')');

				if((static::$request_method_variable) && ($routing_element[3] !== null))
					fwrite($output_file, '&&(\''.$routing_element[3].'\'==='.static::$request_method_variable.')');

				fwrite($output_file, '){');

				if(!empty($routing_element[1]->get_closure_vars()))
					fwrite($output_file, 'extract(unserialize(\''.str_replace('\'', '\\\'', serialize($routing_element[1]->get_closure_vars())).'\'));');

				fwrite($output_file, '$__c='.$routing_element[1]->get_closure_body().';$__c();unset($__c);}');

				$routing_element[1]->_flush();
				$first_condition=true;
				$first_if=false;
			}

			if(isset(static::$default_route['callback']))
			{
				static::$default_route['callback']->__sleep();

				fwrite($output_file, 'else{');

				if(!empty(static::$default_route['callback']->get_closure_vars()))
					fwrite($output_file, 'extract(unserialize('.str_replace('\'', '\\\'', serialize(static::$default_route['callback']->get_closure_vars())).'));');

				fwrite($output_file, '$__c='.static::$default_route['callback']->get_closure_body().';$__c();unset($__c);}');

				static::$default_route['callback']->_flush();
			}

			if(!empty(static::$cache_registry))
				fwrite($output_file, 'unset($__superclosure_router_cache);');

			fwrite($output_file, ' ?>');
			fclose($output_file);

			file_put_contents($cache_file, php_strip_whitespace($cache_file));
		}
	}
?>