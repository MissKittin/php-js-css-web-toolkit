<?php
	function var_export_contains(
		$input,
		string $content,
		bool $print=false,
		?callable $postprocess=null
	){
		/*
		 * Check if the content of the variable is correct
		 * or dump the contents of the variable into a flat string
		 * Mainly designed for testing purposes
		 *
		 * Usage:
		 	var_export_contains($my_array, '', true) // returns string eg. "array(0=>'aa',1=>'ba',2=>'ba',3=>'bb',)"
		 	var_export_contains(
				$my_array,
				"array(0=>'aa',1=>'ba',2=>'ba',3=>'bb',)"
			) // returns bool
		 *
		 * Usage with post-processing:
			var_export_contains(
				$my_array, // 1=>'replaceme'
				"array(0=>'aa',1=>'replacedstring',2=>'ba',3=>'bb',)",
				true,
				function($input)
				{
					return str_replace('replaceme', 'replacedstring', $input);
				}
			) // returns string eg. "array(0=>'aa',1=>'replacedstring',2=>'ba',3=>'bb',)"
			var_export_contains(
				$my_array, // 1=>'replaceme'
				"array(0=>'aa',1=>'replacedstring',2=>'ba',3=>'bb',)",
				false,
				function($input)
				{
					return str_replace('replaceme', 'replacedstring', $input);
				}
			) // returns bool
		 */

		if($postprocess === null)
			$postprocess=function($input)
			{
				return $input;
			};

		$result=$postprocess(str_replace(
			["\n", ' '],
			'',
			var_export($input, true)
		));

		if($print)
			return $result;

		if($result === $content)
			return true;

		return false;
	}
?>