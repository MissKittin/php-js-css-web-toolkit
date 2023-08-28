<?php
	/*
	 * simpleblog_db.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  var_export_contains.php library is required
	 */

	foreach([
		'rmdir_recursive.php',
		'var_export_contains.php'
	] as $library){
		echo ' -> Including '.$library;
			if(is_file(__DIR__.'/../lib/'.$library))
			{
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.$library))
			{
				if(@(include __DIR__.'/../'.$library) === false)
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
		@mkdir(__DIR__.'/tmp/simpleblog_db');
		foreach([
			'simpleblog_db',
			'simpleblog_dbcache',
			'simpleblog_dbcache_cache'
		] as $file)
			rmdir_recursive(__DIR__.'/tmp/simpleblog_db/'.$file);
		@unlink(__DIR__.'/tmp/simpleblog_db/simpleblog_db.zip');
	echo ' [ OK ]'.PHP_EOL;

	$errors=[];

	foreach(['simpleblog_db', 'simpleblog_db_cache', 'simpleblog_db_zip'] as $version)
	{
		echo ' -> Testing '.$version.PHP_EOL;

		switch($version)
		{
			case 'simpleblog_db':
				@mkdir(__DIR__.'/tmp/simpleblog_db/simpleblog_db');
				$db=new simpleblog_db([
					'db_path'=>__DIR__.'/tmp/simpleblog_db/simpleblog_db'
				]);
			break;
			case 'simpleblog_db_cache':
				@mkdir(__DIR__.'/tmp/simpleblog_db/simpleblog_dbcache');
				$db=new simpleblog_db_cache([
					'db_path'=>__DIR__.'/tmp/simpleblog_db/simpleblog_dbcache',
					'cache_path'=>__DIR__.'/tmp/simpleblog_db/simpleblog_dbcache_cache'
				]);
			break;
			case 'simpleblog_db_zip':
				$db=new simpleblog_db_zip([
					'db_path'=>__DIR__.'/tmp/simpleblog_db/simpleblog_db.zip',
					'db_compression'=>true
				]);
		}

		echo '  -> add/read';
			$db->add('sample-record', [
				'name'=>'value',
				'subrecord'=>[
					'subrecord_name'=>'subrecord_value'
				]
			]);

			if($db->read('sample-record')['name'] === 'value')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]=$version.' add/read phase 1';
			}

			if($db->read('sample-record')['subrecord']['subrecord_name'] === 'subrecord_value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' add/read phase 2';
			}

		echo '  -> add_bulk/read';
			$db->add_bulk('sample-record-bulk', [
				'name'=>'value',
				'subrecord'=>[
					'subrecord_name_BULK'=>'subrecord_value'
				]
			]);
			$db->reopen_db();

			if($db->read('sample-record-bulk')['name'] === 'value')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]=$version.' add_bulk/read phase 1';
			}

			if($db->read('sample-record-bulk')['subrecord']['subrecord_name_BULK'] === 'subrecord_value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' add_bulk/read phase 2';
			}

		echo '  -> rename/read';
			if($db->rename('sample-record', 'renamed-record'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]=$version.' rename/read phase 1';
			}

			if($db->read('renamed-record')['name'] === 'value')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]=$version.' rename/read phase 2';
			}

			if($db->read('renamed-record')['subrecord']['subrecord_name'] === 'subrecord_value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' rename/read phase 3';
			}

		echo '  -> find';
			if(var_export_contains(
				$db->find('subrecord/subrecord_name_BULK'),
				"array(0=>'sample-record-bulk/subrecord/subrecord_name_BULK',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' find';
			}

		echo '  -> add (edit)/read';
			$db->add('renamed-record', [
				'name'=>'new value'
			]);

			if($db->read('renamed-record')['name'] === 'new value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' add (edit)/read';
			}

		echo '  -> list';
			if(
				var_export_contains(
					$db->list(),
					"array(0=>'sample-record-bulk',1=>'renamed-record',)"
				) ||
				var_export_contains(
					$db->list(),
					"array(0=>'renamed-record',1=>'sample-record-bulk',)"
				)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' list';
			}

		echo '  -> delete/read';
			$db->delete('renamed-record');

			if(empty(@$db->read('renamed-record')['name']))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$version.' delete/read';
			}
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>