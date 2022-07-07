<?php
	/*
	 * ob_sfucator.php library test
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

	echo ' -> Testing library';
		$GLOBALS['_ob_sfucator']=[
			'title'=>'Example title',
			'label'=>'Enable javascript'
		];
		if(ob_sfucator('Example content') === '<!DOCTYPE html><html><head><title>Example title</title><meta charset="utf-8"></head><body onload="document.write(unescape(\'%45%78%61%6d%70%6c%65%20%63%6f%6e%74%65%6e%74\'));"><noscript>Enable javascript</noscript></body></html>')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>