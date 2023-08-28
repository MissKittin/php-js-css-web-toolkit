<?php
	/*
	 * redis_connect.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Hint:
	 *  you can setup Redis credentials by environment variables
	 *  variables:
	 *   TEST_REDIS_HOST (default: 127.0.0.1)
	 *   TEST_REDIS_PORT (default: 6379)
	 *   TEST_REDIS_DBINDEX (default: 0)
	 *   TEST_REDIS_USER
	 *   TEST_REDIS_PASSWORD
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  redis extension is required
	 */

	if(!extension_loaded('redis'))
	{
		echo 'redis extension is not loaded'.PHP_EOL;
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

	$_redis_host=getenv('TEST_REDIS_HOST');
	$_redis_port=getenv('TEST_REDIS_PORT');
	$_redis_dbindex=getenv('TEST_REDIS_DBINDEX');
	$_redis_user=getenv('TEST_REDIS_USER');
	$_redis_password=getenv('TEST_REDIS_PASSWORD');
	$_redis_auth_user='';
	$_redis_auth_password='';
	if($_redis_host === false)
		$_redis_host='127.0.0.1';
	if($_redis_port === false)
		$_redis_port='6379';
	if($_redis_dbindex === false)
		$_redis_dbindex='0';
	if($_redis_user !== false)
		$_redis_auth_user='$auth["user"]="'.$_redis_user.'";';
	if($_redis_password !== false)
		$_redis_auth_password='$auth["pass"]="'.$_redis_password.'";';

	echo ' -> Removing temporary files';
		rmdir_recursive(__DIR__.'/tmp/redis_connect');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating database definitions';
		@mkdir(__DIR__.'/tmp');
		mkdir(__DIR__.'/tmp/redis_connect');
		file_put_contents(__DIR__.'/tmp/redis_connect/config.php', '<?php
			$auth=null;
			'.$_redis_auth_user.$_redis_auth_password.'
			return [
				"host"=>"'.$_redis_host.'",
				"port"=>'.$_redis_port.',
				"dbindex"=>'.$_redis_dbindex.',
				"auth"=>$auth
			];
		?>');
	echo ' [ OK ]'.PHP_EOL;

	try {
		$redis_handler=redis_connect(__DIR__.'/tmp/redis_connect');

		echo ' -> Testing redis_connect';
			if($redis_handler instanceof Redis)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
	} catch(Throwable $error) {
		echo ' <- Testing redis_connect [FAIL]'.PHP_EOL;
		echo ' caught: '.$error->getMessage().PHP_EOL;
	}

	if($failed)
		exit(1);
?>