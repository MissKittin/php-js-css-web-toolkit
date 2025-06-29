# Superclosure router
Cacheable `uri_router.php`  
Allows you to convert defined rules to PHP code

## Required libraries
* `superclosure.php`
* `uri_router.php`

## Note
Throws an `superclosure_router_exception` on error

## New methods
* **[static]** `set_source_variable(string_variable)` [returns self]  
	set URI source for cache
* **[static]** `set_request_method_variable(string_variable)` [returns self]  
	for cache
* **[static]** `set_run_callback(closure_callback)` [returns self]  
	define routing function arguments
* **[static]** `add_to_cache(string_variable, string_value)` [returns self]  
	cache operation result
* **[static]** `read_from_cache(string_variable)` [returns string]  
	read the result of the operation from the cache
* **[static]** `dump_cache(string_output_file_path)` [returns self]  
	dump defined rules and cached operations
For more info, see `uri_router.php` library

## Usage
Before defining the rules, check if the cache file exists
```
if(file_exists('./var/cache/superclosure_router.php'))
	require './var/cache/superclosure_router.php';
else
{
	require './com/superclosure_router/main.php';
```
Use the same as `uri_router` (see `uri_router.php`)  
Before calling `route()`, generate the cache:
```
superclosure_router
::	set_source_variable("strtok(\$_SERVER['REQUEST_URI'], '?')") // required
::	set_request_method_variable("\$_SERVER['REQUEST_METHOD']") // optional
::	dump_cache('./var/cache/superclosure_router.php');
```
or (the `strtok` function will only be called once - second parameter of the `add_to_cache()` will be eval'd)
```
superclosure_router
::	add_to_cache('strtok', "strtok(\$_SERVER['REQUEST_URI'], '?')") // optimization
::	set_source_variable(superclosure_router::read_from_cache('strtok')) // required
::	set_request_method_variable("\$_SERVER['REQUEST_METHOD']") // optional
::	dump_cache('./var/cache/superclosure_router.php');
```
and then
```
superclosure_router::route(); // exec and flush routing table
```

## Set run callback
Here it's different - do not extend the `superclosure_router` class  
If you want to define routing function arguments,  
you can use the `set_run_callback()` method, eg:
```
superclosure_router::set_run_callback(function($callback, $matches){
	return $callback(
		$matches, // regex matches
		'example-arg-1',
		'example-arg-2'
	);
});
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
