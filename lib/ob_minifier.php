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

		return str_replace(
			[
				'\'\'', '-src\'',
				"\r", "\n", "\t", '  ',
				' type="text/css"', " type='text/css'", ' type="text/javascript"', " type='text/javascript'",
				'<script  ', '<style  ',
				'<script >', '<style >'
			],
			[
				'\' \'', '-src \'',
				'', '', '', '',
				' ', ' ', ' ', ' ',
				'<script ', '<style ',
				'<script>', '<style>'
			],
			preg_replace(
				[
					'!/\*[^*]*\*+([^/][^*]*\*+)*/!',
					'/(?=<!--)([\s\S]*?)-->/'
				],
				'',
				$buffer
			)
		);
	}
?>