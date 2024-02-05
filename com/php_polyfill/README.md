# PHP Polyfill
Backport some features

## Note
Throws an `Exception` if the library is not found

## Required libraries
* `has_php_close_tag.php` (for `mkcache.php`)
* `pf_getallheaders.php`
### for older than 8.3
* `pf_json_validate.php`
### for older than 8.1
* `pf_array.php` - `array_is_list()`
### for older than 8.0
* `pf_str.php` - `str_contains()`, `str_ends_with()` and `str_starts_with()`
* `pf_Stringable.php`
* `pf_ValueError.php`
### for older than 7.3
* `pf_array.php` - `array_key_first()` and `array_key_last()`
* `pf_is_countable.php`
### for older than 7.2
* `pf_php_float.php`
* `pf_php_os_family.php`
* `pf_stream_isatty.php`
* `pf_spl_object_id.php`

## Cache
To reduce the number of included files/update the cache,  
use the `mkcache.php` tool:
```
php ./bin/mkcache.php
```
you can also delete the cache:
```
php ./bin/mkcache.php --remove
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
