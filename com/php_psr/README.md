# PHP PSR
A component providing PSR compliance for toolkit libraries

## Under construction
This **PRERELEASE** version of PHP PSR is under construction!  
Please remember that the standards implementation will continue to change through the product's development cycle.  
Thanks!
- MissKittin@GitHub

## Implemented PSR standards
* PSR-3: Logger Interface
* PSR-11: Container interface

## Required libraries
* `ioc_container.php` (PSR-11)
* `logger.php` (PSR-3)
* `string_interpolator.php` (PSR-3)
* `bin/get-composer.php` (for tests)

## Required packages
* `psr/container` (PSR-11)
* `psr/log` (PSR-3)

## Note
Throws an `php_psr_exception` on error

## Usage
Just include the component:
```
require './com/php_psr/main.php';
```
**Warning:** if an `php_psr_exception` occurs, it will be ignored

If you want to include a subcomponent, use e.g:
```
require './com/php_psr/container.php';
```
**Note:** if an error occurs, a `php_psr_exception` will be thrown

### Subcomponents
* `container.php` (PSR-11)
* `logger.php` (PSR-3)

### Logger
Wrapper for object from `logger.php` library:
```
$logger=new log_to_psr(new log_to_txt([
	'app_name'=>'test_app',
	// another params
]));

$logger->emergency('My message');
$logger->emerg('My message');
$logger->alert('My message');
$logger->critical('My message');
$logger->crit('My message');
$logger->error('My message');
$logger->warning('My message');
$logger->warn('My message');
$logger->notice('My message');
$logger->info('My message');
$logger->debug('My message');
$logger->log(
	'debug', // method name
	'My message'
);

$logger->emergency(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->emerg(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->alert(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->critical(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->crit(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->error(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->warning(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->warn(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->notice(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->info(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->debug(
	'My message: {message}',
	['message'=>'interpolated string']
);
$logger->log(
	'debug', // method name
	'My message: {message}',
	['message'=>'interpolated string']
);
```
For more info see `logger.php` library.

### Container
Two classes implementing the PSR standard via composition:
```
$container=new ioc_closure_container_psr();
$autowired_container=new ioc_autowired_container_psr();
$autowired_container=new ioc_autowired_container_psr(true); // with cache
```
For more info see `ioc_container.php` library.

## PHP-FIG documentation
[PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)  
[PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/)  

## Portability
Create a `./lib` directory  
and copy the required libraries to this directory.  
Libraries in this directory have priority over `../../lib`.
