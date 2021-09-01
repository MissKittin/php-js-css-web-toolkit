<?php
	function ob_sfucator($buffer)
	{
		/*
		 * Obsfucate HTML by escaping all characters
		 *
		 * Usage:
			$ob_sfucator=array(
				'title'=>'Nice title',
				'label'=>'<h1>Enable javascript to view content</h1>'
			);
			ob_start('ob_sfucator');
		 *  where $ob_sfucator is global variable
		 */

		global $ob_sfucator;

		$hexString='';
		for($i=0; $i<strlen($buffer); $i++)
			$hexString.='%' . bin2hex($buffer[$i]);

		return '<!DOCTYPE html><html><head><title>' . $ob_sfucator['title'] . '</title><meta charset="utf-8"></head><body onload="document.write(unescape(\'' . $hexString . '\'));"><noscript>' . $ob_sfucator['label'] . '</noscript></body></html>';
	}
?>