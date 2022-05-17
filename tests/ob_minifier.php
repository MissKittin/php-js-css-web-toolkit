<?php
	/*
	 * ob_minifier.php library test
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

	$source='<tag>
		<meta http-equiv="Content-Security-Policy" content="default-src \'self\'; style-src \'self\'">
		<script type="text/javascript"></script>
		<script type=\'text/javascript\'></script>
		<style type="text/css">
			box {
				property: value; /* comment */
			}
		</style>
		<!-- comment -->
	</tag>';

	echo ' -> Testing library';
		if(ob_minifier($source) === '<tag><meta http-equiv="Content-Security-Policy" content="default-src \'self\'; style-src \'self\'"><script ></script><script ></script><style>box {property: value; }</style></tag>')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>