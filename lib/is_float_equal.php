<?php
	/*
	 * Quickly check if two floats are equal
	 * Designed for testing purposes
	 */

	function is_float_equal(float $input, float $expected_result)
	{
		return (abs($input-$expected_result) < PHP_FLOAT_EPSILON);
	}

	if(!defined('PHP_FLOAT_EPSILON'))
		define('PHP_FLOAT_EPSILON', 2.2204460492503E-16);
?>