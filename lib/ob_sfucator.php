<?php
	function ob_sfucator(
		array $_ob_sfucator=[],
		bool $raw=false,
		string $raw_content=''
	){
		/*
		 * Obsfucate HTML by escaping all characters
		 *
		 * Usage:
			ob_sfucator([
				// this array is optional
				'title'=>'Nice title',
				'label'=>'<h1>Enable javascript to view content</h1>'
			]);
		 * or
			$output=ob_sfucator(
				[
					'title'=>'Nice title',
					'label'=>'<h1>Enable javascript to view content</h1>'
				],
				true, $buffer
			);
		 */

		$ob_start_function=function($buffer) use($_ob_sfucator)
		{
			$buffer_length=strlen($buffer);
			$hex_string='';

			for($i=0; $i<$buffer_length; ++$i)
				$hex_string.='%'.bin2hex($buffer[$i]);

			$title='';
			if(isset($_ob_sfucator['title']))
				$title='<title>'.$_ob_sfucator['title'].'</title>';

			$label='';
			if(isset($_ob_sfucator['label']))
				$label='<noscript>'.$_ob_sfucator['label'].'</noscript>';

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
		};

		if($raw)
			return $ob_start_function($raw_content);

		ob_start($ob_start_function);
	}
?>