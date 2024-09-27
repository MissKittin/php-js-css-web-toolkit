<?php
	/*
	 * var_export_contains.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

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

	foreach([
		'int'=>[1, '1'],
		'float'=>[1.55, '1.55'],
		'string'=>['string', "'string'"],
		'array'=>[
			[
				'ia'=>'va',
				'ib'=>'vb'
			],
			"array('ia'=>'va','ib'=>'vb',)"
		]
	] as $name=>$args){
		//var_dump(var_export_contains($args[0], '', true));

		echo ' -> Testing string '.$name;
			if(var_export_contains($args[0], '', true) === $args[1])
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$name.' string';
			}

		echo ' -> Testing bool '.$name;
			if(var_export_contains($args[0], $args[1]))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$name.' bool';
			}
	}

	echo ' -> Testing postprocess string array';
		if(var_export_contains(
			[
				'ia'=>'/va',
				'/ib'=>'vb'
			],
			'',
			true,
			function($input)
			{
				return strtr($input, '/', 'A');
			}
		) === "array('ia'=>'Ava','Aib'=>'vb',)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='array postprocess string';
		}
	echo ' -> Testing postprocess bool array';
		if(var_export_contains(
			[
				'ia'=>'/va',
				'/ib'=>'vb'
			],
			"array('ia'=>'Ava','Aib'=>'vb',)",
			false,
			function($input)
			{
				return strtr($input, '/', 'A');
			}
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='array postprocess bool';
		}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>