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
	 *   TEST_MEMCACHED=yes (default: no)
	 *   TEST_MEMCACHED_HOST (default: 127.0.0.1)
	 *   TEST_MEMCACHED_SOCKET (has priority over the HOST)
	 *    eg. /var/run/memcached/memcached.sock
	 *   TEST_MEMCACHED_PORT (default: 11211)
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  memcached extension is required
	 */

	if(!extension_loaded('memcached'))
	{
		echo 'memcached extension is not loaded'.PHP_EOL;
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
		$memcached_handler=memcached_connect(__DIR__.'/tmp/memcached_connect');

		echo ' -> Testing memcached_connect';
			if($memcached_handler instanceof Memcached)
			{
				echo ' [ OK ]'.PHP_EOL;

				echo ' -> Testing connection to memcached server';
					$memcached_handler->set(
						'memcached_connect_test_key',
						'memcached_connect_test_value'
					);
					if($memcached_handler->get('memcached_connect_test_key') === 'memcached_connect_test_value')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
					$memcached_handler->delete('memcached_connect_test_key');
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