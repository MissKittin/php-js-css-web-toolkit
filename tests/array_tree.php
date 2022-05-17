<?php
	/*
	 * array_tree.php library test
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

	$failed=false;

	echo ' -> Testing array_flat2tree';
		$nested_array=array_flat2tree
		(
			[
				[0, null, 'content0|description0'],
				[1, null, 'content1|description1'],
				[2, 3, 'content2|description2'],
				[3, null, 'content3|description3'],
				[4, 2, 'content4|description4'],
				[5, null, 'content5|description5'],
				[6, 4, 'content6|description6'],
				[7, null, 'content7|description7'],
				[8, 2, 'content8|description8'],
				[9, 5, 'content9|description9']
			],
			[
				'element_id'=>0,
				'parent_element_id'=>1,
				'content'=>2
			],
			null
		);

		if(str_replace(["\n", ' '], '', var_export($nested_array, true)) === "array('content0|description0'=>array(),'content1|description1'=>array(),'content3|description3'=>array('content2|description2'=>array('content4|description4'=>array('content6|description6'=>array(),),'content8|description8'=>array(),),),'content5|description5'=>array('content9|description9'=>array(),),'content7|description7'=>array(),)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing print_array_recursive';
		$result=print_array_recursive(
			$nested_array,
			function($array, $depth)
			{
				if($depth === 0)
					return '[OPEN_ROOT]'.PHP_EOL;
				else
					return '[OPEN_SUBROOT]'.PHP_EOL;
			},
			function($key, $value, $depth)
			{
				return '[OPEN_NODE]'.PHP_EOL;
			},
			function($key, $value, $depth)
			{
				$content=explode('|', $key);
				if(empty($value))
					return'[SINGLE_NODE] '.$content[0].'|'.$content[1].'|'.$depth.PHP_EOL;
				else
					return '[SUBROOT_LABEL] '.$content[0].'|'.$content[1].'|'.$depth.PHP_EOL;
			},
			function($key, $value, $depth)
			{
				return '[CLOSE_NODE]'.PHP_EOL;
			},
			function($array, $depth)
			{
				if($depth === 0)
					return '[CLOSE_ROOT]'.PHP_EOL;
				else
					return '[CLOSE_SUBROOT]'.PHP_EOL;
			}
		);

		if(str_replace(PHP_EOL, '', $result) === '[OPEN_ROOT][OPEN_NODE][SINGLE_NODE] content0|description0|0[CLOSE_NODE][OPEN_NODE][SINGLE_NODE] content1|description1|0[CLOSE_NODE][OPEN_NODE][SUBROOT_LABEL] content3|description3|0[OPEN_SUBROOT][OPEN_NODE][SUBROOT_LABEL] content2|description2|1[OPEN_SUBROOT][OPEN_NODE][SUBROOT_LABEL] content4|description4|2[OPEN_SUBROOT][OPEN_NODE][SINGLE_NODE] content6|description6|3[CLOSE_NODE][CLOSE_SUBROOT][CLOSE_NODE][OPEN_NODE][SINGLE_NODE] content8|description8|2[CLOSE_NODE][CLOSE_SUBROOT][CLOSE_NODE][CLOSE_SUBROOT][CLOSE_NODE][OPEN_NODE][SUBROOT_LABEL] content5|description5|0[OPEN_SUBROOT][OPEN_NODE][SINGLE_NODE] content9|description9|1[CLOSE_NODE][CLOSE_SUBROOT][CLOSE_NODE][OPEN_NODE][SINGLE_NODE] content7|description7|0[CLOSE_NODE][CLOSE_ROOT]')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>