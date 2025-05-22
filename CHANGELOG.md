# [1.1]

### Added

- `clickalicious_memcached.php` library - added multi-server support
- `json_contents.php` library
- `maximebf_debugbar.php` library - new `custom_debug_bar`, `set_storage`, `set_base_url`, `set_csp_nonce` and `add_csp_header` methods, added `$return_path` argument to `route` method
- `string_interpolator.php` library

### Fixed

- Fixed typo `throw new pdo_connect_exception` in `cache_container.php` library
- `lv_cookie_session_handler` (`sec_lv_encrypter.php` library) automatically closes the session when output starts
- Fixed bug `if(!extension_loaded('openssl_random_pseudo_bytes'))` in `uuid.php` library

### Changed

- Updated `admin_panel` component
- Updated `login` component
- Updated `lv_hlp` component
- Updated `middleware_form` component
- Updated `superclosure_router` component
- `assets_compiler` function (`assets_compiler.php` library) can work without specifying output file
- New `cache_driver_shm` in `cache_container.php` library
- Added debug notices to `cli_server_finish_request.php` library
- `http_request_response.php` library:
	* new `http_uri` class
	* new `auth_user`, `auth_password`, `input_stream` and `uri` methods in `http_request`, old `uri` method renamed to `request_uri`, `json` method no longer requires POST request
	* new `http_input_stream` class
	* new `get_cookie`, `cookie_remove`, `cookie_expire`, `has_header`, `get_header`, `header_append`, `header_remove`, `get_response_content`, `get_response_stream` and `get_status` methods in `http_response`, now uses internal array to manipulate HTTP headers (the constructor parses already defined headers), removed default headers, added middleware system (new `middleware` and `middleware_arg` static methods), added `$append` argument to `response_content` method, removed destructor - from now on you have to run `send_response` method manually
	* the `http_session` class can operate on a subarray (new `$subkey` constructor argument)
- All setters in `ioc_container.php` return self - now you can chain set/unset methods, documentation supplemented with PSR-11
- `logger.php` library: new `notice`, `crit`, `alert` and `emerg` methods, documentation supplemented with PSR-3
- `mkphar.php` tool accepts file and current directory as `--source`
- `ob_cache.php` library now caches HTTP headers
- Added removal of single-line comments in `ob_minifier.php` library
- `pf_mbstring.php` library checks for `mbstring` extension via `function_exists`
- Added SQL `NULL` support for CRUD in `pdo_cheat.php` and `pdo_crud_builder.php` libraries
- Added custom colors in `simpleblog_materialized.css` library
- `uri_router.php` checks if the route callback returns `true`, added `return` in the `run_callback` method (backward compatible, see documentation)
