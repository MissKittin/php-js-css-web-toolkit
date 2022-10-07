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


# Application content

### Controllers
* `about.php` - `/about` (About toolkit) (has view)
* `check-date.php` - `/check-date` (check_date() test) (has view)
* `database-test.php` - `/database-test` (Database libraries test) (has view)
* `home.php` - `/` (has view)
* `http_error.php` - `/http_error_test` (HTTP errors) and 404 page (has view)
* `login-component-test.php` - `/login-component-test` (Login component test) (has view)
* `login-library-test.php` - `/login-library-test` (Login library test) (has view)
* `obsfucate-html.php` - `/obsfucate-html` (HTML obsfucator test) (has view)
* `preprocessing-test.php` - `/preprocessing-test` (PHP preprocessing test) (has view)
* `robots-sitemap.php` - `/robots.txt` and `/sitemap.xml`

### Models <-> controllers
* `database_test_abstract.php` <-> `database-test.php`
* `login_component_test_credentials.php` <-> `login-component-test.php`
* `login_library_test_credentials.php` <-> `login-library-test.php`

### Databases (host:port db_name user password)
One of these databases is used in the `database_test_abstract.php` model.  
you can select a database via the `DB_TYPE` environment variable (default: `sqlite`).
* `pgsql` (127.0.0.1:5432 sampledb postgres postgres)
* `mysql` ([::1]:3306 sampledb root (no password))
* `sqlite` (`./var/lib/databases/sqlite/database.sqlite3`)
You can configure the database connection through the following environment variables:
* `PGSQL_HOST`
* `PGSQL_PORT`
* `PGSQL_SOCKET` (has priority over the host/port)
* `PGSQL_DBNAME`
* `PGSQL_CHARSET`
* `PGSQL_USER`
* `PGSQL_PASSWORD`
* `MYSQL_HOST`
* `MYSQL_PORT`
* `MYSQL_SOCKET` (has priority over the host/port)
* `MYSQL_DBNAME`
* `MYSQL_CHARSET`
* `MYSQL_USER`
* `MYSQL_PASSWORD`
* `SQLITE_PATH`
* `DB_IGNORE_ENV=true` (ignores all the variables above and the DB_TYPE)

### Shared code
* `default_http_headers.php` - for all controllers
* `ob_adapter.php` - `ob_start()` handler
* `ob_cache.php`
* `session_start.php` - session handler

### Templates
* `default` - simple template management

### Tools
* `install-assets.php`
* `remove-samples.php`
* `replace-public-index-with-link.php`
* `session-clean.php` - remove stale sessions (if the application stores the session content in files)
