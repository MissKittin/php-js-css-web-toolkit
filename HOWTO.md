# How to create application

### Configuring URL routing
Edit switch in `app/router.php` and `include` controller in case

### Creating controllers and models
Create new file in `app/controllers`.  
Set HTTP headers, `include` libraries (`include './lib/library_name.php'`),  
write all php code in this file and put output into `$view` array,  
put view's settings into `$view` array,  
`include` model (`include './app/models/model_name.php'`) and view (`include './app/views/view_name.php'`).  
Now create php file in `app/models`.  
Begin with `<?php $view['content']=function($view) { ?>`.  
Write page's body html with php values from `$view` array (`$view['sample-output']`).   
End with `<?php }; ?>`.

### Creating views
Create new php or html file in `app/views` and place html code in this.  
Use only `$view` array for content, labels etc

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
	Open <?php tag and write dynamic code - these block will be executed during compiling.  
	$current_asset point to asset's directory.  
	Also put css or js files in this directory these will be included in `main.php`.

### Creating database configuration for pdo_connect()
See `lib/pdo_connect.php`

### Cleaning up the repeating code
Create new php file in `app/shared`, paste block of code and `include` this file

### Compiling assets
Run `php ./bin/assets-compiler.php ./app/assets ./public/assets`.

### Minifying assets - webdev.sh client
Run `php ./bin/webdev.sh ./public/assets`. All css and js files in `public/assets` will be minified.

### Seeding database offline with pdo_connect() (optional)
To offline seed database, run `php ./bin/pdo-connect-offline-seed.php database-name`.  
Note: database will be seeded automatically on first start.

### Running dev server
In this dir run `php ./bin/serve.php`.  
You can also specify IP, port and document root, eg `php ./bin/serve.php -ip 127.0.0.1 -port 8080 -docroot ./public`.

### Deploy on shared hosting in a subdirectory
All routing and asset paths in views must be appropriate
1) move `./public` directory to `../public_html/app-name` (where `public_html` is document root in your hosting)
2) edit public_html/app-name/index.php and correct the include path (here: `include '../../your-app/app/routing.php';`)
3) test application: `php ./bin/serve.php -docroot ../public_html`