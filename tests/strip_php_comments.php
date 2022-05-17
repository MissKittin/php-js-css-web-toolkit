<?php
	/*
	 * strip_php_comments.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  tokenizer extension is required
	 */

	if(!extension_loaded('tokenizer'))
	{
		echo 'tokenizer extension is not loaded'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$source='<?php
		/*
		 * comment block
		 */

		echo "ok"; // line comment
	?>';

	echo ' -> Testing library';
		if(str_replace(["\n", "\t"], [' ', ''], strip_php_comments($source)) === '<?php   echo "ok"; ?>')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>