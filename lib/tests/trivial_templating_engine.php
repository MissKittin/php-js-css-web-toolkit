<?php
	/*
	 * trivial_templating_engine.php library test
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

	$failed=false;

	echo ' -> Testing basic_templating_engine';
		$source='variable_a is {{ variable_a }} and variable_b is {{ variable_b }}';
		$variables=[
			'variable_a'=>'value_a',
			'variable_b'=>'value_b'
		];
		if(basic_templating_engine($source, $variables) === 'variable_a is value_a and variable_b is value_b')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing trivial_templating_engine';
		$source='
			{{variable_a}}
			{{ variable_b }}
			{[foreach fvariable as myvariable]}
				{[[myvariable]]} sample text
			{[end]}
			{[for i=0 i<=10 +]}
				for i={[[i]]}
			{[end]}
			{[{$i=0}]}
			{[while $i<=10]}
				while i={[[i]]}
				{[{++$i}]}
			{[end]}
		';
		$variables=[
			'variable_a'=>'value_a',
			'variable_b'=>'value_b',
			'fvariable'=>['f_a', 'f_b', 'f_c']
		];
		ob_start();
		trivial_templating_engine($source, $variables);
		$result=ob_get_clean();
		if(str_replace(["\n", "\t", ' '], '', $result) === 'value_avalue_bf_asampletextf_bsampletextf_csampletextfori=0fori=1fori=2fori=3fori=4fori=5fori=6fori=7fori=8fori=9fori=10whilei=0whilei=1whilei=2whilei=3whilei=4whilei=5whilei=6whilei=7whilei=8whilei=9whilei=10')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>