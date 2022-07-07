<?php
	function ob_sfucator($buffer)
	{
		/*
		 * Obsfucate HTML by escaping all characters
		 *
		 * Usage:
			// this is optional
			$GLOBALS['_ob_sfucator']=[
				'title'=>'Nice title',
				'label'=>'<h1>Enable javascript to view content</h1>'
			];

			ob_start('ob_sfucator');
		 */

		$buffer_length=strlen($buffer);
		$hex_string='';

		for($i=0; $i<$buffer_length; ++$i)
			$hex_string.='%'.bin2hex($buffer[$i]);

		$title='';
		if(isset($GLOBALS['_ob_sfucator']['title']))
			$title='<title>'.$GLOBALS['_ob_sfucator']['title'].'</title>';

		$label='';
		if(isset($GLOBALS['_ob_sfucator']['label']))
			$label='<noscript>'.$GLOBALS['_ob_sfucator']['label'].'</noscript>';

		return ''
		.	'<!DOCTYPE html>'
		.	'<html>'
		.		'<head>'
		.			$title
		.			'<meta charset="utf-8">'
		.		'</head>'
		.		'<body onload="document.write(unescape(\''.$hex_string.'\'));">'
		.			$label
		.		'</body>'
		.	'</html>'
		;
	}
?>