<?php
	function var_export_contains($input, string $content, bool $print=false)
	{
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
		 */

		if($print)
			return str_replace(["\n", ' '], '', var_export($input, true));

		if(str_replace(["\n", ' '], '', var_export($input, true)) === $content)
			return true;

		return false;
	}
?>