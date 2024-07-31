<?php
	/*
	 * scandir_recursive.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 *  var_export_contains.php library is required
	 */

	foreach(['rmdir_recursive.php', 'var_export_contains.php'] as $library)
	{
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
		rmdir_recursive(__DIR__.'/tmp/scandir_recursive');
		mkdir(__DIR__.'/tmp/scandir_recursive');
	echo ' [ OK ]'.PHP_EOL;

	chdir(__DIR__.'/tmp/scandir_recursive');

	echo ' -> Creating test directory';
		file_put_contents('./1', '');
		mkdir('./A');
		file_put_contents('./A/1', '');
		file_put_contents('./A/2', '');
		file_put_contents('./A/3', '');
		mkdir('./B');
		file_put_contents('./B/1', '');
		file_put_contents('./B/2', '');
		file_put_contents('./B/3', '');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing library';
		//echo ' ['.var_export_contains(iterator_to_array(scandir_recursive('.', false)), '', true).']';
		if(var_export_contains(
			iterator_to_array(scandir_recursive('.', false)),
			"array(0=>'.',1=>'..',2=>'1',3=>'A/.',4=>'A/..',5=>'A/1',6=>'A/2',7=>'A/3',8=>'B/.',9=>'B/..',10=>'B/1',11=>'B/2',12=>'B/3',)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		//echo ' ['.var_export_contains(iterator_to_array(scandir_recursive('.', true)), '', true).']';
		if(var_export_contains(
			iterator_to_array(scandir_recursive('.', true)),
			"array(0=>'1',1=>'A/1',2=>'A/2',3=>'A/3',4=>'B/1',5=>'B/2',6=>'B/3',)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}

	if($failed)
		exit(1);
?>