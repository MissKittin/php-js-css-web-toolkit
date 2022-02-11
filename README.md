# PHP-JS-CSS web toolkit
MVC project skeleton and set of tools and libraries that you can use in your project.  
Made for experimental purposes.

## Principles
* respect the KISS rule
* use MVC design pattern
* avoid autoloading
* avoid chdir()
* move all repeating code to the separated files
* mix PHP code with HTML as little as possible, avoid this in controllers
* PHP and CSS have a snake_case, Js - camelCase
* if you are writing a new library, keep it independent of the other libraries
* if you are writing a new component, keep it independent of the other components

## PHP CLI tools
* `assets-compiler.php` - compile assets from app/assets
* `autoloader-generator.php` - a toy that scans PHP files and generates an autoloader script
* `check-easter-mkcache.php` - cache generator for `check_easter_cache()`
* `file-sign.php` - interface for `sec_file_sign.php`
* `file-watch.php` - run the command after modifying the file(s)
* `include2blob.php` - a toy that converts inclusion to a single file blob
* `link2file.php` - recursively convert all symbolic links to files
* `lv-encrypter.php` - interface for `sec_lv_encrypter.php`
* `opcache-preload-generator.php` - opcache preload script generator
* `pdo-connect.php` - interface for `pdo_connect.php`, `pdo_cheat.php` (optional) and `pdo_crud_builder.php` (optional) - seed databases from `app/databases`
* `queue-worker.php` - interface for `queue_worker.php` server
* `serve.php` - start PHP development server
* `sqlite3-db-dump.php` - interface for `sqlite3_db_dump.php`
* `sqlite3-db-vacuum.php` - vacuum database
* `webdevsh.php` - interface for `webdevsh.php`

## PHP libraries
* `array_tree.php` - convert flat array into tree, tree to list
* `blog_page_slicer.php` - select n elements from array at start point
* `cache_container.php` - cache manager
* `check_date.php` - check if is between DD.MM - DD.MM
* `check_var.php` - check if variable and eventually return value
* `convert_bytes.php` - automatically convert input number to human-readable form
* `copy_recursive.php` - copy entire directories
* `curl_file_updown.php` - quickly download/upload file
* `directoryIterator_sort.php` - run directoryIterator and sort output by name
* `dotenv.php` - DotEnv proxy implementation
* `file_http_request.php` - `file_get_contents()` wrapper for http streams
* `find_php_definitions.php` - look up the definition of functions, classes, interfaces and traits in the source code
* `getallheaders.php` - `getallheaders()` for older PHP versions
* `global_variable_streamer.php` - a helper for functions that only support writing output to a file
* `http_request_response.php` - OOP overlay for standard request-response handling
* `ioc_container.php` - dependency injection containers
* `logger.php` - easily write logs
* `maintenance_break.php` - check to send the maintenance break pattern
* `measure_exec_time.php` - debugging
* `ob_cache.php` - cache output buffer
* `ob_minifier.php` - simple minifier and compressor
* `ob_sfucator.php` - xor all page content on server and decode on client
* `observer.php` - design pattern
* `pdo_cheat.php` - use the table as an object
* `pdo_connect.php` - open preconfigured connection to the database and optionally seed
* `pdo_crud_builder.php` - OOP SQL builder
* `print_file.php` - set http headers and send specified file to the client
* `queue_worker.php` - execute jobs outside the HTTP server
* `rand_str.php` - random string generator
* `registry.php` - design pattern
* `relative_path.php` - get relative path between two files/directories
* `school_algorithms.php` - miscellaneous and sorting algorithms from lessons
* `sec_bruteforce.php` - trivial banning method by IP on n unsuccessful attempts
* `sec_captcha.php` - CAPTCHA image generator
* `sec_csrf.php` - CSRF protection helpers
* `sec_file_sign.php` - easily generate file signatures
* `sec_http_basic_auth.php` - request and validate basic HTTP authentication
* `sec_login.php` - login/logout helpers
* `sec_lv_encrypter.php` - laravel's encrypter class for cookies and sessions (MIT)
* `sec_prevent_direct.php` - for historical purposes
* `simple_html_dom.php` - S.C. Chen's HTML DOM parser v1.9.1 (MIT)
* `simpleblog_db.php` - key-value database that can be edited in notepad
* `singleton.php` - each time you use a singleton, one little kitten dies
* `sitemap_generator.php` - `sitemap.xml` builder
* `sqlite3_db_dump.php` - Ephestione's SQLite3 database dumper (unknown license)
* `strip_php_comments.php` - remove comments from PHP source
* `superclosure.php` - serializable anonymous functions
* `time_converter.php` - time converting library - convert time to human-readable form
* `trivial_templating_engine.php`
* `uri_router.php` - OOP routing solution
* `webdevsh.php` - CLI functions for webdev.sh minifiers
* `zip.php` - make ZIP file in RAM - library from PhpMyAdmin (GNU GPL2)

## Javascript libraries
* `addDesktopIcon.js` - create box with win98-style icon
* `beautify.js` - quickly replace certain word (temporary solution)
* `checkDate.js` - `check_date.php` in Js version
* `clock.js` - render clock in div
* `convertBytes.js` - `convert_bytes.php` in Js version
* `disableEnterOnForm.js` - disable submit by Enter behavior
* `enableTabOnTextarea.js` - allow inserting tabs on selected textareas
* `epilepsi.js` - nice functions for creating epileptic impressions
* `fadeAnimations.js` - fade-in on load and fade-out before unload
* `getCookie.js` - read cookie value
* `getCssJs.js` - apply CSS or Js at run time
* `getJson.js` - get/send JSON data
* `imgRotator.js` - rotate images on selected id
* `linkify.js` - convert plaintext links to anchors (author: rooseve)
* `list2tree.js` - convert ul or ol to expandable tree
* `multipage.js` - put several pages in one HTML file / element switcher
* `rand.js`
* `sendNotification.js` - send notification to the browser
* `sleep.js`
* `sortTable.js` - adds table sort by clicking table header
* `time_converter.php` - `time_converter.php` in Js version
* `titleScroller.js` - infinity title scrolling
* `wicdPhpGuiWindows.js` - CSS window objects wrapped in Js automation

## CSS libraries
* `button.css` - quickly create nice buttons
* `copyleft.css` - flipped `&copy;`
* `fakelink.css` - link-styled span
* `simpleblog_default.css` - simpleblog default skin - layout template
* `simpleblog_materialized.css` - Material design theme from simpleblog admin panel
* `tooltip.css` - CSS tooltips

## PHP components
* `login` - quickly create login-restricted content
* `middleware_form` - customizable HTML form
* `superclosure_router` - cacheable `uri_router.php`
