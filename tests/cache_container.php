<?php
	/*
	 * cache_container.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
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
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		foreach([
			'cache_container.json',
			'cache_container.json.lock',
			'cache_container_realtime.json',
			'cache_container_realtime.json.lock',
			'cache_container.sqlite3'
		] as $file)
			@unlink(__DIR__.'/tmp/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	$cache_drivers=[
		'cache_driver_none'=>null,
		'cache_driver_file'=>[
			'file'=>__DIR__.'/tmp/cache_container.json',
			'lock_file'=>__DIR__.'/tmp/cache_container.json.lock'
		],
		'cache_driver_file_realtime'=>[
			'file'=>__DIR__.'/tmp/cache_container_realtime.json',
			'lock_file'=>__DIR__.'/tmp/cache_container_realtime.json.lock'
		],
		'cache_driver_pdo'=>[
			'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/cache_container.sqlite3')
		],
		'cache_driver_phpredis'=>[
			'address'=>'127.0.0.1'
		]
	];
	$errors=[];

	foreach(['cache_container', 'cache_container_lite'] as $cache_container)
	{
		echo ' -> Testing container '.$cache_container.PHP_EOL;

		foreach($cache_drivers as $driver_name=>$driver_params)
		{
			if(($cache_container === 'cache_container_lite') && ($driver_name === 'cache_driver_none'))
			{
				echo '  -> Skipping cache_driver_none'.PHP_EOL;
				continue;
			}

			echo '  -> Testing driver '.$driver_name.PHP_EOL;

			try {
				$current_container=new $cache_container(new $driver_name($driver_params));

				if($cache_container === 'cache_container')
				{
					echo '   -> put_temp/get';
						$current_container->put_temp('put_temp_test', 'good');
						if($current_container->get('put_temp_test') === 'good')
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							$errors[$cache_container.' => '.$driver_name]='[TEST] put_temp/get failed';
							echo ' [FAIL]'.PHP_EOL;
						}
				}
				else
					echo '   -> Skipping put_temp/get'.PHP_EOL;

				if($cache_container === 'cache_container')
				{
					echo '   -> put_temp 2/isset';
						$current_container->put_temp('put_tempb_test', 'good', 2);
						sleep(3);
						if(!$current_container->isset('put_tempb_test'))
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							$errors[$cache_container.' => '.$driver_name]='[TEST] put_temp 2/isset failed';
							echo ' [FAIL]'.PHP_EOL;
						}
				}
				else
					echo '   -> Skipping put_temp 2/isset'.PHP_EOL;

				echo '   -> put/increment/get';
					$current_container->put('increment_test', 2);
					$current_container->increment('increment_test');
					if($current_container->get('increment_test') == 3)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/increment/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/increment 3/get';
					$current_container->put('incrementb_test', 2);
					$current_container->increment('incrementb_test', 3);
					if($current_container->get('incrementb_test') == 5)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/increment 3/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/decrement/get';
					$current_container->put('decrement_test', 3);
					$current_container->decrement('decrement_test');
					if($current_container->get('decrement_test') == 2)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/decrement/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/decrement 3/get';
					$current_container->put('decrement_test', 5);
					$current_container->decrement('decrement_test', 3);
					if($current_container->get('decrement_test') == 2)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/decrement 3/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> isset';
					if($current_container->isset('decrement_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> pull/isset';
					if($current_container->pull('decrement_test') == 2)
						echo ' [ OK ]';
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] pull/isset pull failed';
						echo ' [FAIL]';
					}
					if(!$current_container->isset('decrement_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] pull/isset isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> unset/isset';
					$current_container->unset('increment_test');
					if(!$current_container->isset('increment_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] unset/isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put 2/isset';
					$current_container->put('timeout_test', 'value', 2);
					sleep(3);
					if(!$current_container->isset('timeout_test'))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put 2/isset failed';
						echo ' [FAIL]'.PHP_EOL;
					}

				echo '   -> put/flush/get';
					$current_container->put('flush_test', 'example');
					$current_container->flush();
					if($current_container->get('flush_test') === null)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						$errors[$cache_container.' => '.$driver_name]='[TEST] put/flush/get failed';
						echo ' [FAIL]'.PHP_EOL;
					}
			} catch(Throwable $error) {
				$errors[$cache_container.' => '.$driver_name]=$error->getMessage();
				echo '  <- Testing driver '.$driver_name.' [FAIL]'.PHP_EOL;
			}
		}
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error_class=>$error_content)
			echo $error_class.': '.$error_content.PHP_EOL;

		exit(1);
	}
?>