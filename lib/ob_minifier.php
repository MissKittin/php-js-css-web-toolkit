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
			['\'\'', '-src\''],
			['\' \'', '-src \''],
			str_replace(
				[
					"\r\n",
					"\r",
					"\n",
					"\t",
					'  '
				],
				'',
				str_replace(
					[
						'type="text/css"',
						"' type='text/css'",
						' type="text/javascript"',
						" type='text/javascript'"
					],
					' ',
					preg_replace(
						[
							'!/\*[^*]*\*+([^/][^*]*\*+)*/!',
							'/(?=<!--)([\s\S]*?)-->/'
						],
						'',
						$buffer
					)
				)
			)
		);
	}
?>