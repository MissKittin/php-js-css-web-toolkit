<?php
	/*
	 * sec_bruteforce.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
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

	$_SERVER['REMOTE_ADDR']='';
	$errors=[];

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

	foreach([
		'bruteforce_pdo'=>new bruteforce_pdo([
			'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce.sqlite3'),
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
	] as $class_name=>$class) {
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
		foreach([
			'bruteforce_pdo'=>new bruteforce_pdo([
				'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce_resume.sqlite3'),
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
		] as $class_name=>$class) {
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
		foreach([
			'bruteforce_pdo'=>new bruteforce_pdo([
				'pdo_handler'=>new PDO('sqlite:'.__DIR__.'/tmp/sec_bruteforce_resume.sqlite3'),
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
		] as $class_name=>$class) {
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

	foreach([
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
	] as $class_name=>$class) {
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
			echo '   -> check/get_attempts';//var_dump($class->check());var_dump($class->get_attempts());
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

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>