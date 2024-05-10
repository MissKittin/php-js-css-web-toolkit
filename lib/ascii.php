<?php
	/*
	 * ASCII conversion library
	 *
	 * Functions:
	 *  to_ascii [returns string]
	 *   convert string to ASCII
	 *   warning: intl extension is required
	 *  to_ascii_slug [returns string]
	 *   generate a URL friendly "slug" from a given string
	 *   this function comes from laravel helpers
	 *    and complements the lv_str.php library
	 *   warning: mbstring extension is required
	 *  is_ascii [returns bool]
	 *   checks if the string is ASCII compatible
	 *
	 * Usage:
	 *  to_ascii
	 *   to_ascii('example text')
	 *  to_ascii_slug
	 *   to_ascii_slug('example text')
	 *  is_ascii
	 *   is_ascii('example text')
	 *
	 * Sources:
	 *  https://dev.to/bdelespierre/convert-accentuated-character-to-their-ascii-equivalent-in-php-3kf1
	 *  https://github.com/illuminate/support/blob/master/Str.php
	 *  https://github.com/voku/portable-ascii/blob/master/src/voku/helper/ASCII.php
	 * License: MIT
	 */

	class ascii_exception extends Exception {}

	function to_ascii(string $input)
	{
		if(!function_exists('transliterator_transliterate'))
			throw new ascii_exception('intl extension is not loaded');

		return transliterator_transliterate(
			'NFKC; [:Nonspacing Mark:] Remove; NFKC; Any-Latin; Latin-ASCII',
			$input
		);
	}
	function to_ascii_slug(string $title, string $separator='-', array $dictionary=['@'=>'at'])
	{
		if(!function_exists('mb_strtolower'))
			throw new ascii_exception('mbstring extension is not loaded');

		if((!isset($separator[0])) || isset($separator[1])) // (strlen($separator) !== 1)
			throw new ascii_exception('The separator must be one character long');

		$flip='_';

		if($separator === '_')
			$flip='-';

		$title=preg_replace(
			'!['.preg_quote($flip).']+!u',
			$separator,
			to_ascii($title)
		);

		foreach($dictionary as $key=>$value)
			$dictionary[$key]=$separator.$value.$separator;

		return trim(
			preg_replace(
				'!['.preg_quote($separator).'\s]+!u', $separator,
				preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower(
					str_replace(array_keys($dictionary), array_values($dictionary), $title),
					'UTF-8'
				))
			),
			$separator
		);
	}
	function is_ascii(string $input)
	{
		return (
			preg_match(
				'/'."[^\x09\x10\x13\x0A\x0D\x20-\x7E]".'/',
				$input
			) === 0
		);
	}
?>