<?php
	/*
	 * Time converting library
	 *
	 * Usage:
	 * seconds2human(3599)
	 *  returns array with integers:
	 *  A years B months C days D hours E minutes F seconds (G weeks total)
	 * convert_seconds(3599, 'output_format')
	 *  where output format is: minutes hours days months30 months31 years leap_years weeks
	 *  returns float or integer
	 */

	function seconds2human(int $input_seconds)
	{
		/*
		 * Convert seconds to time string
		 *
		 * Usage:
		 *  seconds2human(3599)
		 *   returns array with integers:
		 *   A years B months C days D hours E minutes F seconds (G weeks total)
		 */

		return [
			'seconds'=>$input_seconds%60,
			'minutes'=>(int)floor(($input_seconds%3600)/60),
			'hours'=>(int)floor(($input_seconds%86400)/3600),
			'days'=>(int)floor(($input_seconds%2592000)/86400),
			'months'=>(int)floor(($input_seconds/2592000)%12),
			'years'=>(int)floor($input_seconds/31556926),
			'weeks'=>(int)floor($input_seconds/604800)
		];
	}
	function convert_seconds(int $input_seconds, string $output_format)
	{
		/*
		 * Convert seconds to higher unit
		 *
		 * Usage:
		 *  convert_seconds(3599, 'output_format')
		 *   where output format is: minutes hours days months30 months31 years leap_years weeks
		 *   returns float or integer
		 */

		switch($output_format)
		{
			case 'minutes':
				return $input_seconds/60;
			case 'hours':
				return $input_seconds/3600;
			case 'days':
				return $input_seconds/43200;
			case 'months30':
				return $input_seconds/1296000;
			case 'months31':
				return $input_seconds/1339200;
			case 'years':
				return $input_seconds/31536000;
			case 'leap_years':
				return $input_seconds/31622400;
			case 'weeks':
				return $input_seconds/604800;
		}
	}
?>