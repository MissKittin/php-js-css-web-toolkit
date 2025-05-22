<?php
	/*
	 * clickalicious_memcached.php library test
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
	 *
	 * Warning:
	 *  you must set TEST_MEMCACHED_CM=yes
	 *  clickalicious/memcached.php package is required
	 */

	if(getenv('TEST_MEMCACHED_CM') !== 'yes')
	{
		echo 'TEST_MEMCACHED_CM environment variable is not "yes"'.PHP_EOL;
		exit(1);
	}

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

	echo ' -> Including composer autoloader';
		if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	if(!class_exists('\Clickalicious\Memcached\Client'))
	{
		echo ' <- clickalicious/memcached.php package is not installed [FAIL]'.PHP_EOL;
		exit(1);
	}

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

	$failed=false;
	$exceptions=[];

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

	echo ' -> Testing library'.PHP_EOL;
		$memcached_handle=new clickalicious_memcached('persistentid');
		$memcached_handle->addServers([
			[$_memcached_host, $_memcached_port, $_memcached_socket]
		]);
		echo '  -> set';
			try {
				if($memcached_handle->set('clickalicious_memcached_test_key', 'clickalicious_memcached_test_value') === true)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Clickalicious\Memcached\Exception $exception) {
				echo ' [FAIL] (cm-exception)'.PHP_EOL;
				$failed=true;
				$exceptions[]=$exception->getMessage();
			}
		echo '  -> get';
			try {
				if($memcached_handle->get('clickalicious_memcached_test_key') === 'clickalicious_memcached_test_value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Clickalicious\Memcached\Exception $exception) {
				echo ' [FAIL] (cm-exception)'.PHP_EOL;
				$failed=true;
				$exceptions[]=$exception->getMessage();
			}
		echo '  -> delete';
			try {
				if($memcached_handle->delete('clickalicious_memcached_test_key') === true)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Clickalicious\Memcached\Exception $exception) {
				echo ' [FAIL] (cm-exception)'.PHP_EOL;
				$failed=true;
				$exceptions[]=$exception->getMessage();
			}

	echo ' -> Testing get_client_instance()';
		try {
			$memcached_handle->get_client_instance('0', 0);
			echo ' [FAIL]';
			$failed=true;
		} catch(Exception $error) {
			echo ' [ OK ]';
		}
		try {
			if($memcached_handle->get_client_instance($_memcached_host, $_memcached_port) instanceof Clickalicious\Memcached\Client)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		} catch(Exception $error) {
			echo ' [FAIL] (exception)'.PHP_EOL;
			$failed=true;
		}

	if($failed)
	{
		if(!empty($exceptions))
		{
			echo PHP_EOL;

			foreach($exceptions as $exception)
				echo $exception.PHP_EOL;
		}

		exit(1);
	}
?>