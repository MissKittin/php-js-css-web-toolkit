<?php
	function is_float_equal(float $input, float $expected_result)
	{
		/*
		 * Quickly check if two floats are equal
		 * Designed for testing purposes
		 *
		 * Note:
		 *  for PHP < 7.2 you also need to include the pf_php_float.php library
		 */

		return (abs($input-$expected_result) < PHP_FLOAT_EPSILON);
	}
?>