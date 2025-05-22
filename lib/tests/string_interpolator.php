<?php
	/*
	 * string_interpolator.php library test
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

	echo ' -> Testing library';
		$context=[
			'username'=>'bolivar',
			'status'=>new class()
			{
				public function __toString()
				{
					return 'NO_ERROR';
				}
			}
		];
		if(string_interpolator(
			'User {username} created ({status} {status})',
			$context
		) === 'User bolivar created (NO_ERROR NO_ERROR)')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(string_interpolator(
			'User [username] created ([status] [status])',
			$context,
			'[', ']'
		) === 'User bolivar created (NO_ERROR NO_ERROR)')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(string_interpolator(
			'User {{ username }} created ({{ status }} {{ status }})',
			$context,
			'{{ ', ' }}'
		) === 'User bolivar created (NO_ERROR NO_ERROR)')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);

?>