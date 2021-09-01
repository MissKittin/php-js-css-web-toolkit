<?php
	function ob_minifier($buffer)
	{
		/*
		 * Simple minifier and compressor (25-27.11.2019)
		 * minify HTML and CSS from PHP output buffer
		 *
		 * Usage:
		 *  ob_start('ob_minifier');
		 */

		$buffer=preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer); // remove comments - css
		$buffer=preg_replace('/(?=<!--)([\s\S]*?)-->/', '', $buffer); // remove comments - html
		$buffer=str_replace(['type="text/css"', "' type='text/css'", ' type="text/javascript"', " type='text/javascript'"], ' ', $buffer); // remove unneeded element meta
		$buffer=str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer); // remove whitespace
		return $buffer;
	}
?>