<?php
	/*
	 * predis_connect.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *  looks for a tool at ../../bin
	 *
	 * Hint:
	 *  you can setup Redis credentials by environment variables
	 *  variables:
	 *   TEST_REDIS_HOST (default: 127.0.0.1)
	 *   TEST_REDIS_SOCKET (has priority over the HOST)
	 *    eg. /var/run/redis/redis.sock
	 *   TEST_REDIS_PORT (default: 6379)
	 *   TEST_REDIS_DBINDEX (default: 0)
	 *   TEST_REDIS_PASSWORD
	 *
	 * Warning:
	 *  you must set TEST_REDIS_PREDIS=yes
	 *  get-composer.php tool is recommended
	 */

	if(getenv('TEST_REDIS_PREDIS') !== 'yes')
	{
		echo 'TEST_REDIS_PREDIS environment variable is not "yes"'.PHP_EOL;
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

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/predis_connect');
		@unlink(__DIR__.'/tmp/predis_connect/config.php');
	echo ' [ OK ]'.PHP_EOL;

	if(!file_exists(__DIR__.'/tmp/.composer/vendor/predis/predis'))
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

		echo '  -> Installing predis/predis'.PHP_EOL;
			system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
			.	'--no-cache '
			.	'"--working-dir='.__DIR__.'/tmp/.composer" '
			.	'require predis/predis'
			);
	}

	echo ' -> Including composer autoloader';
		if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	if(!class_exists('\Predis\Client'))
	{
		echo ' <- predis/predis package is not installed [FAIL]'.PHP_EOL;
		exit(1);
	}

	echo ' -> Setting up credentials'.PHP_EOL;
		$_redis=[
			'credentials'=>[
				'host'=>'127.0.0.1',
				'port'=>6379,
				'socket'=>null,
				'dbindex'=>0,
				'user'=>null,
				'password'=>null
			],
			'connection_options'=>[
				'timeout'=>0,
				'retry_interval'=>0,
				'read_timeout'=>0
			]
		];
		foreach(['host', 'port', 'socket', 'dbindex', 'user', 'password'] as $_redis['_parameter'])
		{
			$_redis['_variable']='TEST_REDIS_'.strtoupper($_redis['_parameter']);
			$_redis['_value']=getenv($_redis['_variable']);

			if($_redis['_value'] !== false)
			{
				echo '  -> Using '.$_redis['_variable'].'="'.$_redis['_value'].'" as Redis '.$_redis['_parameter'].PHP_EOL;
				$_redis['credentials'][$_redis['_parameter']]=$_redis['_value'];
			}
		}
		if($_redis['credentials']['socket'] !== null)
		{
			$_redis['credentials']['host']='unix://'.$_redis['credentials']['socket'];
			$_redis['credentials']['port']=0;
		}
		if($_redis['credentials']['password'] !== null)
			$_redis['_credentials_auth']['pass']=$_redis['credentials']['password'];
		$_redis['_predis']=[
			[
				'scheme'=>'tcp',
				'host'=>$_redis['credentials']['host'],
				'port'=>$_redis['credentials']['port'],
				'database'=>$_redis['credentials']['dbindex']
			],
			[
				'scheme'=>'unix',
				'path'=>$_redis['credentials']['socket'],
				'database'=>$_redis['credentials']['dbindex']
			]
		];
		if($_redis['credentials']['password'] !== null)
		{
			$_redis['_predis'][0]['password']=$_redis['credentials']['password'];
			$_redis['_predis'][1]['password']=$_redis['credentials']['password'];
		}

	echo ' -> Creating database definition';
		$connetion_type=0;
		if($_redis['credentials']['socket'] !== null)
			$connetion_type=1;

		$db_content='';
		foreach($_redis['_predis'][$connetion_type] as $key=>$value)
			$db_content.='"'.$key.'"=>"'.$value.'",';
		$db_content=substr($db_content, 0, -1);

		file_put_contents(__DIR__.'/tmp/predis_connect/config.php', '<?php return ['.$db_content.']; ?>');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing predis_connect';
		try {
			$redis_handle=predis_connect(__DIR__.'/tmp/predis_connect');
			$redis_handle->connect();

			$redis_handle->del('predis_connect_test_set');
			$redis_handle->del('predis_connect_test_set_ex');
			$redis_handle->del('predis_connect_test_get');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing predis_phpredis_proxy'.PHP_EOL;
		$proxy_handle=new predis_phpredis_proxy($redis_handle);
		echo '  -> set'.PHP_EOL;
			echo '   -> no expire';
				if(is_bool($proxy_handle->set('predis_connect_test_set', 'passed')))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '   -> expire';
				if(is_bool($proxy_handle->set('predis_connect_test_set_ex', 'passed', ['ex'=>60])))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
		echo '  -> scan';
			$iterator=null;
			$scan_failed=false;
			do {
				$keys=$proxy_handle->scan($iterator, 'predis_connect_test_*');

				if($keys === false)
				{
					$scan_failed=true;
					break;
				}

				foreach($keys as $key)
					break 2;

				$scan_failed=false;
			}
			while($iterator > 0);
			if($scan_failed)
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> get';
			if($proxy_handle->get('predis_connect_test_get') === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>