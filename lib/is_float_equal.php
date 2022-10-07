<?php
	/*
	 * Quickly check if two floats are equal
	 * Mainly designed for testing purposes
	 */

	function is_float_equal(float $input, float $expected_result)
	{
		if(abs($input-$expected_result) < PHP_FLOAT_EPSILON)
			return true;

		return false;
	}

	if(!defined('PHP_FLOAT_EPSILON'))
		define('PHP_FLOAT_EPSILON', 0.00001);
?>