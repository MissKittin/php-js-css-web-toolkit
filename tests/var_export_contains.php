<?php
	/*
	 * var_export_contains.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
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

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>