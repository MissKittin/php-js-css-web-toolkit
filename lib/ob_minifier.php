<?php
	function ob_minifier($buffer)
	{
		/*
		 * Simple minifier and compressor
		 * minify HTML and CSS from PHP output buffer
		 *
		 * Usage:
		 *  ob_start('ob_minifier')
		 */

		$buffer=preg_replace([
			'!/\*[^*]*\*+([^/][^*]*\*+)*/!',
			'/(?=<!--)([\s\S]*?)-->/'
		], '', $buffer);
		$buffer=str_replace([
			'type="text/css"',
			"' type='text/css'",
			' type="text/javascript"',
			" type='text/javascript'"
		], ' ', $buffer);
		$buffer=str_replace([
			"\r\n",
			"\r",
			"\n",
			"\t",
			'  ',
			'    ',
			'    '
		], '', $buffer);
		$buffer=str_replace(['\'\'', '-src\''], ['\' \'', '-src \''], $buffer);
		return $buffer;
	}
?>