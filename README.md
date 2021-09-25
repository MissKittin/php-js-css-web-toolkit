# PHP-JS-CSS web toolkit
MVC project skeleton and set of tools and libraries that you can use in your project.  
Made for experimental purposes.

## Principles
* respect the KISS rule
* use MVC design pattern
* avoid autoloading
* move all repeating code to the separated files
* mix PHP code with HTML as little as possible, avoid this in controllers
* PHP and CSS have snake_case, Js - camelCase
* if you are writing a new library, keep it independent of the other libraries
* if you are writing a new component, keep it independent of the other components

## Things to do after clone
1) create `app/assets` directory
2) `public/index.php` just imports another php file - this is stupid thing if your server OS allows you to use softlinks.  
	You can remove this file and create link to `../app/routing.php`.  
	Run in this directory:  
	for *nix:
	```
	ln -s ../app/routing.php ./public/index.php
	```
	for windows:
	```
	mklink public\index.php ..\app\routing.php
	```
3) To install assets for default view, run in this directory:  
	for *nix:
	```
	ln -s ../views/samples/default/default.css ./; ln -s ../views/samples/default/default.js ./app/assets/default.js; ln -s ../../lib/sendNotification.js ./app/assets/sendNotification.js
	```
	for windows:
	```
	mklink app\assets\default.css ..\views\samples\default\default.css
	mklink /d app\assets\default.js ..\views\samples\default\default.js
	mklink app\assets\sendNotification.js ..\..\lib\sendNotification.js 
	```

## PHP CLI tools
* `assets-compiler.php` - compile assets from app/assets
* `opcache-preload-generator.php` - opcache preload script generator
* `pdo-connect-offline-seed.php` - interface for pdo_connect.php and pdo_crud_builder.php (optional) - seed databases from app/databases
* `serve.php` - start php development server
* `sqlite3-db-dump.php` - interface for sqlite3_db_dump.php
* `sqlite3-db-vacuum.php` - vacuum database
* `webdevsh.php` - interface for webdevsh.php - minify files from public/assets

## PHP libraries
* `array_tree.php` - convert flat array into tree, tree to list
* `blog_page_slicer.php` - select n elements from array at start point
* `check_date.php` - check if is between DD.MM - DD.MM
* `check_var.php` - check if variable and eventually return value
* `convert_bytes.php` - automatically convert input number to human-readable form
* `curl_file_updown.php` - quickly download/upload file
* `directoryIterator_sort.php` - run directoryIterator and sort output by name
* `file_cache.php` - cache all output to file
* `logger.php` - easily write logs
* `login.php` - login/logout helpers
* `measure_exec_time.php` - debugging
* `observer.php` - design pattern
* `ob_minifier.php` - simple minifier and compressor
* `ob_sfucator.php` - xor all page content on server and decode on client
* `pdo_connect.php` - open preconfigured connection to the database and optionally seed
* `pdo_crud_builder.php` - oop sql builder
* `print_file.php` - set http headers and send specified file to the client
* `rand_str.php` - random string generator
* `registry.php` - design pattern
* `sec_bruteforce.php` - trivial banning method by IP on n unsuccessful attempts
* `sec_csrf.php` - CSRF protection helpers
* `sec_http_basic_auth.php` - request and validate basic HTTP authentication
* `sec_prevent_direct.php` - for historical purposes
* `simple_html_dom.php` - S.C. Chen's HTML DOM parser v1.9.1 (MIT)
* `singleton.php` - each time you use a singleton, one little kitten dies
* `sqlite3_db_dump.php` - Ephestione's SQLite3 database dumper (unknown license)
* `time_converter.php` - time converting library - convert time to human-readable form
* `webdevsh.php` - cli functions for webdev.sh minifiers
* `zip.php` - make zip file from php - library from PhpMyAdmin (GNU GPL2)

## Javascript libraries
* `addDesktopIcon.js` - create box with win98-style icon
* `beautify.js` - quickly replace certain word (temporary solution)
* `checkDate.js` - check_date.php in javascript version
* `clock.js` - render clock in div
* `convertBytes.js` - convert_bytes.php in javascript version
* `enableTabOnTextarea.js` - allow inserting tabs on selected textareas
* `epilepsi.js` - nice functions for creating epileptic impressions
* `fadeAnimations.js` - fade-in on load and fade-out before unload
* `getCookie.js` - read cookie value
* `getCssJs.js` - apply css or js at run time
* `getJson.js` - get/send JSON data
* `imgRotator.js` - rotate images on selected id
* `linkify.js` - convert plaintext links to anchors (author: rooseve)
* `list2tree.js` - convert ul or ol to expandable tree
* `multipage.js` - put several pages in one HTML file / element switcher
* `rand.js`
* `sendNotification.js` - send notification to the browser
* `sleep.js`
* `sortTable.js` - adds table sort by clicking table header
* `time_converter.php` - time_converter.php in javascript version
* `titleScroller.js` - infinity title scrolling

## CSS libraries
* `button.css` - quickly create button
* `copyleft.css` - flipped &copy;
* `fakelink.css` - link-styled span
* `simpleblog_default.css` - simpleblog default skin - layout template
* `simpleblog_materialized.css` - Material design theme from simpleblog admin panel
* `tooltip.css` - CSS tooltips

## PHP components
* `login` - quickly create login-restricted content

## Removing samples
All sample code is in `samples` dirs - ignore this fact. Remove samples and start developing application.  
To remove all samples run in this directory:  
for *nix:
```
(find ./app -maxdepth 2 -name samples) | xargs rm -r -f; sed -i '/{/{:1;N;s/{.*}/{\n\t\t\n\t}/;T1}' ./app/routing.php
```
