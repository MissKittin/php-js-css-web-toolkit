<?php
	if(!class_exists('uri_router'))
	{
		if(file_exists(__DIR__.'/lib/uri_router.php'))
			require __DIR__.'/lib/uri_router.php';
		else if(file_exists(__DIR__.'/../../lib/uri_router.php'))
			require __DIR__.'/../../lib/uri_router.php';
		else
			throw new Exception('uri_router.php library not found');
	}
	if(!class_exists('superclosure'))
	{
		if(file_exists(__DIR__.'/lib/superclosure.php'))
			require __DIR__.'/lib/superclosure.php';
		else if(file_exists(__DIR__.'/../../lib/superclosure.php'))
			require __DIR__.'/../../lib/superclosure.php';
		else
			throw new Exception('superclosure.php library not found');
	}

	class superclosure_router_exception extends Exception {}
	abstract class superclosure_router extends uri_router
	{
		protected static $source_variable=null;
		protected static $request_method_variable=null;
		protected static $cache_registry=[];
		protected static $run_callback=['callback'=>null];

		protected static function run_callback(callable $callback)
		{
			if(static::$run_callback['callback'] === null)
				$callback();
			else
				static::$run_callback['callback']($callback);
		}

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
		public static function set_run_callback(closure $callback)
		{
			static::$run_callback['callback']=new superclosure_meta($callback);
		}

		public static function add_to_cache(string $variable, string $value)
		{
			if(strpos($variable, '\'') !== false)
				throw new superclosure_router_exception('An apostrophe is not allowed here');

			static::$cache_registry[$variable]=$value;
		}
		public static function read_from_cache(string $variable)
		{
			if(!isset(static::$cache_registry[$variable]))
				throw new superclosure_router_exception('The '.$variable.' variable is not set in the cache');

			return '$__superclosure_router_cache[\''.$variable.'\']';
		}

		public static function add(
			array $source,
			closure $callback,
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
				throw new superclosure_router_exception('Source variable undefined');

			$output_file=fopen($cache_file, 'w');
			$first_if=true;

			fwrite($output_file, '<?php ');

			if(static::$run_callback['callback'] !== null)
			{
				fwrite($output_file, ''
				.	'$__superclosure_router_rc=function($c){'
				.		'$w='.static::$run_callback['callback']->get_closure_body().';'
				.		'$w($c);'
				.	'};'
				);
				static::$run_callback['callback']->flush();
			}

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
						fwrite($output_file, ''
						.	'extract('
						.		'unserialize(\''
						.			str_replace(
										'\'', '\\\'',
										serialize(
											$routing_element[1]->get_closure_vars()
										)
									)
						.		'\')'
						.	');'
						);

					if(static::$run_callback['callback'] === null)
						fwrite($output_file, ''
						.	'$__c='.$routing_element[1]->get_closure_body().';'
						.	'$__c();'
						.	'unset($__c);'
						);
					else
						fwrite($output_file, ''
						.	'$__c='.$routing_element[1]->get_closure_body().';'
						.	'$__superclosure_router_rc($__c);'
						.	'unset($__c);'
						);
				fwrite($output_file, '}');

				$routing_element[1]->flush();
				$first_condition=true;
				$first_if=false;
			}

			if(isset(static::$default_route['callback']))
			{
				fwrite($output_file, 'else{');
					if(!empty(static::$default_route['callback']->get_closure_vars()))
						fwrite($output_file, ''
						.	'extract('
						.		'unserialize(\''
						.			str_replace(
										'\'', '\\\'',
										serialize(
											static::$default_route['callback']->get_closure_vars()
										)
									)
						.		'\')'
						.	');'
						);

					fwrite($output_file, ''
					.	'$__c='.static::$default_route['callback']->get_closure_body().';'
					.	'$__c();'
					.	'unset($__c);'
					);
				fwrite($output_file, '}');

				static::$default_route['callback']->flush();
			}

			if(!empty(static::$cache_registry))
				fwrite($output_file, 'unset($__superclosure_router_cache);');
			if(static::$run_callback['callback'] !== null)
				fwrite($output_file, 'unset($__superclosure_router_rc);');

			fwrite($output_file, ' ?>');
			fclose($output_file);

			file_put_contents($cache_file, php_strip_whitespace($cache_file));
		}
	}
?>