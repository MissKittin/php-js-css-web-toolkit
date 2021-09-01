/*
 * Time converting library
 * javascript version
 */

function seconds2human(inputSeconds)
{
	/*
	 * Usage:
	 *  seconds2human(3599)
	 * returns array with:
	 * A years B months C days D hours E minutes F seconds (G weeks total)
	 */

	'use strict';

	return {
		'seconds':inputSeconds%60,
		'minutes':Math.floor((inputSeconds%3600)/60),
		'hours':Math.floor((inputSeconds%86400)/3600),
		'days':Math.floor((inputSeconds%2592000)/86400),
		'months':Math.floor((inputSeconds/2592000)%12),
		'years':Math.floor(inputSeconds/31556926),
		'weeks':Math.floor(inputSeconds/604800)
	};
}
function convertSeconds(inputSeconds, outputFormat)
{
	/*
	 * Usage:
	 *  convert_seconds(3599, 'output_format')
	 * where output format is: minutes hours days months30 months31 years leap_years weeks
	 */

	'use strict';

	switch(outputFormat)
	{
		case 'minutes': return inputSeconds/60;
		case 'hours': return inputSeconds/3600;
		case 'days': return inputSeconds/43200;
		case 'months30': return inputSeconds/1296000;
		case 'months31': return inputSeconds/1339200;
		case 'years': return inputSeconds/31536000;
		case 'leap_years': return inputSeconds/31622400;
		case 'weeks': return inputSeconds/604800;
	}
}