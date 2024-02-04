<?php
	/*
	 * PHP_FLOAT_* polyfill
	 *
	 * Sources:
	 *  https://github.com/symfony/polyfill-php72/blob/1.x/bootstrap.php
	 */

	if(!defined('PHP_FLOAT_DIG'))
		define('PHP_FLOAT_DIG', 15);

	if(!defined('PHP_FLOAT_EPSILON'))
		define('PHP_FLOAT_EPSILON', 2.2204460492503E-16);

	if(!defined('PHP_FLOAT_MIN'))
		define('PHP_FLOAT_MIN', 2.2250738585072E-308);

	if(!defined('PHP_FLOAT_MAX'))
		define('PHP_FLOAT_MAX', 1.7976931348623157E+308);
?>