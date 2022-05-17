<?php
	/*
	 * sec_lv_encrypter.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  openssl extension is required
	 *  mbstring extensions is required
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 */

	foreach(['openssl', 'mbstring', 'PDO', 'pdo_sqlite'] as $extension)
		if(!extension_loaded($extension))
		{
			echo $extension.' extension is not loaded'.PHP_EOL;
			exit(1);
		}

	ob_start();

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			ob_end_flush();
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$errors=[];

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/sec_lv_encrypter.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing lv_encrypter';
		$lv_encrypter=new lv_encrypter(lv_encrypter::generate_key());
		$secret_message=$lv_encrypter->encrypt('Secret message');
		if($lv_encrypter->decrypt($secret_message) === 'Secret message')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='lv_encrypter';
		}

	echo ' -> Testing lv_cookie_encrypter [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_session_encrypter [SKIP]'.PHP_EOL;
	echo ' -> Testing lv_cookie_session_handler [SKIP]'.PHP_EOL;

	echo ' -> Testing lv_pdo_session_handler';
		$lv_pdo_session_handler_key=lv_encrypter::generate_key();
		$lv_pdo_session_handler_pdo=new PDO('sqlite:'.__DIR__.'/tmp/sec_lv_encrypter.sqlite3');
		session_set_save_handler(new lv_pdo_session_handler([
			'key'=>$lv_pdo_session_handler_key,
			'pdo_handler'=>$lv_pdo_session_handler_pdo,
			'table_name'=>'lv_pdo_session_handler'
		]), true);
		session_id('123abc');
		session_start([
			'use_cookies'=>0,
			'cache_limiter'=>''
		]);
		$_SESSION['test_variable_a']='test_value_a';
		$_SESSION['test_variable_b']='test_value_b';
		session_write_close();

		$output=$lv_pdo_session_handler_pdo->query('SELECT * FROM lv_pdo_session_handler')->fetchAll();
		if(isset($output[0]['payload']))
		{
			$lv_pdo_session_handler_encrypter=new lv_encrypter($lv_pdo_session_handler_key);
			if($lv_pdo_session_handler_encrypter->decrypt($output[0]['payload'], false) === 'test_variable_a|s:12:"test_value_a";test_variable_b|s:12:"test_value_b";')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='lv_pdo_session_handler';
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='lv_pdo_session_handler';
		}

	ob_end_flush();

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>