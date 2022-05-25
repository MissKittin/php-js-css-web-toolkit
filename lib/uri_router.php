<?php
	abstract class uri_router
	{
		/*
		 * OOP URI routing solution
		 * with regex support
		 *
		 * Hint:
		 *  you can inherit from this class
		 *  if you do uri_router::route() without uri_router::set_default_route() first,
		 *   you can build next routing table
		 *
		 * Warning:
		 *  uri_router::route() will flush routing table
		 *  routes without request method specified applies to any request method
		 *
		 * Methods:
		 *  uri_router::set_base_path(string)
		 *  uri_router::set_source(string)
		 *  uri_router::set_request_method(string)
		 *  uri_router::set_default_route(callback_function)
		 *  uri_router::set_reverse_mode(bool) [default: false]
		 *  uri_router::add(string_source, callback_function, bool_use_regex, string_request_method) [default: use_regex=false, request_method=null]
		 *  uri_router::route()
		 *
		 * Usage:
			// example $_SERVER values:
			// REQUEST_METHOD: GET
			// REQUEST_URI: /basepth/arg1/arg2/arg3?getarg1=getval1&getarg2=getval2

			// general settings
			uri_router::set_base_path('/basepth'); // optional
			uri_router::set_source(strtok($_SERVER['REQUEST_URI'], '?')); // required
			uri_router::set_request_method($_SERVER['REQUEST_METHOD']); // optional
			//uri_router::set_reverse_mode(true); // will execute routes from last to first, optional, disabled by default

			// if route not found, optional
			uri_router::set_default_route(function(){
				echo '[EE] Not found';
			});

			// simple route
			uri_router::add(['/arg1/arg2/arg3'], function(){
				echo '[OK] arg1-arg2-arg3';
			});

			// multipath route (a or b)
			uri_router::add(['/arg1/arg6/arg3', '/arg1/arg7/arg3'], function(){
				echo '[OK] arg1-arg6||arg7-arg3';
			});

			// route with regex
			uri_router::add(['/arg1/arg([0-9])/arg3'], function(){
				echo '[OK] arg1-argX-arg3';
			}, true);

			// POST-only route (you can write anything instead of POST, false means do not use regex)
			// note: first rule will be always executed instead of this unless you enable reverse mode
			uri_router::add(['/arg1/arg2/arg3'], function(){
				echo '[OK] POST: arg1-arg2-arg3';
			}, false, 'POST');

			uri_router::route(); // exec and flush routing table
		 */

		protected static $routing_table=[];
		protected static $base_path='';
		protected static $source=null;
		protected static $request_method;
		protected static $default_route=null;
		protected static $reverse_mode=false;

		public static function set_base_path(string $path)
		{
			static::$base_path=$path;
		}
		public static function set_source(string $source)
		{
			static::$source=$source;
		}
		public static function set_request_method(string $method)
		{
			static::$request_method=$method;
		}
		public static function set_default_route(callable $callback)
		{
			static::$default_route['callback']=$callback;
		}
		public static function set_reverse_mode(bool $enable=false)
		{
			static::$reverse_mode=$enable;
		}

		public static function add(
			array $source,
			callable $callback,
			bool $use_regex=false,
			$request_method=null
		){
			if(static::$reverse_mode)
				array_unshift(static::$routing_table, [
					$source,
					$callback,
					$use_regex,
					$request_method
				]);
			else
				static::$routing_table[]=[
					$source,
					$callback,
					$use_regex,
					$request_method
				];
		}
		public static function route()
		{
			if(static::$source === null)
				throw new Exception('Source undefined');

			$path_matches=false;
			foreach(static::$routing_table as $routing_element)
				if(($routing_element[3] === null) || ($routing_element[3] === static::$request_method))
					foreach($routing_element[0] as $routing_path)
					{
						if($routing_element[2])
						{
							if(preg_match(
								'#^'.static::$base_path.$routing_path.'$#',
								static::$source
							))
								$path_matches=true;
						}
						else
							if(static::$base_path.$routing_path === static::$source)
								$path_matches=true;

						if($path_matches)
						{
							static::$routing_table=[];
							$routing_element[1]();

							return true;
						}
					}

			if(isset(static::$default_route['callback']))
			{
				static::$routing_table=[];
				static::$default_route['callback']();
			}

			return false;
		}
	}
?>