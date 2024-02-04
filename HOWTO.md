# Testing toolkit
To check if all libraries and components will work on your server, run:
```
php ./bin/run-php-lib-tests.php
php ./bin/run-php-components-tests.php
```
You can also run the HTTP server to test the CSS and Js libraries on the web browser:
```
php ./bin/run-phtml-tests.php
```

# Packing the toolkit into Phar
If a better option is to pack the libraries and components into one file,  
use the `mkphar.php` utility:
```
php -d phar.readonly=0 ./bin/mkphar.php --compress=gz --source=components --source=lib --ignore=assets/ --ignore=bin/ --ignore=tests/ --ignore=tmp/ --ignore=README.md --output=toolkit.phar
```
and use the generated Phar, eg:
```
require 'phar://'
.	'./toolkit.phar'
.	'/components/admin_panel/admin_panel.php'
;
```

# Installing Composer
To install Composer, run:
```
php ./bin/get-composer.php
```
Composer will be installed in `./bin/composer.phar`


# How to create application

### Autoloading
You can use the `autoloader-generator.php` tool  
to generate an autoloader script for functions and classes.   
For more info, run:
```
php ./bin/autoloader-generator.php
```

### Configuring URL routing
Edit `app/entrypoint.php`  
You can use eg. `uri_router.php` library or `superclosure_router` component.

### Creating assets
See `assets_compiler.php` library.

### Creating database configuration for pdo_connect()
See `pdo_connect.php` library.

### Queue worker
It is better to do more time-consuming tasks outside the main application.  
The `queue_worker.php` library and the `queue-worker.php` tool are available for this purpose.  
Note: this library uses the features of *nix systems and is only intended for them.  
For more info, see `./lib/queue_worker.php` and run:
```
php ./bin/queue-worker.php
```

### WebSockets
Note: this tool uses the features of *nix systems and is only intended for them.  
For more info, run:
```
php ./bin/websockets.php
```

### Compiling assets
Run
```
php ./bin/assets-compiler.php ./app/assets ./public/assets
```
or you can watch for changes:
```
php ./bin/file-watch.php "php ./bin/assets-compiler.php ./app/assets ./public/assets" ./app/assets
```

### Minifying assets - webdev.sh client
Run
```
php ./bin/webdev.sh --dir ./public/assets
```
All css and js files in `public/assets` will be minified.

### Minifying assets - matthiasmullie's minifier
The way of using `./bin/matthiasmullie-minify.php` is the same as in the webdevsh client.  
Note: before use, you need to install the composer and minifier package:
```
mkdir ./bin/composer
php ./bin/composer.phar --optimize-autoloader --no-cache --working-dir=./bin/composer require matthiasmullie/minify
```

### Seeding database offline with pdo_connect() (optional)
To offline seed database, run
```
php ./bin/pdo-connect.php --db ./app/databases/database-name
```
Note: database can be seeded automatically on first start.

### Running dev server
In this dir run
```
php ./bin/serve.php
```
You can also specify IP, port, preload script, document root and server threads, eg.
```
php ./bin/serve.php --ip 127.0.0.1 --port 8080 --docroot ./public --preload ../tmp/app-preload.php --threads 4
```

### Preloading application
To take advantage of the possibility of preloading the application,  
you can generate the preloader script using the `opcache-preload-generator.php` tool.  
For more info, run:
```
php ./bin/opcache-preload-generator.php
```

### Task scheduling
For info, run:
```
php ./bin/cron.php
```

### Rotating logs
Text files can swell to large sizes - this can degrade application performance.  
To prevent this from happening, you can use the `logrotate.php` utility.  
For more info, run:
```
php ./bin/logrotate.php
```

### Removing symlinks
In some cases, you may need to replace symbolic links with the target files.  
This process can be automated using the `link2file.php` tool:
```
php ./bin/link2file.php ./app
```

### Deploy on shared hosting in a subdirectory
Note: all routing and asset paths in views must be appropriate  
Note: if you don't have something like `public_html`, good luck
1) upload `./public` directory to `public_html/app-name` (where `public_html` is document root in your hosting)
2) upload application to `app-name` (next to the `public_html` directory)
3) edit `public_html/app-name/index.php` and correct the require path (here: `require '../../your-app/app/entrypoint.php';`)
4) test application: `php ./bin/serve.php --docroot ../public_html`

# Apache configuration
You need to `a2enmod rewrite`  
Optionally you can also `a2enmod headers`  

HTTP only:
```
<VirtualHost *:80>
	ServerName myapp.com
	ServerAlias www.myapp.com

	HostnameLookups off

	DocumentRoot /absolute/path/to/php-js-css-web-toolkit/public

	<Directory "/absolute/path/to/php-js-css-web-toolkit/public">
		Options Indexes FollowSymlinks
		AllowOverride All
		Require all granted
	</Directory>

	# Use PHP-FPM (you need to a2enmod proxy_fcgi)
	#<FilesMatch \.php$>
	#	SetHandler "proxy:unix:/run/php/php7.3-fpm.sock|fcgi://localhost"
	#</FilesMatch>

	# Proxy to the websockets.php server
	#RewriteEngine on
	#RewriteCond %{REQUEST_URI} !/ws$
	#RewriteCond %{HTTP:Upgrade} websocket [NC]
	#RewriteCond %{HTTP:Connection} Upgrade [NC]
	#RewriteRule .* ws://localhost:8081%{REQUEST_URI} [P,L]

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

HTTPS only:
```
<VirtualHost *:80>
	RewriteEngine on
	RewriteCond %{HTTPS} !=on
	RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>
<VirtualHost *:443>
	ServerName myapp.com
	ServerAlias www.myapp.com

	HostnameLookups off

	# You need to a2enmod ssl
	SSLEngine on
	SSLCertificateFile /etc/apache2/certificates/myapp.com.crt
	SSLCertificateKeyFile /etc/apache2/certificates/myapp.com.key

	# You need to a2enmod socache_shmcb
	SSLSessionCache shmcb:/run/httpd/sslcache(1024000)
	SSLSessionCacheTimeout 600
	KeepAlive On
	MaxKeepAliveRequests 50
	KeepAliveTimeout 70
	SSLProtocol TLSv1 TLSv1.1 TLSv1.2
	SSLCipherSuite HIGH:!aNULL:!MD5
	Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

	DocumentRoot /absolute/path/to/php-js-css-web-toolkit/public

	<Directory "/absolute/path/to/php-js-css-web-toolkit/public">
		Options Indexes FollowSymlinks
		AllowOverride All
		Require all granted
	</Directory>

	# Use PHP-FPM (you need to a2enmod proxy_fcgi)
	#<FilesMatch \.php$>
	#	SetHandler "proxy:unix:/run/php/php7.3-fpm.sock|fcgi://localhost"
	#</FilesMatch>

	# Proxy to the websockets.php server
	#RewriteEngine on
	#RewriteCond %{REQUEST_URI} !/ws$
	#RewriteCond %{HTTP:Upgrade} websocket [NC]
	#RewriteCond %{HTTP:Connection} Upgrade [NC]
	#RewriteRule .* ws://localhost:8081%{REQUEST_URI} [P,L]

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

# nginx configuration
Development:
```
server {
	listen 8080;
	listen [::]:8080;

	root /absolute/path/to/php-js-css-web-toolkit/public;
	index index.php index.html index.htm;

	# Enable directory listing
	#autoindex on;

	location / {
		# Allow directory listing
		#try_files $uri $uri/ /index.php?$query_string;

		# Disallow directory listing
		try_files $uri /index.php?$query_string;
	}

	# Proxy to the websockets.php server
	#location /ws {
	#	if ($http_connection != "Upgrade") {
	#		rewrite ^ /index.php last;
	#		break;
	#	}

	#	set $or 0;
	#	if ($http_upgrade = "websocket") {
	#		set $or 1;
	#	}
	#	if ($http_upgrade = "WebSocket") {
	#		set $or 1;
	#	}
	#	if ($or = 0) {
	#		rewrite ^ /index.php last;
	#		break;
	#	}

	#	proxy_pass http://127.0.0.1:8081;
	#	proxy_http_version 1.1;
	#	proxy_set_header Upgrade $http_upgrade;
	#	proxy_set_header Connection "Upgrade";
	#	proxy_set_header Host $host;
	#}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php7.3-fpm.sock;

		# Remove the following code
		# if the application is to be
		# behind a proxy server
		fastcgi_param HTTP_X_FORWARDED_PROTO "";
		fastcgi_param HTTP_X_FORWARDED_HOST "";
		fastcgi_param HTTP_X_FORWARDED_PORT "";
		fastcgi_param HTTP_X_REAL_IP "";
	}

	location ~ /\.ht {
		rewrite ^ /index.php last;
	}
}
```

HTTP only:
```
server {
	listen 80;
	listen [::]:80;

	root /absolute/path/to/php-js-css-web-toolkit/public;
	index index.php index.html index.htm;

	server_name myapp.com www.myapp.com;

	# Enable directory listing
	#autoindex on;

	location / {
		# Allow directory listing
		#try_files $uri $uri/ /index.php?$query_string;

		# Disallow directory listing
		try_files $uri /index.php?$query_string;
	}

	# Proxy to the websockets.php server
	#location /ws {
	#	if ($http_connection != "Upgrade") {
	#		rewrite ^ /index.php last;
	#		break;
	#	}

	#	set $or 0;
	#	if ($http_upgrade = "websocket") {
	#		set $or 1;
	#	}
	#	if ($http_upgrade = "WebSocket") {
	#		set $or 1;
	#	}
	#	if ($or = 0) {
	#		rewrite ^ /index.php last;
	#		break;
	#	}

	#	proxy_pass http://127.0.0.1:8081;
	#	proxy_http_version 1.1;
	#	proxy_set_header Upgrade $http_upgrade;
	#	proxy_set_header Connection "Upgrade";
	#	proxy_set_header Host $host;
	#}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php7.3-fpm.sock;

		# Remove the following code
		# if the application is to be
		# behind a proxy server
		fastcgi_param HTTP_X_FORWARDED_PROTO "";
		fastcgi_param HTTP_X_FORWARDED_HOST "";
		fastcgi_param HTTP_X_FORWARDED_PORT "";
		fastcgi_param HTTP_X_REAL_IP "";
	}

	location ~ /\.ht {
		rewrite ^ /index.php last;
	}
}
```

HTTPS only:
```
server {
	listen 80;
	listen [::]:80;

	server_name myapp.com www.myapp.com;
	return 301 https://$server_name$request_uri;
}
server {
	listen 443 ssl http2;
	listen [::]:443 ssl http2;

	include snippets/ssl-myapp.com.conf;
	include snippets/ssl-params.conf;

	ssl_session_cache shared:SSL:10m;
	ssl_session_timeout 10m;
	keepalive_timeout 70;
	ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
	ssl_ciphers HIGH:!aNULL:!MD5;
	add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

	root /absolute/path/to/php-js-css-web-toolkit/public;
	index index.php index.html index.htm;

	server_name myapp.com www.myapp.com;

	# Enable directory listing
	#autoindex on;

	location / {
		# Allow directory listing
		#try_files $uri $uri/ /index.php?$query_string;

		# Disallow directory listing
		try_files $uri /index.php?$query_string;
	}

	# Proxy to the websockets.php server
	#location /ws {
	#	if ($http_connection != "Upgrade") {
	#		rewrite ^ /index.php last;
	#		break;
	#	}

	#	set $or 0;
	#	if ($http_upgrade = "websocket") {
	#		set $or 1;
	#	}
	#	if ($http_upgrade = "WebSocket") {
	#		set $or 1;
	#	}
	#	if ($or = 0) {
	#		rewrite ^ /index.php last;
	#		break;
	#	}

	#	proxy_pass http://127.0.0.1:8081;
	#	proxy_http_version 1.1;
	#	proxy_set_header Upgrade $http_upgrade;
	#	proxy_set_header Connection "Upgrade";
	#	proxy_set_header Host $host;
	#}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php7.3-fpm.sock;

		# Remove the following code
		# if the application is to be
		# behind a proxy server
		fastcgi_param HTTP_X_FORWARDED_PROTO "";
		fastcgi_param HTTP_X_FORWARDED_HOST "";
		fastcgi_param HTTP_X_FORWARDED_PORT "";
		fastcgi_param HTTP_X_REAL_IP "";
	}

	location ~ /\.ht {
		rewrite ^ /index.php last;
	}
}
```
