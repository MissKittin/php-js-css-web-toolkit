<?php
	/*
	 * ob_minifier.php library test
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

	$source='<tag>
		<meta http-equiv="Content-Security-Policy" content="default-src \'self\'; style-src \'self\'">
		<script type="text/javascript" nonce="noncevalue"></script>
		<script type=\'text/javascript\'></script>
		<style type="text/css">
			box {
				property: value; /* comment */
			}
		</style>
		<style type="text/css" nonce="noncevalue2">
			box {
				property2: value2; /* comment2 */
			}
		</style>
		<!-- comment -->
	</tag>';

	echo ' -> Testing library';
		//echo ' ('.ob_minifier($source).')';
		//echo ' ['.md5(ob_minifier($source)).']';
		if(md5(ob_minifier($source)) === 'b7e4c9c20868adb03c993536a315d164')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>