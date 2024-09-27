# PHP-JS-CSS web toolkit
A set of tools, components and libraries that you can use in your project  
Minimum PHP version: 7.1  
Recommended PHP version: 7.4 or newer  
Supported relational databases: PostgreSQL, MySQL, SQLite3  
Supported cache databases: Redis, Memcached, APCu

## Principles
* respect the KISS rule
* respect the DRY rule
* PHP and CSS have a snake_case, Js - camelCase
* if you are writing a new library, keep it independent of the other libraries, components and tools
* if you are writing a new tool, keep it independent of the other tools
* if you are writing a new component, keep it independent of the other components

## PHP CLI tools
* `assets-compiler.php` - interface for `assets_compiler.php` library
* `autoloader-generator.php` - a toy that scans PHP files and generates an autoloader script
* `check-easter-mkcache.php` - cache generator for `check_easter_cache()`
* `cron.php` - interface for `cron.php` library
* `file-sign.php` - interface for `sec_file_sign.php` library
* `file-watch.php` - run the command after modifying the file(s)
* `get-composer.php` - easily add composer to your project
* `link2file.php` - recursively convert all symbolic links to files
* `logrotate.php` - interface for `logrotate.php` library
* `lv-encrypter.php` - interface for `sec_lv_encrypter.php` library
* `matthiasmullie-minify.php` - interface for the `matthiasmullie/minify` package
* `mkphar.php` - a tool to quickly build a PHP archive
* `opcache-preload-generator.php` - opcache preload script generator
* `pdo-connect.php` - interface for `pdo_connect.php` library - seed databases
* `queue-worker.php` - interface for `queue_worker.php` server
* `remove-gpl.php` - remove GPL-licensed libraries
* `run-php-bin-tests.php` - run PHP tool tests in batch mode
* `run-php-com-tests.php` - run PHP components tests in batch mode
* `run-php-lib-tests.php` - run PHP library tests in batch mode
* `run-phtml-tests.php` - serve phtml tests (for Js and CSS libraries)
* `serve.php` - start PHP development server
* `sqlite3-db-dump.php` - interface for `sqlite3_db_dump.php` library
* `sqlite3-db-vacuum.php` - vacuum database
* `strip-php-files.php` - a toy that removes comments and whitespace from php files
* `webdevsh.php` - interface for `webdevsh.php` library
* `websockets.php` - a simple point-to-point websocket server

## PHP libraries
* `ascii.php` - ASCII conversion (MIT)
* `assets_compiler.php`
* `async_process_builder.php` - `proc_*` wrapper and standard input-output-error interface
* `cache_container.php` - cache manager
* `check_date.php` - check if is between DD.MM - DD.MM
* `check_var.php` - check if variable and eventually return value
* `cli_prompt.php` - console prompter
* `convert_bytes.php` - automatically convert input number to human-readable form
* `copy_recursive.php` - copy entire directories
* `cron.php` - task scheduler
* `csv2table.php` - convert CSV file to HTML table
* `curl_file_updown.php` - quickly download/upload file
* `dotenv.php` - DotEnv proxy implementation
* `file_http_request.php` - `file_get_contents()` wrapper for http streams
* `find_php_definitions.php` - look up the definition of functions, classes, interfaces and traits in the source code
* `generate_csp_hash.php` - generate SHA-256 for a string or file
* `global_variable_streamer.php` - a helper for functions that only support writing output to a file
* `has_php_close_tag.php` - check if the PHP file ends with a close tag
* `http_request_response.php` - OOP overlay for standard request-response handling
* `include_into_namespace.php` - function that facilitates including libraries to a namespace (mainly for testing purposes)
* `ioc_container.php` - dependency injection containers
* `is_float_equal.php`
* `logger.php` - easily write logs
* `logrotate.php` - journalists rotation machine
* `lv_arr.php` - Laravel array helpers & collections (MIT)
* `lv_macroable.php` - add extra functionality to a class (MIT)
* `lv_str.php` - Laravel string helpers (MIT)
* `maintenance_break.php` - check to send the maintenance break pattern
* `measure_exec_time.php` - debugging
* `mem_zip.php` - create zip file in memory (GNU GPL2)
* `memcached_connect.php` - open preconfigured connection to the Memcached
* `ob_cache.php` - cache output buffer
* `ob_minifier.php` - simple minifier and compressor
* `ob_sfucator.php` - xor all page content on server and decode on client
* `observer.php` - design pattern
* `ocw_slugify.php` - Our Code World slugifiers (CC0 1.0 Deed)
* `pdo_cheat.php` - use the table as an object
* `pdo_connect.php` - open preconfigured connection to the database and optionally seed
* `pdo_crud_builder.php` - OOP SQL builder
* `predis_connect.php` - Predis support library
* `print_file.php` - set HTTP headers and send specified file to the client
* `queue_worker.php` - execute jobs outside the HTTP server
* `rand_str.php` - random string generator
* `redis_connect.php` - open preconfigured connection to the Redis
* `registry.php` - design pattern
* `relative_path.php` - get relative path between two files/directories
* `rmdir_recursive.php` - remove non-empty directories
* `scandir_recursive.php` - scan directory recursively
* `sess_array.php` - store the session in an array (mainly for testing purposes)
* `simple_html_dom.php` - S.C. Chen's HTML DOM parser v1.9.1 (MIT)
* `simpleblog_db.php` - key-value database that can be edited in notepad
* `singleton.php` - each time you use a singleton, one little kitten dies
* `sitemap_generator.php` - `sitemap.xml` builder
* `sqlite3_db_dump.php` - Ephestione's SQLite3 database dumper (unknown license)
* `string_translator.php` - translate your app
* `strip_php_comments.php` - remove comments from PHP source
* `superclosure.php` - serializable anonymous functions
* `time_converter.php` - time converting library - convert time to human-readable form
* `trivial_templating_engine.php`
* `ulid.php` - ULID manipulation (MIT)
* `uri_router.php` - OOP routing solution
* `uuid.php` - UUID manipulation (MIT)
* `var_export_contains.php` - check if the content of the variable is correct (mainly for testing purposes)
* `webdevsh.php` - functions for Toptal minifiers
* `wp_is_serialized.php` - check if string is serialized data - function from Wordpress (GNU GPL2)

## PHP libraries - polyfills
* `pf_array.php` - `array_*()` (MIT)
* `pf_get_debug_type.php`
* `pf_getallheaders.php`
* `pf_is_countable.php` (MIT)
* `pf_json_validate.php` (MIT)
* `pf_mbstring.php` - `mb_*()` (MIT)
* `pf_php_float.php` - `PHP_FLOAT_*`
* `pf_php_os_family.php` (MIT)
* `pf_spl_object_id.php` (MIT)
* `pf_str.php` - `str_*()` (MIT)
* `pf_stream_isatty.php` (MIT)
* `pf_Stringable.php`
* `pf_ValueError.php` - `Error` and `ValueError`

## PHP libraries - security
* `sec_bruteforce.php` - trivial banning method by IP on n unsuccessful attempts
* `sec_captcha.php` - CAPTCHA image generator
* `sec_csrf.php` - CSRF protection helpers
* `sec_file_sign.php` - easily generate file signatures
* `sec_http_basic_auth.php` - request and validate basic HTTP authentication
* `sec_login.php` - login/logout helpers
* `sec_lv_encrypter.php` - Laravel encrypter class for cookies and sessions (MIT)

## JavaScript libraries (ES6)
* `addDesktopIcon.js` - create box with win98-style icon
* `beautify.js` - quickly replace certain word (temporary solution)
* `checkDate.js` - `check_date.php` in Js
* `clock.js` - render clock in div
* `convertBytes.js` - `convert_bytes.php` in Js
* `csv2table.js` - build a table from CSV data
* `disableEnterOnForm.js` - disable submit by Enter behavior
* `enableTabOnTextarea.js` - allow inserting tabs on selected textareas
* `epilepsi.js` - nice functions for creating epileptic impressions
* `fadeAnimationIn.js` - fade-in on load
* `fadeAnimationOut.js` - fade-out before unload
* `getCookie.js` - read cookie value
* `getCssJs.js` - apply CSS or Js at run time
* `getJson.js` - get/send JSON data
* `imgRotator.js` - rotate images on selected id
* `linkify.js` - rooseve's plaintext link to anchor converter
* `multipage.js` - put several pages in one HTML file / element switcher
* `rand.js`
* `richTextEditor.js` - basic WYSIWYG editor
* `sendNotification.js` - send notification to the browser
* `sleep.js`
* `sortTable.js` - adds a function to sort the table by clicking on the table header
* `timeConverter.js` - `time_converter.php` in Js
* `titleScroller.js` - infinity title scrolling
* `wicdPhpGuiWindows.js` - CSS window objects wrapped in Js automation

## CSS3 libraries
* `button.css` - quickly create nice buttons
* `copyleft.css` - flipped `&copy;`
* `fakelink.css` - link-styled span
* `simpleblog_default.css` - simpleblog default skin - layout template
* `simpleblog_materialized.css` - Material design theme from simpleblog admin panel (MIT)
* `tooltip.css` - CSS tooltips

## PHP components
* `admin_panel` - a small framework
* `herring` - device for tracking users
* `login` - quickly create login-restricted content
* `lv_hlp` - Laravel helpers (MIT)
* `middleware_form` - customizable HTML form
* `php_polyfill` - backport some features
* `superclosure_router` - cacheable `uri_router.php`
