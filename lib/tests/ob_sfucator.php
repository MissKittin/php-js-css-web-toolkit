<?php
	/*
	 * ob_sfucator.php library test
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

	echo ' -> Testing library';
		ob_sfucator(
			'Example title',
			'Enable javascript'
		);
		if(ob_sfucator::run('Example content') === '<!DOCTYPE html><html><head><title>Example title</title><meta charset="utf-8"></head><body onload="document.write(unescape(\'%45%78%61%6d%70%6c%65%20%63%6f%6e%74%65%6e%74\'));"><noscript>Enable javascript</noscript></body></html>')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>