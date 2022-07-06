# Things to do after clone
1) `public/index.php` just imports another php file - this is stupid thing if your OS allows you to use softlinks.  
	You can remove this file and create link to `../app/entrypoint.php`.  
	Run in this directory:  
	```
	php ./app/bin/replace-public-index-with-link.php
	```
2) to install assets for default template, run in this directory:  
	```
	php ./app/bin/install-assets.php
	```
	and follow the prompts
3) compile assets to the public directory:  
	```
	php ./bin/assets-compiler.php ./app/assets ./public/assets
	```

# Removing samples
All example code is in `samples` dirs - ignore this fact.  
Remove samples and start developing application.  
To remove example application, run:  
```
php ./app/bin/remove-samples.php
```

# How to create application

### Configuring URL routing
Edit `app/entrypoint.php`  
You can use eg. `uri_router.php` library or `superclosure_router` component.

### Creating assets
There are tree types of assets:
1) single file assets
	Create/put file in `app/assets` - it will be copied to the `public/assets` (see Compiling assets).
2) concatenated assets
	Create directory in `app/assets` with output file name. Place all css or js files in this directory.  
	All files will be merged to one file in `public/assets`  (see Compiling assets).  
	Also you can create this directory in `app/views` and link it to the `app/assets`
3) preprocessed assets
	Create directory in `app/assets` with output file name.  
	Create `main.php` file in this directory with css/js/etc code.  
	Open `<?php` tag and write dynamic code - these block will be executed during compilation.  
	`$current_asset` point to asset's directory.  
	Also put css or js files in this directory these will be included in `main.php`.

### Creating database configuration for pdo_connect()
See `lib/pdo_connect.php`

### Cleaning up the repeating code
Create new php file in `app/shared`, paste block of code and `include` this file

### Compiling assets
Run `php ./bin/assets-compiler.php ./app/assets ./public/assets`  
or you can watch for changes: `php ./bin/file-watch.php "php ./bin/assets-compiler.php ./app/assets ./public/assets" ./app/assets`.

### Minifying assets - webdev.sh client
Run `php ./bin/webdev.sh --dir ./public/assets`. All css and js files in `public/assets` will be minified.

### Seeding database offline with pdo_connect() (optional)
To offline seed database, run `php ./bin/pdo-connect.php -db ./app/databases/database-name`.  
Note: database can be seeded automatically on first start.

### Running dev server
In this dir run `php ./bin/serve.php`.  
You can also specify IP, port, preload script, document root and server threads,  
eg `php ./bin/serve.php --ip 127.0.0.1 --port 8080 --docroot ./public --preload ../tmp/app-preload.php --threads 4`.

### Deploy on shared hosting in a subdirectory
Note: all routing and asset paths in views must be appropriate
1) move `./public` directory to `../public_html/app-name` (where `public_html` is document root in your hosting)
2) edit `public_html/app-name/index.php` and correct the include path (here: `include '../../your-app/app/entrypoint.php';`)
3) test application: `php ./bin/serve.php --docroot ../public_html`
