# Superclosure router
Cacheable `uri_router.php`  
Allows you to convert defined rules to PHP code

## Required libraries
* `superclosure.php`
* `uri_router.php`

## Note
Throws an `superclosure_router_exception` on error

## Usage
Before defining the rules, check if the cache file exists
```
if(file_exists('./tmp/routing-cache.php'))
	require './tmp/routing-cache.php';
else {
```
Use the same as `uri_router` (see `uri_router.php`)  
Before calling `route()`, generate the cache:
```
superclosure_router::set_source_variable("strtok(\$_SERVER['REQUEST_URI'], '?')"); // required
superclosure_router::set_request_method_variable("\$_SERVER['REQUEST_METHOD']"); // optional
superclosure_router::dump_cache('./tmp/routing-cache.php');
```
or (the strtok function will only be called once - second parameter of the add_to_cache() will be eval'd)
```
superclosure_router::add_to_cache('strtok', "strtok(\$_SERVER['REQUEST_URI'], '?')"); // optimization
superclosure_router::set_source_variable(superclosure_router::read_from_cache('strtok')); // required
superclosure_router::set_request_method_variable("\$_SERVER['REQUEST_METHOD']"); // optional
superclosure_router::dump_cache('./tmp/routing-cache.php');
```
and then
```
superclosure_router::route(); // exec and flush routing table
```

## set_run_callback method
If you want to define routing function arguments,  
you can use the set_run_callback method, eg:
```
superclosure_router::set_run_callback(function($callback){
	$callback('example-arg-1', 'example-arg-2');
});
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
