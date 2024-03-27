<?php
	/*
	 * registry.php library test
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

	echo ' -> Testing registry class'.PHP_EOL;
		$registry=new registry(false);
		echo '  -> Testing $registry->key';
			$registry->phasea='value';
			if($registry->phasea === 'value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> Testing $registry["key"]';
			$registry['phaseb']='value';
			if($registry['phaseb'] === 'value')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing static_registry class'.PHP_EOL;
		abstract class static_registry_a extends static_registry { protected static $registry=null; }
		abstract class static_registry_b extends static_registry { protected static $registry=null; }
		echo '  -> Testing registry->key';
			static_registry_a::registry()->phasea='valuea';
			static_registry_b::registry()->phasea='valueb';
			if(static_registry_a::registry()->phasea === 'valuea')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(static_registry_b::registry()->phasea === 'valueb')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> Testing registry["key"]';
			static_registry_a::registry()['phaseb']='valuea';
			static_registry_b::registry()['phaseb']='valueb';
			if(static_registry_a::registry()['phaseb'] === 'valuea')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(static_registry_b::registry()['phaseb'] === 'valueb')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>