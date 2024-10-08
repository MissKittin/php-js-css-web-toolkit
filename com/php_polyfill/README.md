# PHP Polyfill
Backport some features

## Note
Throws an `php_polyfill_exception` if the library is not found

## Required libraries
* `has_php_close_tag.php` (for `mkcache.php`)
* `pf_getallheaders.php`
### for older than 8.4
* `pf_array.php` - `array_all()`, `array_any()`, `array_find()` and `array_find_key()`
* `pf_mbstring.php` - `mb_lcfirst()` and `mb_ucfirst()`
### for older than 8.3
* `pf_mbstring.php` - `mb_str_pad()`
* `pf_json_validate.php`
### for older than 8.1
* `pf_array.php` - `array_is_list()`
### for older than 8.0
* `pf_get_debug_type.php`
* `pf_str.php` - `str_contains()`, `str_ends_with()` and `str_starts_with()`
* `pf_Stringable.php`
* `pf_ValueError.php`
### for older than 7.4
* `pf_mbstring.php` - `mb_str_split()`
### for older than 7.3
* `pf_array.php` - `array_key_first()` and `array_key_last()`
* `pf_is_countable.php`
### for older than 7.2
* `pf_mbstring.php` - `mb_chr()`, `mb_ord()` and `mb_scrub()`
* `pf_php_float.php`
* `pf_php_os_family.php`
* `pf_stream_isatty.php`
* `pf_spl_object_id.php`

## Usage
Just include the component:
```
<?php
	require './com/php_polyfill/main.php';
```

## Cache
To reduce the number of included files/update the cache,  
use the `mkcache.php` tool:
```
php ./com/php_polyfill/bin/mkcache.php
```
you can also delete the cache:
```
php ./com/php_polyfill/bin/mkcache.php --remove
```
or you can define the cache file path:
```
php ./com/php_polyfill/bin/mkcache.php --out ./php_polyfill_cache.php
```

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
