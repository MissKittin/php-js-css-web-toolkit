<?php
	/*
	 * superclosure.php library test
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

	$GLOBALS['superclosure_executed']=false;
	$GLOBALS['arg_content']='';
	$GLOBALS['use_var_content']='';
	$failed=false;

	echo ' -> Testing library';
		$use_var='use_var_value';
		$closure=new superclosure(function($arg) use($use_var){
			$GLOBALS['superclosure_executed']=true;
			$GLOBALS['arg_content']=$arg;
			$GLOBALS['use_var_content']=$use_var;
		});
		$serialized_closure=serialize($closure);
		$unserialized_closure=unserialize($serialized_closure);

		try {
			$unserialized_closure('arg_value');
		} catch(Error $e) {
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}

		if(!$GLOBALS['superclosure_executed'])
			$failed=true;
		if($GLOBALS['arg_content'] !== 'arg_value')
			$failed=true;
		if($GLOBALS['use_var_content'] !== 'use_var_value')
			$failed=true;

		if($failed)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
		else
			echo ' [ OK ]'.PHP_EOL;
?>