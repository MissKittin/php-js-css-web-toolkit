<?php
	/*
	 * pdo_cheat.php library test
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
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$errors=[];

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@unlink(__DIR__.'/tmp/pdo_cheat.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Initializing database handler';
		$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/pdo_cheat.sqlite3');
		$pdo_cheat=new pdo_cheat([
			'pdo_handler'=>$pdo_handler,
			'table_name'=>'test_table'
		]);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating table';
		$pdo_cheat->new_table()
			->id(pdo_cheat::default_id_type)
			->name('VARCHAR(30)')
			->surname('VARCHAR(30)')
			->personal_id('INTEGER')
			->save_table();
		if($pdo_handler->query('SELECT * FROM test_table') === false)
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
		if(str_replace(["\n", ' '], '', var_export($pdo_handler->query('SELECT * FROM test_table')->fetchAll(PDO::FETCH_NAMED), true)) === "array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'20',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)")
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
		if(($test_person !== false) && ($test_person->id() === '1') && ($test_person->personal_id() === '20'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='Reading rows first result/dump row phase 1';
		}
		if(str_replace(["\n", ' '], '', var_export($test_person->dump_row(), true)) === "array('id'=>'1','personal_id'=>'20',)")
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
		if(($test_person !== false) && ($test_person->id() === '2') && ($test_person->personal_id() === '30'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='Reading rows second result/dump row phase 1';
		}
		if(str_replace(["\n", ' '], '', var_export($test_person->dump_row(), true)) === "array('id'=>'2','personal_id'=>'30',)")
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
		if(str_replace(["\n", ' '], '', var_export($pdo_handler->query('SELECT * FROM test_table')->fetchAll(PDO::FETCH_NAMED), true)) === "array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'50',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Editing row';
		}

	echo ' -> Dumping table';
		if(str_replace(["\n", ' '], '', var_export($pdo_cheat->dump_table(), true)) === "array(0=>array('id'=>'1','name'=>'Test1','surname'=>'tseT','personal_id'=>'50',),1=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Dumping table';
		}

	echo ' -> Dumping schema';
		if(str_replace(["\n", ' '], '', var_export($pdo_cheat->dump_schema(), true)) === "array('id'=>'id','name'=>'name','surname'=>'surname','personal_id'=>'personal_id',)")
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
		if(str_replace(["\n", ' '], '', var_export($pdo_handler->query('SELECT * FROM test_table')->fetchAll(PDO::FETCH_NAMED), true)) === "array(0=>array('id'=>'2','name'=>'Test2','surname'=>'tseT','personal_id'=>'30',),)")
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
		if(str_replace(["\n", ' '], '', var_export($pdo_handler->query('SELECT * FROM test_table')->fetchAll(PDO::FETCH_NAMED), true)) === "array(0=>array('id'=>'3','name'=>'Test1','surname'=>'tseT','personal_id'=>'20',),)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='Clearing table';
		}

	echo ' -> Dropping table';
		$pdo_cheat->clear_table()->drop_table();
		if($pdo_handler->query('SELECT * FROM test_table') === false)
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