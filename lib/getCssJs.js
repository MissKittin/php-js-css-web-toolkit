/*
 * Easily download and apply assets at run time
 * For CSS and JS files
 */

function getCSS(file)
{
	/*
	 * Download and apply css at run time
	 * Usage:
	 *  getCSS('/style.css')
	 */

	'use strict';

	var link=document.createElement('link');

	link.rel='stylesheet';
	link.type='text/css';
	link.href=location.origin+file;

	document.getElementsByTagName('head')[0].appendChild(link);
}
function getJS(file)
{
	/*
	 * Download and run js at run time
	 * Usage:
	 *  getJS('/script.js')
	 */

	'use strict';

	var script=document.createElement('script');

	script.src=location.origin+file;

	document.getElementsByTagName('head')[0].appendChild(script);
}