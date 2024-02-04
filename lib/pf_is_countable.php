<?php
	/*
	 * is_countable() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill/blob/1.x/src/Php73/bootstrap.php
	 * License: MIT
	 */

	if(!function_exists('is_countable'))
	{
		function is_countable($variable)
		{
			if(is_array($variable))
				return true;

			if($variable instanceof \Countable)
				return true;

			if($variable instanceof \ResourceBundle)
				return true;

			if($variable instanceof \SimpleXmlElement)
				return true;

			return false;
		}
	}
?>