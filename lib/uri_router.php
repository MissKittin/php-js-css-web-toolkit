<?php
	class uri_router_exception extends Exception {}
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
		 * Note:
		 *  default route callback will not be cleared by uri_router::route()
		 *  throws an uri_router_exception on error
		 *  you can chain all methods except route()
		 *
		 * Methods:
		 *  [static] set_base_path(string) [returns self]
		 *   define a repeating string at the beginning of the URI
		 *  [static] set_source(string) [returns self]
		 *   set URI source
		 *  [static] set_request_method(string) [returns self]
		 *  [static] set_default_route(callback_function) [returns self]
		 *   the function will be called when no rules match
		 *  [static] set_reverse_mode(bool=false) [returns self]
		 *   if true, will execute routes from last to first
		 *  [static] add(string_source, callback_function, bool_use_regex=false, string_request_method=null) [returns self]
		 *   add routing rule
		 *  [static] route() [returns bool]
		 *   jump down the big rabbit hole
		 *
		 * Another source of application arguments:
		 *  if you want application arguments
		 *  regardless of whether index.php is in a subdirectory or not,
		 *  you can use the $_SERVER['PATH_INFO'] variable
		 *  instead of $_SERVER['REQUEST_URI']:
			uri_router::set_source((isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '/'); // will be /arg1/arg2/argX instead of /basepth/arg1/arg2/argX?getvar=getval
		 *
		 * Usage:
			// example $_SERVER values:
			// REQUEST_METHOD: GET
			// REQUEST_URI: /basepth/arg1/arg2/arg3?getarg1=getval1&getarg2=getval2

			// general settings
			uri_router
			::	set_base_path('/basepth') // optional
			::	set_source(strtok($_SERVER['REQUEST_URI'], '?')) // required
			::	set_request_method($_SERVER['REQUEST_METHOD']) // optional

		//	::	set_reverse_mode(true) // will execute routes from last to first, optional, disabled by default

			::	set_default_route(function(){
					// if route not found, optional

					echo '[EE] Not found';
				})
			::	add(['/arg1/arg2/arg3'], function(){
					// simple route

					echo '[OK] arg1-arg2-arg3';
				})
			::	add(['/arg1/arg6/arg3', '/arg1/arg7/arg3'], function(){
					// multipath route (a or b)

					echo '[OK] arg1-arg6||arg7-arg3';
				})
			::	add(['/arg1/arg([0-9])/arg3'], function(){
					// route with regex

					echo '[OK] arg1-argX-arg3';
				}, true)
			::	add(['/arg1/arg2/arg3'], function(){
					// POST-only route (you can write anything instead of POST, false means do not use regex)
					// note: first rule will be always executed instead of this unless you enable reverse mode

					echo '[OK] POST: arg1-arg2-arg3';
				}, false, 'POST')
			::	route(); // exec and flush routing table
		 *
		 * run_callback method
		 *  if you want to define routing function arguments,
		 *  you can override the run_callback method with extension, eg:
			class custom_router extends uri_router
			{
				protected static function run_callback(callable $callback)
				{
					$callback('example-arg-1', 'example-arg-2');
				}
			}
		 */

		protected static $routing_table=[];
		protected static $base_path='';
		protected static $source=null;
		protected static $request_method;
		protected static $default_route=null;
		protected static $reverse_mode=false;

		protected static function run_callback(callable $callback)
		{
			$callback();
		}

		public static function set_base_path(string $path)
		{
			static::$base_path=$path;
			return static::class;
		}
		public static function set_source(string $source)
		{
			static::$source=$source;
			return static::class;
		}
		public static function set_request_method(string $method)
		{
			static::$request_method=$method;
			return static::class;
		}
		public static function set_default_route(callable $callback)
		{
			static::$default_route[0]=$callback;
			return static::class;
		}
		public static function set_reverse_mode(bool $enable=false)
		{
			static::$reverse_mode=$enable;
			return static::class;
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

			return static::class;
		}
		public static function route()
		{
			if(static::$source === null)
				throw new uri_router_exception('Source undefined');

			$path_matches=false;

			foreach(static::$routing_table as $routing_element)
				if(
					($routing_element[3] === null) ||
					($routing_element[3] === static::$request_method)
				)
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
							static::run_callback($routing_element[1]);

							return true;
						}
					}

			if(isset(static::$default_route[0]))
			{
				static::$routing_table=[];
				static::run_callback(static::$default_route[0]);
			}

			return false;
		}
	}
?>