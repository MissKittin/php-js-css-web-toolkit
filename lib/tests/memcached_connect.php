<?php
	/*
	 * memcached_connect.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Hint:
	 *  you can setup Memcached credentials by environment variables
	 *  variables:
	 *   TEST_MEMCACHED_HOST (default: 127.0.0.1)
	 *   TEST_MEMCACHED_SOCKET (has priority over the HOST)
	 *    eg. /var/run/memcached/memcached.sock
	 *   TEST_MEMCACHED_PORT (default: 11211)
	 *  you can also use the memcached.php package instead of memcached extension:
	 *   TEST_MEMCACHED_CM=yes (default: no)
	 *
	 * Warning:
	 *  you must set TEST_MEMCACHED=yes
	 *  rmdir_recursive.php library is required
	 *  memcached extension is required or
	 *   clickalicious_memcached.php library is required
	 */

	if(getenv('TEST_MEMCACHED') !== 'yes')
	{
		echo 'TEST_MEMCACHED environment variable is not "yes"'.PHP_EOL;
		exit(1);
	}

	if(
		(!class_exists('Memcached')) &&
		(getenv('TEST_MEMCACHED_CM') === 'yes')
	){
		echo '  -> Including clickalicious_memcached.php';
			if(is_file(__DIR__.'/../lib/clickalicious_memcached.php'))
			{
				if(@(include __DIR__.'/../lib/clickalicious_memcached.php') === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../clickalicious_memcached.php'))
			{
				if(@(include __DIR__.'/../clickalicious_memcached.php') === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;

		if(!file_exists(__DIR__.'/tmp/.composer/vendor/clickalicious/memcached.php'))
		{
			@mkdir(__DIR__.'/tmp');
			@mkdir(__DIR__.'/tmp/.composer');

			if(file_exists(__DIR__.'/../../bin/composer.phar'))
				$_composer_binary=__DIR__.'/../../bin/composer.phar';
			else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
				$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
			else if(file_exists(__DIR__.'/../../bin/get-composer.php'))
			{
				echo '  -> Downloading composer'.PHP_EOL;

				system(''
				.	'"'.PHP_BINARY.'" '
				.	'"'.__DIR__.'/../../bin/get-composer.php" '
				.	'"'.__DIR__.'/tmp/.composer"'
				);

				if(!file_exists(__DIR__.'/tmp/.composer/composer.phar'))
				{
					echo '  <- composer download failed [FAIL]'.PHP_EOL;
					exit(1);
				}

				$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
			}
			else
			{
				echo 'Error: get-composer.php tool not found'.PHP_EOL;
				exit(1);
			}

			echo '  -> Installing clickalicious/memcached.php'.PHP_EOL;
				system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
				.	'--no-cache '
				.	'"--working-dir='.__DIR__.'/tmp/.composer" '
				.	'require clickalicious/memcached.php'
				);
		}

		echo '  -> Including composer autoloader';
			if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;

		if(!class_exists('\Clickalicious\Memcached\Client'))
		{
			echo '  <- clickalicious/memcached.php package is not installed [FAIL]'.PHP_EOL;
			exit(1);
		}
	}

	if(!class_exists('Memcached'))
	{
		echo 'memcached extension is not loaded'.PHP_EOL;
		echo 'set TEST_MEMCACHED_CM=yes to use polyfill from clickalicious_memcached.php library'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../lib/rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	if(class_exists('clickalicious_memcached'))
	{
		echo ' -> Setting clickalicious_memcached class as Memcached'.PHP_EOL;
		memcached_connect_bridge::set_class('clickalicious_memcached', function(...$arguments){
			return new clickalicious_memcached(
				...$arguments
			);
		});
	}

	$failed=false;

	$_memcached_host=getenv('TEST_MEMCACHED_HOST');
	$_memcached_port=getenv('TEST_MEMCACHED_PORT');
	$_memcached_socket=getenv('TEST_MEMCACHED_SOCKET');
	if($_memcached_host === false)
		$_memcached_host='127.0.0.1';
	if($_memcached_port === false)
		$_memcached_port='11211';
	if($_memcached_socket !== false)
	{
		$_memcached_host=$_memcached_socket;
		$_memcached_port='0';
	}

	echo ' -> Removing temporary files';
		rmdir_recursive(__DIR__.'/tmp/memcached_connect');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating database definitions';
		@mkdir(__DIR__.'/tmp');
		mkdir(__DIR__.'/tmp/memcached_connect');
		file_put_contents(__DIR__.'/tmp/memcached_connect/config.php', '<?php
			return [[
				"host"=>"'.$_memcached_host.'",
				"port"=>'.$_memcached_port.'
			]];
		?>');
	echo ' [ OK ]'.PHP_EOL;

	try {
		$memcached_handle=memcached_connect(__DIR__.'/tmp/memcached_connect');

		echo ' -> Testing memcached_connect';
			if(
				($memcached_handle instanceof Memcached) ||
				($memcached_handle instanceof clickalicious_memcached)
			){
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Testing connection to memcached server';
					$memcached_handle->set(
						'memcached_connect_test_key',
						'memcached_connect_test_value'
					);
					if($memcached_handle->get('memcached_connect_test_key') === 'memcached_connect_test_value')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
					$memcached_handle->delete('memcached_connect_test_key');
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
	} catch(Throwable $error) {
		echo ' <- Testing memcached_connect [FAIL]'.PHP_EOL;
		echo ' caught: '.$error->getMessage().PHP_EOL;
	}

	if($failed)
		exit(1);
?>