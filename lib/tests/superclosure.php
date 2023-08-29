<?php
	/*
	 * superclosure.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  var_export_contains.php library is required
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

	$GLOBALS['superclosure_executed']=false;
	$GLOBALS['superclosure_arg_content']='';
	$GLOBALS['superclosure_use_var_content']='';
	$GLOBALS['superclosure_meta_executed']=false;
	$GLOBALS['superclosure_meta_arg_content']='';
	$GLOBALS['superclosure_meta_use_var_content']='';
	$failed=false;

	$use_var='use_var_value';
	$classes=[
		'superclosure'=>new superclosure(function($arg) use($use_var){
			$GLOBALS['superclosure_executed']=true;
			$GLOBALS['superclosure_arg_content']=$arg;
			$GLOBALS['superclosure_use_var_content']=$use_var;
		}),
		'superclosure_meta'=>new superclosure_meta(function($arg) use($use_var){
			$GLOBALS['superclosure_meta_executed']=true;
			$GLOBALS['superclosure_meta_arg_content']=$arg;
			$GLOBALS['superclosure_meta_use_var_content']=$use_var;
		})
	];
	foreach($classes as $class_name=>$closure)
	{
		echo ' -> Testing '.$class_name;
			$serialized_closure=serialize($closure);
			$unserialized_closure=unserialize($serialized_closure);

			try {
				$unserialized_closure('arg_value');
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

			if(!$GLOBALS[$class_name.'_executed'])
				$failed=true;
			if($GLOBALS[$class_name.'_arg_content'] !== 'arg_value')
				$failed=true;
			if($GLOBALS[$class_name.'_use_var_content'] !== 'use_var_value')
				$failed=true;

			if($failed)
				echo ' [FAIL]'.PHP_EOL;
			else
				echo ' [ OK ]'.PHP_EOL;
	}

	if($failed)
		exit(1);

	echo ' -> Testing superclosure_meta methods'.PHP_EOL;
		$GLOBALS['superclosure_meta_executed']=false;
		$use_var='use_var_value';
		$superclosure_meta=new superclosure_meta(function() use($use_var){
			$GLOBALS['superclosure_meta_executed']=true;
		});
	echo '  -> get_closure_vars';
		if(var_export_contains(
			$superclosure_meta->get_closure_vars(),
			"array('use_var'=>'use_var_value',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo '  -> get_closure_body';
		if(md5($superclosure_meta->get_closure_body()) === '71f68fa66d6a4c26e9cfdf45c44a48e4')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo '  -> __sleep'; // $this->sleep_called === true
		$serialized_closure=serialize($superclosure_meta);
		$unserialized_closure=unserialize($serialized_closure);

		try {
			$unserialized_closure();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

		if($GLOBALS['superclosure_meta_executed'])
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>