<?php
	/*
	 * Obfuscate HTML by escaping all characters
	 *
	 * Warning:
	 *  javascript events like DOMContentLoaded will not be fired
	 *
	 * Usage:
		ob_start(ob_sfucator(
			'Nice title',
			'<h1>Enable javascript to view content</h1>'
		));
	 * or
		ob_sfucator
		::	set_title('Nice title')
		::	set_label('<h1>Enable javascript to view content</h1>');
		ob_start(ob_sfucator());
	 *
	 * Custom template:
	 *  override get_template method via inheritance
	 *  this method takes an argument with the encoded content of the page
	 */

	function ob_sfucator(?string $title=null, ?string $label=null)
	{
		if($title !== null)
			ob_sfucator::set_title($title);

		if($label !== null)
			ob_sfucator::set_label($label);

		return 'ob_sfucator::run';
	}

	class ob_sfucator
	{

		protected static $title=null;
		protected static $label=null;

		protected static function get_template($hex_string)
		{
			$title='';
			$label='';

			if(static::$title !== null)
				$title=''
				.	'<title>'
				.		static::$title
				.	'</title>';

			if(static::$label !== null)
				$label=''
				.	'<noscript>'
				.		static::$label
				.	'</noscript>';

			return ''
			.	'<!DOCTYPE html>'
			.	'<html>'
			.		'<head>'
			.			$title
			.			'<meta charset="utf-8">'
			.		'</head>'
			.		'<body onload="document.write(unescape('
			.			'\''.$hex_string.'\''
			.		'));">'
			.			$label
			.		'</body>'
			.	'</html>';
		}

		public static function set_title(string $title)
		{
			static::$title=$title;
			return static::class;
		}
		public static function set_label(string $label)
		{
			static::$label=$label;
			return static::class;
		}
		public static function run($buffer)
		{
			$buffer_length=strlen($buffer);
			$hex_string='';

			for($i=0; $i<$buffer_length; ++$i)
				$hex_string.='%'.bin2hex(
					$buffer[$i]
				);

			return static::get_template($hex_string);
		}
	}
?>