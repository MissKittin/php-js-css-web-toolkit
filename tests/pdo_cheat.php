<?php
	/*
	 * pdo_cheat.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite, overrides first argument)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_DBNAME (default: php-toolkit-tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 *  var_export_contains.php library is required
	 */

	foreach(['PDO', 'pdo_sqlite'] as $extension)
		if(!extension_loaded($extension))
		{
			echo $extension.' extension is not loaded'.PHP_EOL;
			exit(1);
		}

	echo ' -> Including var_export_contains.php';
		if(@(include __DIR__.'/../lib/var_export_contains.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$errors=[];

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/pdo_cheat.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	if(getenv('TEST_DB_TYPE') !== false)
		$argv[1]=getenv('TEST_DB_TYPE');
	if(isset($argv[1]))
	{
		$_db_type=$argv[1];
		$_db_credentials=[
			'pgsql'=>[
				'host'=>'127.0.0.1',
				'port'=>'5432',
				'dbname'=>'php_toolkit_tests',
				'user'=>'postgres',
				'password'=>'postgres'
			],
			'mysql'=>[
				'host'=>'[::1]',
				'port'=>'3306',
				'dbname'=>'php-toolkit-tests',
				'user'=>'root',
				'password'=>''
			]
		];
		foreach(['pgsql', 'mysql'] as $database)
			foreach(['host', 'port', 'dbname', 'user', 'password'] as $parameter)
			{
				$variable='TEST_'.strtoupper($database.'_'.$parameter);
				$value=getenv($variable);

				if($value !== false)
				{
					echo ' -> Using '.$variable.'="'.$value.'" as '.$database.' '.$parameter.PHP_EOL;
					$_db_credentials[$database][$parameter]=$value;
				}
			}

		try {
			switch($_db_type)
			{
				case 'pgsql':
					if(!extension_loaded('pdo_pgsql'))
						throw new Exception('pdo_pgsql extension is not loaded');

					$pdo_handler=new PDO('pgsql:'
						.'host='.$_db_credentials[$_db_type]['host'].';'
						.'port='.$_db_credentials[$_db_type]['port'].';'
						.'dbname='.$_db_credentials[$_db_type]['dbname'].';'
						.'user='.$_db_credentials[$_db_type]['user'].';'
						.'password='.$_db_credentials[$_db_type]['password'].''
					);
				break;
				case 'mysql':
					if(!extension_loaded('pdo_mysql'))
						throw new Exception('pdo_mysql extension is not loaded');

					$pdo_handler=new PDO('mysql:'
						.'host='.$_db_credentials[$_db_type]['host'].';'
						.'port='.$_db_credentials[$_db_type]['port'].';'
						.'dbname='.$_db_credentials[$_db_type]['dbname'],
						$_db_credentials[$_db_type]['user'],
						$_db_credentials[$_db_type]['password']
					);
			}
		} catch(Throwable $error) {
			echo ' Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(isset($pdo_handler))
		{
			$pdo_handler->exec('DROP TABLE pdo_cheat_test_table');
			$pdo_handler->exec('DROP TABLE pdo_cheat_alter_test_table');
			$pdo_handler->exec('DROP TABLE pdo_cheat_alter_test_table_r');
		}
	}
	if(!isset($pdo_handler))
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/pdo_cheat.sqlite3');

	$pdo_cheat=new pdo_cheat([
		'pdo_handler'=>$pdo_handler,
		'table_name'=>'pdo_cheat_test_table'
	]);
	$pdo_cheat_alter=new pdo_cheat([
		'pdo_handler'=>$pdo_handler,
		'table_name'=>'pdo_cheat_alter_test_table'
	]);

	echo ' -> Creating alter table';
		$pdo_cheat_alter->new_table()
			->id(pdo_cheat::default_id_type)
			->name('VARCHAR(30)')
			->surname('VARCHAR(30)')
			->personal_id('INTEGER')
			->save_table();
		if($pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Creating table';
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Altering the table (add)';
		$pdo_cheat_alter->alter_table()
			->add_column('alter_test', 'INTEGER');
		$pdo_handler->exec('INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES(2)');
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>2,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),)";
		}
		if(var_export_contains(
			$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (add)';
		}
	echo ' -> Altering the table (rename_column)';
		if($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite')
			echo ' [SKIP]'.PHP_EOL;
		else
		{
			$pdo_cheat_alter->alter_table()
				->rename_column('alter_test', 'alter_test_a');
			switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$output_string="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test_a'=>2,),)";
				break;
				case 'mysql':
					$output_string="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test_a'=>'2',),)";
			}
			if(var_export_contains(
				$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED),
				$output_string
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='Altering the table (rename_column phase 1)';
			}

			$pdo_cheat_alter->alter_table()
				->rename_column('alter_test_a', 'alter_test');
			switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$output_string="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>2,),)";
				break;
				case 'mysql':
					$output_string="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),)";
			}
			if(var_export_contains(
				$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED),
				$output_string
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='Altering the table (rename_column phase 2)';
			}
		}
	echo ' -> Altering the table (modify)';
		if($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite')
			echo ' [SKIP]'.PHP_EOL;
		else
		{
			$pdo_cheat_alter->alter_table()
				->modify_column('alter_test', 'VARCHAR(30)');
			switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$pdo_handler->exec("INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES('asd')");
					$output_string="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),1=>array('id'=>2,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'asd',),)";
				break;
				case 'mysql':
					$pdo_handler->exec('INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES("asd")');
					$output_string="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),1=>array('id'=>'2','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'asd',),)";
			}
			if(var_export_contains(
				$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED),
				$output_string
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='Altering the table (modify)';
			}
			switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$pdo_handler->exec("DELETE FROM pdo_cheat_alter_test_table WHERE alter_test='asd'");
				break;
				case 'mysql':
					$pdo_handler->exec('DELETE FROM pdo_cheat_alter_test_table WHERE alter_test="asd"');
			}
		}
	echo ' -> Altering the table (drop)';
		if($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite')
			echo ' [SKIP]'.PHP_EOL;
		else
		{
			$pdo_cheat_alter->alter_table()
				->drop_column('alter_test');
			switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$output_string="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
				break;
				case 'mysql':
					$output_string="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
			}
			if(var_export_contains(
				$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED),
				$output_string
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='Altering the table (drop)';
			}
		}
	echo ' -> Altering the table (rename_table)';
		if($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite')
			$pdo_handler->exec('INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES(2)');
		$pdo_cheat_alter->alter_table()
			->rename_table('pdo_cheat_alter_test_table_r');
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>2,),)";
				$output_string_b="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string_a="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),)";
				$output_string_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
		}
		if(
			var_export_contains(
				$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table_r')->fetchAll(PDO::FETCH_NAMED),
				$output_string_a
			) ||
			var_export_contains(
				$pdo_handler->query('SELECT * FROM pdo_cheat_alter_test_table_r')->fetchAll(PDO::FETCH_NAMED),
				$output_string_b
			)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (rename_table)';
		}

	echo ' -> Creating table';
		$pdo_cheat->new_table()
			->id(pdo_cheat::default_id_type)
			->name('VARCHAR(30)')
			->surname('VARCHAR(30)')
			->personal_id('INTEGER')
			->save_table();
		if($pdo_handler->query('SELECT * FROM pdo_cheat_test_table') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Creating table';
		}
		else
			echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating rows';
		$pdo_cheat->new_row()
			->name('Test1')
			->surname('tseT')
			->personal_id(20)
			->save_row();
		$pdo_cheat->new_row()
			->name('Test2')
			->surname('tseT')
			->personal_id(30)
			->save_row();
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array(0=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>20,),1=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'20',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		}
		if(var_export_contains(
			$pdo_handler->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Creating rows';
		}

	echo ' -> Reading rows'.PHP_EOL;
	echo '  -> first result/dump row';
		$test_person=$pdo_cheat->get_row()
			->select_id()
			->select_personal_id()
			->get_row_by_surname('tseT')
			->get_row();
		if(($test_person !== false) && ($test_person->id() == '1') /*int*/ && ($test_person->personal_id() == '20'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='Reading rows first result/dump row phase 1';
		}
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array('id'=>1,'personal_id'=>20,)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array('id'=>'1','personal_id'=>'20',)";
		}
		if(var_export_contains(
			$test_person->dump_row(),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Reading rows first result/dump row phase 2';
		}
	echo '  -> second result/dump row';
		$test_person=$pdo_cheat->get_row();
		$test_person
			->select_id()
			->select_personal_id()
			->get_row_by_surname('tseT')
			->get_row();
		$test_person=$test_person->get_next_row();
		if(($test_person !== false) && ($test_person->id() == '2') /* int */ && ($test_person->personal_id() == '30'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='Reading rows second result/dump row phase 1';
		}
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array('id'=>2,'personal_id'=>30,)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array('id'=>'2','personal_id'=>'30',)";
		}
		if(var_export_contains(
			$test_person->dump_row(),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Reading rows second result/dump row phase 2';
		}

	echo ' -> Editing row';
		$test_person=$pdo_cheat->get_row()
			->get_row_by_name('Test1')
			->get_row();
		$test_person->personal_id(50)->save_row();
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array(0=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),1=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>50,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'50',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		}
		if(var_export_contains(
			$pdo_handler->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Editing row';
		}

	echo ' -> Dumping table';
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array(0=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),1=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>50,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'50',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		}
		if(var_export_contains(
			$pdo_cheat->dump_table(),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Dumping table';
		}

	echo ' -> Dumping schema';
		if(var_export_contains(
			$pdo_cheat->dump_schema(),
			"array('id'=>'id','name'=>'name','surname'=>'surname','personal_id'=>'personal_id',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Dumping schema';
		}

	echo ' -> Deleting row';
		$pdo_cheat->delete_row()
			->name('Test1')
			->delete_row();
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array(0=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array(0=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		}
		if(var_export_contains(
			$pdo_handler->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Deleting row';
		}

	echo ' -> Clearing table';
		$pdo_cheat->clear_table()->flush_table();
		$pdo_cheat->new_row()
			->name('Test1')
			->surname('tseT')
			->personal_id(20)
			->save_row();
		switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$output_string="array(0=>array('id'=>3,'name'=>'Test1','surname'=>'tseT','personal_id'=>20,),)";
			break;
			case 'mysql':
			case 'sqlite':
				$output_string="array(0=>array('id'=>'3','name'=>'Test1','surname'=>'tseT','personal_id'=>'20',),)";
		}
		if(var_export_contains(
			$pdo_handler->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED),
			$output_string
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Clearing table';
		}

	echo ' -> Dropping table';
		$pdo_cheat->clear_table()->drop_table();
		if($pdo_handler->query('SELECT * FROM pdo_cheat_test_table') === false)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Dropping table';
		}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>