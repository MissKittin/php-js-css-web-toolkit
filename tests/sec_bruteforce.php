<?php
	/*
	 * sec_bruteforce.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Hint:
	 *  you can setup Redis server address and port by environment variables
	 *  variables:
	 *   TEST_REDIS_HOST (default: 127.0.0.1)
	 *   TEST_REDIS_PORT (default: 6379)
	 *  to skip cleaning the Redis database,
	 *   run the test with the --no-redis-clean parameter
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 *  redis extension is recommended
	 */

	foreach(['PDO', 'pdo_sqlite'] as $extension)
		if(!extension_loaded($extension))
		{
			echo $extension.' extension is not loaded'.PHP_EOL;
			exit(1);
		}

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			ob_end_flush();
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		foreach([
			'sec_bruteforce.sqlite3',
			'sec_bruteforce_timeout.sqlite3',
			'sec_bruteforce.json',
			'sec_bruteforce.json.lock',
			'sec_bruteforce_resume.sqlite3',
			'sec_bruteforce_resume.json',
			'sec_bruteforce_resume.json.lock',
			'sec_bruteforce_timeout.json',
			'sec_bruteforce_timeout.json.lock',
			'sec_bruteforce_mixed.sqlite3'
		] as $file)
			@unlink(__DIR__.'/tmp/'.$file);
	echo ' [ OK ]'.PHP_EOL;

		if(isset($argv[1]))
	{
		switch($argv[1])
		{
			case 'mysql':
				$pdo_handler=new PDO('mysql:'
					.'host=[::1];'
					.'port=3306;'
					.'dbname=sec-bruteforce-test',
					'root',
					''
				);
		}

		$pdo_handler->exec('DROP TABLE sec_bruteforce');
	}
	if(!isset($pdo_handler))
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce.sqlite3');


	$GLOBALS['_redis_handler']=null;
	if(extension_loaded('redis'))
	{
		$GLOBALS['_redis_handler']=new Redis();

		$_redis_host=getenv('TEST_REDIS_HOST');
		$_redis_port=getenv('TEST_REDIS_PORT');

		if($_redis_host === false)
			$_redis_host='127.0.0.1';
		if($_redis_port === false)
			$_redis_port=6379;

		if($GLOBALS['_redis_handler']->connect($_redis_host, $_redis_port))
		{
			echo ' -> Removing Redis records';
				$GLOBALS['_redis_handler']->del('bruteforce_redis_test__1.2.3.4');
				$GLOBALS['_redis_handler']->del('bruteforce_redis_test_resume__1.2.3.4');
				$GLOBALS['_redis_handler']->del('bruteforce_redis_test_timeout__1.2.3.4');
			echo ' [ OK ]'.PHP_EOL;
		}
		else
		{
			echo ' -> bruteforce_redis connection error [SKIP]'.PHP_EOL;
			$GLOBALS['_redis_handler']=null;
		}

		unset($_redis_host);
		unset($_redis_port);
	}
	else
		echo ' -> bruteforce_redis redis extension is not loaded [SKIP]'.PHP_EOL;

	function setup_objects()
	{
		global $pdo_handler;

		$objects=[
			'bruteforce_pdo'=>new bruteforce_pdo([
				'pdo_handler'=>$pdo_handler,
				'table_name'=>'sec_bruteforce',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4'
			]),
			'bruteforce_json'=>new bruteforce_json([
				'file'=>__DIR__.'/tmp/sec_bruteforce.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4'
			])
		];

		if($GLOBALS['_redis_handler'] !== null)
			$objects['bruteforce_redis']=new bruteforce_redis([
				'redis_handler'=>$GLOBALS['_redis_handler'],
				'prefix'=>'bruteforce_redis_test__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4'
			]);

		return $objects;
	}
	function setup_resume_objects()
	{
		global $pdo_handler;

		$objects=[
			'bruteforce_pdo'=>new bruteforce_pdo([
				'pdo_handler'=>$pdo_handler,
				'table_name'=>'sec_bruteforce',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4'
			]),
			'bruteforce_json'=>new bruteforce_json([
				'file'=>__DIR__.'/tmp/sec_bruteforce_resume.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce_resume.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4'
			])
		];

		if($GLOBALS['_redis_handler'] !== null)
			$objects['bruteforce_redis']=new bruteforce_redis([
				'redis_handler'=>$GLOBALS['_redis_handler'],
				'prefix'=>'bruteforce_redis_test_resume__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4'
			]);

		return $objects;
	}
	function setup_timeout_objects()
	{
		$objects=[
			'bruteforce_timeout_pdo'=>new bruteforce_timeout_pdo([
				'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce_timeout.sqlite3'),
				'table_name'=>'sec_bruteforce',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2
			]),
			'bruteforce_timeout_json'=>new bruteforce_timeout_json([
				'file'=>__DIR__.'/tmp/sec_bruteforce_timeout.json',
				'lock_file'=>__DIR__.'/tmp/sec_bruteforce_timeout.json.lock',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2
			])
		];

		if($GLOBALS['_redis_handler'] !== null)
			$objects['bruteforce_timeout_redis']=new bruteforce_timeout_redis([
				'redis_handler'=>$GLOBALS['_redis_handler'],
				'prefix'=>'bruteforce_redis_test_timeout__',
				'max_attempts'=>3,
				'ip'=>'1.2.3.4',
				'ban_time'=>2
			]);

		return $objects;
	}

	$errors=[];

	foreach(setup_objects() as $class_name=>$class)
	{
		echo ' -> Testing '.$class_name.PHP_EOL;

		echo '  -> add/check/get_attempts';
			for($i=1; $i<=3; ++$i)
			{
				$class->add();

				$check=(!$class->check());
				if($i === 3)
					$check=$class->check();

				if($check && ($class->get_attempts() === $i))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]=$class_name.' add/check/get_attempts';
				}
			}
			echo PHP_EOL;

		echo '  -> del/check/get_attempts';
			$class->del();

			if((!$class->check()) && ($class->get_attempts() === 0))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$class_name.' del/check';
			}
	}

	echo ' -> Testing save'.PHP_EOL;
		foreach(setup_resume_objects() as $class_name=>$class)
		{
			echo '  -> '.$class_name.PHP_EOL;

			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' save add/check/get_attempts';
					}
				}
				echo PHP_EOL;

				unset($class);
		}
	echo ' -> Testing resume'.PHP_EOL;
		foreach(setup_resume_objects() as $class_name=>$class)
		{
			echo '  -> '.$class_name.PHP_EOL;

			echo '   -> check/get_attempts';
				if($class->check() && ($class->get_attempts() === 3))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]=$class_name.' resume check/get_attempts';
				}
				echo PHP_EOL;
		}

	foreach(setup_timeout_objects() as $class_name=>$class)
	{
		echo ' -> Testing '.$class_name.PHP_EOL;

		echo '  -> phase 1 '.PHP_EOL;
			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' add/check/get_attempts phase 1';
					}
				}
				echo PHP_EOL;
			echo '   -> del/check/get_attempts';
				$class->del();

				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' del/check/get_attempts phase 1';
				}

		echo '  -> phase 2 '.PHP_EOL;
			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' add/check/get_attempts phase 2';
					}
				}
				echo PHP_EOL;
			echo '   -> sleep 2'.PHP_EOL;
				sleep(2);
			echo '   -> check/get_attempts';
				if($class->check() && ($class->get_attempts() === 3))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' check/get_attempts phase 2';
				}
			echo '   -> del/check/get_attempts';
				$class->del();

				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' del/check/get_attempts phase 2';
				}

		echo '  -> phase 3 '.PHP_EOL;
			echo '   -> add/check/get_attempts';
				for($i=1; $i<=3; ++$i)
				{
					$class->add();

					$check=(!$class->check());
					if($i === 3)
						$check=$class->check();

					if($check && ($class->get_attempts() === $i))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$errors[]=$class_name.' add/check/get_attempts phase 3';
					}
				}
				echo PHP_EOL;
			echo '   -> sleep 3'.PHP_EOL;
				sleep(3);
			echo '   -> check/get_attempts';
				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' check/get_attempts phase 3';
				}
			echo '   -> del/check/get_attempts';
				$class->del();

				if((!$class->check()) && ($class->get_attempts() === 0))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]=$class_name.' del/check/get_attempts phase 3';
				}
	}

	echo ' -> Testing bruteforce_mixed (PDO)'.PHP_EOL;
		$tempban_hook=new bruteforce_timeout_pdo([
			'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce_mixed.sqlite3'),
			'table_name'=>'temp_ban',
			'max_attempts'=>3,
			'ip'=>'1.2.3.4',
			'ban_time'=>2
		]);
		$permban_hook=new bruteforce_pdo([
			'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce_mixed.sqlite3'),
			'table_name'=>'perm_ban',
			'max_attempts'=>3,
			'ip'=>'1.2.3.4'
		]);

		for($i=1; $i<=2; ++$i)
		{
			echo '  -> temp ban no '.$i.PHP_EOL;
				echo '   -> phase 1';
					for($y=1; $y<=3; ++$y)
						$tempban_hook->add();

					if(bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='bruteforce_mixed temp ban '.$i.' phase 1';
					}
				echo '   -> sleep 3'.PHP_EOL;
					sleep(3);
				echo '   -> phase 2';
					if(!bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$errors[]='bruteforce_mixed temp ban '.$i.' phase 2';
					}
		}
		echo '  -> perm ban'.PHP_EOL;
			echo '   -> phase 1';
				for($y=1; $y<=3; ++$y)
					$tempban_hook->add();

				if(bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='bruteforce_mixed perm ban '.$i.' phase 1';
				}
			echo '   -> sleep 3'.PHP_EOL;
				sleep(3);
			echo '   -> phase 2';
				if(bruteforce_mixed($tempban_hook, $permban_hook, true, 2))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$errors[]='bruteforce_mixed perm ban '.$i.' phase 2/1';
				}
				if($permban_hook->check())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$errors[]='bruteforce_mixed perm ban phase 2/2';
				}

	if(
		($GLOBALS['_redis_handler'] !== null) &&
		(@$argv[1] !== '--no-redis-clean')
	){
		echo ' -> Removing Redis records';
			$GLOBALS['_redis_handler']->del('bruteforce_redis_test__1.2.3.4');
			$GLOBALS['_redis_handler']->del('bruteforce_redis_test_resume__1.2.3.4');
			$GLOBALS['_redis_handler']->del('bruteforce_redis_test_timeout__1.2.3.4');
		echo ' [ OK ]'.PHP_EOL;
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>