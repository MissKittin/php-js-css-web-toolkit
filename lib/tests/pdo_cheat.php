<?php
	/*
	 * pdo_cheat.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite) (default: sqlite)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_SOCKET (has priority over the HOST)
	 *    eg. /var/run/postgresql
	 *    note: path to the directory, not socket
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_SOCKET (has priority over the HOST)
	 *    eg. /var/run/mysqld/mysqld.sock
	 *   TEST_MYSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 *  var_export_contains.php library is required
	 */

	if(!class_exists('PDO'))
	{
		echo 'PDO extension is not loaded'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including var_export_contains.php';
		if(is_file(__DIR__.'/../lib/var_export_contains.php'))
		{
			if(@(include __DIR__.'/../lib/var_export_contains.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../var_export_contains.php'))
		{
			if(@(include __DIR__.'/../var_export_contains.php') === false)
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

	$errors=[];

	echo ' -> Removing temporary files';
		@unlink(__DIR__.'/tmp/pdo_cheat/pdo_cheat.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	if(getenv('TEST_DB_TYPE') !== false)
	{
		echo ' -> Configuring PDO'.PHP_EOL;

		$_pdo=[
			'type'=>getenv('TEST_DB_TYPE'),
			'credentials'=>[
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
					'dbname'=>'php_toolkit_tests',
					'user'=>'root',
					'password'=>''
				]
			]
		];

		foreach(['pgsql', 'mysql'] as $_pdo['_database'])
			foreach(['host', 'port', 'socket', 'dbname', 'user', 'password'] as $_pdo['_parameter'])
			{
				$_pdo['_variable']='TEST_'.strtoupper($_pdo['_database'].'_'.$_pdo['_parameter']);
				$_pdo['_value']=getenv($_pdo['_variable']);

				if($_pdo['_value'] !== false)
				{
					echo '  -> Using '.$_pdo['_variable'].'="'.$_pdo['_value'].'" as '.$_pdo['_database'].' '.$_pdo['_parameter'].PHP_EOL;
					$_pdo['credentials'][$_pdo['_database']][$_pdo['_parameter']]=$_pdo['_value'];
				}
			}

		try /* some monsters */ {
			switch($_pdo['type'])
			{
				case 'pgsql':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

					if(!in_array('pgsql', PDO::getAvailableDrivers()))
						throw new Exception('pdo_pgsql extension is not loaded');

					if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
						$pdo_handle=new PDO('pgsql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
							.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
							.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
						);
					else
						$pdo_handle=new PDO('pgsql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
							.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
							.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
							.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
						);
				break;
				case 'mysql':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

					if(!in_array('mysql', PDO::getAvailableDrivers()))
						throw new Exception('pdo_mysql extension is not loaded');

					if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
						$pdo_handle=new PDO('mysql:'
							.'unix_socket='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
							$_pdo['credentials'][$_pdo['type']]['user'],
							$_pdo['credentials'][$_pdo['type']]['password']
						);
					else
						$pdo_handle=new PDO('mysql:'
							.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
							.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
							.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
							$_pdo['credentials'][$_pdo['type']]['user'],
							$_pdo['credentials'][$_pdo['type']]['password']
						);
				break;
				case 'sqlite':
					echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;
				break;
				default:
					echo '  -> '.$_pdo['type'].' driver is not supported [FAIL]'.PHP_EOL;
			}
		} catch(Throwable $error) {
			echo ' Error: '.$error->getMessage().PHP_EOL;
			exit(1);
		}

		if(isset($pdo_handle))
		{
			$pdo_handle->exec('DROP TABLE IF EXISTS pdo_cheat_test_table');
			$pdo_handle->exec('DROP TABLE IF EXISTS pdo_cheat_alter_test_table');
			$pdo_handle->exec('DROP TABLE IF EXISTS pdo_cheat_alter_test_table_r');
		}
	}
	if(!isset($pdo_handle))
	{
		if(!in_array('sqlite', PDO::getAvailableDrivers()))
		{
			echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
			exit(1);
		}

		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/pdo_cheat');
		$pdo_handle=new PDO('sqlite:'.__DIR__.'/tmp/pdo_cheat/pdo_cheat.sqlite3');
	}

	$pdo_cheat=new pdo_cheat([
		'pdo_handle'=>$pdo_handle,
		'table_name'=>'pdo_cheat_test_table'
	]);
	$pdo_cheat_alter=[
		'pdo_handle'=>$pdo_handle,
		'table_name'=>'pdo_cheat_alter_test_table'
	];

	echo ' -> Creating alter table';
		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->new_table()
		->	id(pdo_cheat::default_id_type)
		->	name('VARCHAR(30)')
		->	surname('VARCHAR(30)')
		->	personal_id('INTEGER')
		->	save_table();
		if($pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Creating table';
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Altering the table (add_column)';
		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->alter_table()
		->	add_alter_test('INTEGER');
		$pdo_handle->exec('INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES(2)');
		$output_string_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>2,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (add)';
		}
	echo ' -> Altering the table (rename_column)';
		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->alter_table()
		->	rename_from_alter_test()
		->	rename_to_alter_test_a();
		$output_string_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test_a'=>2,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test_a'=>'2',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='Altering the table (rename_column phase 1)';
		}

		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->alter_table()
		->	rename_from_alter_test_a()
		->	rename_to_alter_test();
		$output_string_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>2,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (rename_column phase 2)';
		}
	echo ' -> Altering the table (modify_column)';
		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->alter_table()
		->	modify_alter_test('VARCHAR(30)');
		switch($pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$pdo_handle->exec("INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES('asd')");
			break;
			case 'mysql':
			case 'sqlite':
				$pdo_handle->exec('INSERT INTO pdo_cheat_alter_test_table(alter_test) VALUES("asd")');
		}
		$output_string_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),1=>array('id'=>2,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'asd',),)";
		$output_string_c="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),1=>array('id'=>NULL,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'asd',),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),1=>array('id'=>'2','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'asd',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED);
		//echo ' ('.var_export_contains($query_result, '', true).')';
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_c) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (modify)';
		}
		switch($pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$pdo_handle->exec("DELETE FROM pdo_cheat_alter_test_table WHERE alter_test='asd'");
			break;
			case 'mysql':
			case 'sqlite':
				$pdo_handle->exec('DELETE FROM pdo_cheat_alter_test_table WHERE alter_test="asd"');
		}
	echo ' -> Altering the table (drop_column)';
		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->alter_table()
		->	drop_alter_test();
		$output_string_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (drop)';
		}
	echo ' -> Altering the table (rename_table)';
		$pdo_cheat_alter_handle=new pdo_cheat($pdo_cheat_alter);
		$pdo_cheat_alter_handle->alter_table()
		->	rename_table('pdo_cheat_alter_test_table_r');
		$output_string_a_a="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>2,),)";
		$output_string_a_b="array(0=>array('id'=>1,'name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
		$output_string_b_a="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,'alter_test'=>'2',),)";
		$output_string_b_b="array(0=>array('id'=>'1','name'=>NULL,'surname'=>NULL,'personal_id'=>NULL,),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_alter_test_table_r')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a_a) ||
			var_export_contains($query_result, $output_string_a_b) ||
			var_export_contains($query_result, $output_string_b_a) ||
			var_export_contains($query_result, $output_string_b_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Altering the table (rename_table)';
		}

	echo ' -> Creating table';
		$pdo_cheat->new_table()
		->	id(pdo_cheat::default_id_type)
		->	name('VARCHAR(30)')
		->	surname('VARCHAR(30)')
		->	personal_id('INTEGER')
		->	save_table();
		if($pdo_handle->query('SELECT * FROM pdo_cheat_test_table') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Creating table';
		}
		else
			echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating rows';
		$pdo_cheat->new_row()
		->	name('Test1')
		->	surname('tseT')
		->	personal_id(20)
		->	save_row();
		$pdo_cheat->new_row()
		->	name('Test2')
		->	surname('tseT')
		->	personal_id(30)
		->	save_row();
		$output_string_a="array(0=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>20,),1=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'20',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Creating rows';
		}

	echo ' -> Reading rows'.PHP_EOL;
		echo '  -> first result/dump row';
			$test_person=$pdo_cheat->get_row()
			->	select_id()
			->	select_personal_id()
			->	get_row_by_surname('tseT')
			->	get_row();
			if(($test_person !== false) && ($test_person->id() == '1') /*int*/ && ($test_person->personal_id() == '20'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='Reading rows first result/dump row phase 1';
			}
			$output_string_a="array('id'=>1,'personal_id'=>20,)";
			$output_string_b="array('id'=>'1','personal_id'=>'20',)";
			$query_result=$test_person->dump_row();
			if(
				var_export_contains($query_result, $output_string_a) ||
				var_export_contains($query_result, $output_string_b)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='Reading rows first result/dump row phase 2';
			}
		echo '  -> second result/dump row';
			$test_person=$pdo_cheat->get_row();
			$test_person
			->	select_id()
			->	select_personal_id()
			->	get_row_by_surname('tseT')
			->	get_row();
			$test_person=$test_person->get_next_row();
			if(($test_person !== false) && ($test_person->id() == '2') /* int */ && ($test_person->personal_id() == '30'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='Reading rows second result/dump row phase 1';
			}
			$output_string_a="array('id'=>2,'personal_id'=>30,)";
			$output_string_b="array('id'=>'2','personal_id'=>'30',)";
			$query_result=$test_person->dump_row();
			if(
				var_export_contains($query_result, $output_string_a) ||
				var_export_contains($query_result, $output_string_b)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='Reading rows second result/dump row phase 2';
			}

	echo ' -> Editing row';
		$test_person=$pdo_cheat->get_row()
		->	get_row_by_name('Test1')
		->	get_row();
		$test_person->personal_id(50)->save_row();
		$output_string_a="array(0=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),1=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>50,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'50',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		$output_string_c="array(0=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>50,),1=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED);
		//echo ' ('.var_export_contains($query_result, '', true).')';
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b) ||
			var_export_contains($query_result, $output_string_c)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Editing row';
		}

	echo ' -> Dumping table';
		$output_string_a="array(0=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),1=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>50,),)";
		$output_string_b="array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'50',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		$output_string_c="array(0=>array('id'=>1,'name'=>'Test1','surname'=>'tseT','personal_id'=>50,),1=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),)";
		$query_result=$pdo_cheat->dump_table();
		//echo ' ('.var_export_contains($query_result, '', true).')';
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b) ||
			var_export_contains($query_result, $output_string_c)
		)
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
		->	name('Test1')
		->	delete_row();
		$output_string_a="array(0=>array('id'=>2,'name'=>'Test2','surname'=>'tseT','personal_id'=>30,),)";
		$output_string_b="array(0=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Deleting row';
		}

	echo ' -> Clearing table';
		$pdo_cheat->clear_table()->flush_table();
		$pdo_cheat->new_row()
		->	name('Test1')
		->	surname('tseT')
		->	personal_id(20)
		->	save_row();
		$output_string_a="array(0=>array('id'=>3,'name'=>'Test1','surname'=>'tseT','personal_id'=>20,),)";
		$output_string_b="array(0=>array('id'=>'3','name'=>'Test1','surname'=>'tseT','personal_id'=>'20',),)";
		$query_result=$pdo_handle->query('SELECT * FROM pdo_cheat_test_table')->fetchAll(PDO::FETCH_NAMED);
		if(
			var_export_contains($query_result, $output_string_a) ||
			var_export_contains($query_result, $output_string_b)
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Clearing table';
		}

	echo ' -> Dropping table';
		$pdo_cheat->clear_table()->drop_table();
		try {
			if($pdo_handle->query('SELECT * FROM pdo_cheat_test_table') === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='Dropping table';
			}
		} catch(PDOException $error) {
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