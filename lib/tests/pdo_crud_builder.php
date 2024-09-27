<?php
	/*
	 * pdo_crud_builder.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  var_export_contains.php library is required
	 *  PDO extension is recommended
	 *  pdo_sqlite extension is recommended
	 */

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
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/pdo_crud_builder');
		@unlink(__DIR__.'/tmp/pdo_crud_builder/pdo_crud_builder.sqlite3');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Initializing database handler';
		if(class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers()))
			$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/pdo_crud_builder/pdo_crud_builder.sqlite3');
		else
			$pdo_handler=new class{};

		$pdo_builder=new pdo_crud_builder([
			'pdo_handler'=>$pdo_handler
		]);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Test phase 1 (method/print_exec/print_parameters/flush_all)'.PHP_EOL;
		echo '  -> create_table';
			$pdo_builder->create_table('exampletable', [
				'id'=>pdo_crud_builder::ID_DEFAULT_PARAMS,
				'a'=>'TEXT',
				'b'=>'TEXT',
				'c'=>'TEXT'
			]);
			if(
				($pdo_builder->print_exec() === 'CREATE TABLE exampletable(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, a TEXT, b TEXT, c TEXT) ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='create_table';
			}
			$pdo_builder->flush_all();
		echo '  -> alter_table';
			$pdo_builder->alter_table('exampletable');
			if(
				($pdo_builder->print_exec() === 'ALTER TABLE exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='alter_table';
			}
			$pdo_builder->flush_all();
		echo '  -> alter_table_if_exists';
			$pdo_builder->alter_table_if_exists('exampletable');
			if(
				($pdo_builder->print_exec() === 'ALTER TABLE IF EXISTS exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='alter_table_if_exists';
			}
			$pdo_builder->flush_all();
		echo '  -> add_column';
			$pdo_builder->add_column('examplecolumn', 'datatype');
			if(
				($pdo_builder->print_exec() === 'ADD examplecolumn datatype ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='add_column';
			}
			$pdo_builder->flush_all();
		echo '  -> drop_column';
			$pdo_builder->drop_column('examplecolumn');
			if(
				($pdo_builder->print_exec() === 'DROP COLUMN examplecolumn ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='drop_column';
			}
			$pdo_builder->flush_all();
		echo '  -> rename_column';
			$pdo_builder->rename_column('examplecolumn', 'newname');
			if(
				($pdo_builder->print_exec() === 'RENAME COLUMN examplecolumn TO newname ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='rename_column';
			}
			$pdo_builder->flush_all();
		echo '  -> alter_column';
			$pdo_builder->alter_column('examplecolumn', 'datatype');
			if(
				($pdo_builder->print_exec() === 'ALTER COLUMN examplecolumn datatype ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='alter_column';
			}
			$pdo_builder->flush_all();
		echo '  -> alter_column_type';
			$pdo_builder->alter_column_type('examplecolumn', 'datatype');
			if(
				($pdo_builder->print_exec() === 'ALTER COLUMN examplecolumn TYPE datatype ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='alter_column_type';
			}
			$pdo_builder->flush_all();
		echo '  -> modify_column';
			$pdo_builder->modify_column('examplecolumn', 'datatype');
			if(
				($pdo_builder->print_exec() === 'MODIFY COLUMN examplecolumn datatype ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='modify_column';
			}
			$pdo_builder->flush_all();
		echo '  -> modify';
			$pdo_builder->modify('examplecolumn', 'datatype');
			if(
				($pdo_builder->print_exec() === 'MODIFY examplecolumn datatype ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='modify';
			}
			$pdo_builder->flush_all();
		echo '  -> rename';
			$pdo_builder->rename('exampletable');
			if(
				($pdo_builder->print_exec() === 'RENAME exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='rename';
			}
			$pdo_builder->flush_all();
		echo '  -> rename_to';
			$pdo_builder->rename_to('exampletable');
			if(
				($pdo_builder->print_exec() === 'RENAME TO exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='rename_to';
			}
			$pdo_builder->flush_all();
		echo '  -> drop_table';
			$pdo_builder->drop_table('exampletable');
			if(
				($pdo_builder->print_exec() === 'DROP TABLE IF EXISTS exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='drop_table';
			}
			$pdo_builder->flush_all();
		echo '  -> truncate_table';
			$pdo_builder->truncate_table('exampletable');
			if(
				($pdo_builder->print_exec() === 'TRUNCATE TABLE exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='truncate_table';
			}
			$pdo_builder->flush_all();
		echo '  -> insert_into';
			$pdo_builder->insert_into('exampletable', 'a,b', [
				['aa', 'ba'],
				['ba', 'bb']
			]);
			if(
				($pdo_builder->print_exec() === 'INSERT INTO exampletable(a,b) VALUES(?, ?), (?, ?) ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'aa',1=>'ba',2=>'ba',3=>'bb',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='insert_into';
			}
			$pdo_builder->flush_all();
		echo '  -> select';
			$pdo_builder->select('asterisk');
			if(
				($pdo_builder->print_exec() === 'SELECT asterisk ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='select';
			}
			$pdo_builder->flush_all();
		echo '  -> select_top';
			$pdo_builder->select_top(20, 'asterisk');
			if(
				($pdo_builder->print_exec() === 'SELECT TOP 20 asterisk ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='select_top';
			}
			$pdo_builder->flush_all();
		echo '  -> select_top_percent';
			$pdo_builder->select_top_percent(20, 'asterisk');
			if(
				($pdo_builder->print_exec() === 'SELECT TOP 20 PERCENT asterisk ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='select_top_percent';
			}
			$pdo_builder->flush_all();
		echo '  -> as';
			$pdo_builder->as('aaa');
			if(
				($pdo_builder->print_exec() === 'AS aaa ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='as';
			}
			$pdo_builder->flush_all();
		echo '  -> group_by';
			$pdo_builder->group_by('parameter');
			if(
				($pdo_builder->print_exec() === 'GROUP BY parameter ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='group_by';
			}
			$pdo_builder->flush_all();
		echo '  -> order_by';
			$pdo_builder->order_by('parameter');
			if(
				($pdo_builder->print_exec() === 'ORDER BY parameter ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='order_by';
			}
			$pdo_builder->flush_all();
		echo '  -> join';
			$pdo_builder->join('full', 'parameter', 'onn');
			if(
				($pdo_builder->print_exec() === 'FULL OUTER JOIN parameter ON onn ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='join';
			}
			$pdo_builder->flush_all();
		echo '  -> union';
			$pdo_builder->union();
			if(
				($pdo_builder->print_exec() === 'UNION ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='union';
			}
			$pdo_builder->flush_all();
		echo '  -> union_all';
			$pdo_builder->union_all();
			if(
				($pdo_builder->print_exec() === 'UNION ALL ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='union_all';
			}
			$pdo_builder->flush_all();
		echo '  -> asc';
			$pdo_builder->asc();
			if(
				($pdo_builder->print_exec() === 'ASC ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='asc';
			}
			$pdo_builder->flush_all();
		echo '  -> desc';
			$pdo_builder->desc();
			if(
				($pdo_builder->print_exec() === 'DESC ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='desc';
			}
			$pdo_builder->flush_all();
		echo '  -> limit';
			$pdo_builder->limit(3, 2);
			if(
				($pdo_builder->print_exec() === 'LIMIT 3 OFFSET 2 ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='limit';
			}
			$pdo_builder->flush_all();
		echo '  -> fetch_first';
			$pdo_builder->fetch_first(3, 'ROWS ONLY', 2, 'ROWS');
			if(
				($pdo_builder->print_exec() === 'OFFSET 2 ROWS FETCH FIRST 3 ROWS ONLY ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='fetch_first';
			}
			$pdo_builder->flush_all();
		echo '  -> fetch_first_percent';
			$pdo_builder->fetch_first_percent(3, 'ROWS ONLY', 2, 'ROWS');
			if(
				($pdo_builder->print_exec() === 'OFFSET 2 ROWS FETCH FIRST 3 PERCENT ROWS ONLY ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='fetch_first_percent';
			}
			$pdo_builder->flush_all();
		echo '  -> fetch_next';
			$pdo_builder->fetch_next(3, 'ROWS ONLY', 2, 'ROWS');
			if(
				($pdo_builder->print_exec() === 'OFFSET 2 ROWS FETCH NEXT 3 ROWS ONLY ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='fetch_next';
			}
			$pdo_builder->flush_all();
		echo '  -> fetch_next_percent';
			$pdo_builder->fetch_next_percent(3, 'ROWS ONLY', 2, 'ROWS');
			if(
				($pdo_builder->print_exec() === 'OFFSET 2 ROWS FETCH NEXT 3 PERCENT ROWS ONLY ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='fetch_next_percent';
			}
			$pdo_builder->flush_all();
		echo '  -> replace_into';
			$pdo_builder->replace_into('exampletable', 'a,b', [
				['aa', 'ab'],
				['ba', 'bb']
			]);
			if(
				($pdo_builder->print_exec() === 'REPLACE INTO exampletable(a,b) VALUES(?, ?), (?, ?) ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'aa',1=>'ab',2=>'ba',3=>'bb',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='replace_into';
			}
			$pdo_builder->flush_all();
		echo '  -> update';
			$pdo_builder->update('exampletable');
			if(
				($pdo_builder->print_exec() === 'UPDATE exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='update';
			}
			$pdo_builder->flush_all();
		echo '  -> set';
			$pdo_builder->set([
				['aa', 'ab'],
				['ba', 'bb']
			]);
			if(
				($pdo_builder->print_exec() === 'SET aa = ?, ba = ? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'ab',1=>'bb',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='set';
			}
			$pdo_builder->flush_all();
		echo '  -> delete';
			$pdo_builder->delete('exampletable');
			if(
				($pdo_builder->print_exec() === 'DELETE FROM exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='delete';
			}
			$pdo_builder->flush_all();
		echo '  -> from';
			$pdo_builder->from('exampletable');
			if(
				($pdo_builder->print_exec() === 'FROM exampletable ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='from';
			}
			$pdo_builder->flush_all();
		echo '  -> where';
			$pdo_builder->where('a', '=', 'b');
			if(
				($pdo_builder->print_exec() === 'WHERE a=? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='where';
			}
			$pdo_builder->flush_all();
		echo '  -> and';
			$pdo_builder->and('a', '=', 'b');
			if(
				($pdo_builder->print_exec() === 'AND a=? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='and';
			}
			$pdo_builder->flush_all();
		echo '  -> or';
			$pdo_builder->or('a', '=', 'b');
			if(
				($pdo_builder->print_exec() === 'OR a=? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='or';
			}
			$pdo_builder->flush_all();
		echo '  -> where_like';
			$pdo_builder->where_like('a', 'b');
			if(
				($pdo_builder->print_exec() === 'WHERE a LIKE ? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='where_like';
			}
			$pdo_builder->flush_all();
		echo '  -> where_not_like';
			$pdo_builder->where_not_like('a', 'b');
			if(
				($pdo_builder->print_exec() === 'WHERE a NOT LIKE ? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='where_not_like';
			}
			$pdo_builder->flush_all();
		echo '  -> where_is';
			$pdo_builder->where_is('a', 'b');
			if(
				($pdo_builder->print_exec() === 'WHERE a IS b ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='where_is';
			}
			$pdo_builder->flush_all();
		echo '  -> where_not';
			$pdo_builder->where_not('a', '=', 'b');
			if(
				($pdo_builder->print_exec() === 'WHERE NOT a=? ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='where_not';
			}
			$pdo_builder->flush_all();
		echo '  -> output_into';
			$pdo_builder->output_into('a', 'b');
			if(
				($pdo_builder->print_exec() === 'OUTPUT a INTO b ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='output_into';
			}
			$pdo_builder->flush_all();
		echo '  -> raw_sql';
			$pdo_builder->raw_sql('CUSTOM SQL');
			if(
				($pdo_builder->print_exec() === 'CUSTOM SQL ') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array()"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='raw_sql';
			}
			$pdo_builder->flush_all();
		echo '  -> raw_parameter';
			$pdo_builder->raw_parameter('b');
			if(
				($pdo_builder->print_exec() === '') &&
				var_export_contains(
					$pdo_builder->print_parameters(),
					"array(0=>'b',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='raw_parameter';
			}
			$pdo_builder->flush_all();
		echo '  -> error_info [SKIP]'.PHP_EOL;
		echo '  -> table_dump [SKIP]'.PHP_EOL;
		echo '  -> list_tables [SKIP]'.PHP_EOL;

	if(class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers()))
	{
		echo ' -> Test phase 2'.PHP_EOL;

		echo '  -> create_table/exec';
			$pdo_builder->create_table('exampletable', [
				'id'=>pdo_crud_builder::ID_DEFAULT_PARAMS,
				'a'=>'TEXT',
				'b'=>'TEXT'
			])->exec();
			if($pdo_handler->query('SELECT * FROM exampletable') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='create_table/exec';
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> insert_into/exec';
			$pdo_builder->insert_into('exampletable', 'a,b', [
				['aa', 'ba'],
				['ba', 'bb']
			])->exec();
			$output_string_a="array(0=>array('id'=>'1','a'=>'aa','b'=>'ba',),1=>array('id'=>'2','a'=>'ba','b'=>'bb',),)";
			$output_string_b="array(0=>array('id'=>1,'a'=>'aa','b'=>'ba',),1=>array('id'=>2,'a'=>'ba','b'=>'bb',),)";
			$query_result=$pdo_handler->query('SELECT * FROM exampletable')->fetchAll(PDO::FETCH_NAMED);
			if(
				var_export_contains($query_result, $output_string_a) ||
				var_export_contains($query_result, $output_string_b)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='insert_into/exec';
			}
		echo '  -> select/from/query';
			$result=$pdo_builder
				->select('*')
				->from('exampletable')
			->query();
			$output_string_a="array(0=>array('id'=>'1','a'=>'aa','b'=>'ba',),1=>array('id'=>'2','a'=>'ba','b'=>'bb',),)";
			$output_string_b="array(0=>array('id'=>1,'a'=>'aa','b'=>'ba',),1=>array('id'=>2,'a'=>'ba','b'=>'bb',),)";
			$query_result=$pdo_handler->query('SELECT * FROM exampletable')->fetchAll(PDO::FETCH_NAMED);
			if(
				var_export_contains($result, $output_string_a) ||
				var_export_contains($result, $output_string_b)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='select/from/query';
			}
		echo '  -> select/from/exec/fetch_row';
			$result=$pdo_builder
				->select('*')
				->from('exampletable')
			->exec(true);
			$output_string_a="array('id'=>'1','a'=>'aa','b'=>'ba',)";
			$output_string_b="array('id'=>1,'a'=>'aa','b'=>'ba',)";
			$query_result=$pdo_builder->fetch_row($result);
			if(
				var_export_contains($query_result, $output_string_a) ||
				var_export_contains($query_result, $output_string_b)
			)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='select/from/exec/fetch_row 1';
			}
			$output_string_a="array('id'=>'2','a'=>'ba','b'=>'bb',)";
			$output_string_b="array('id'=>2,'a'=>'ba','b'=>'bb',)";
			$query_result=$pdo_builder->fetch_row($result);
			if(
				var_export_contains($query_result, $output_string_a) ||
				var_export_contains($query_result, $output_string_b)
			)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='select/from/exec/fetch_row 2';
			}
			if(var_export_contains(
				$pdo_builder->fetch_row($result),
				"false"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]='select/from/exec/fetch_row 3';
			}
	}
	else
		echo ' -> Test phase 2 [SKIP]'.PHP_EOL;

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>