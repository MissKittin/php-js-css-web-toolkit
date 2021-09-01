/*
 * Text beautifier
 * This library contains beautify()
 * and replaceAll() for older browsers
 */

function beautify(string, replace)
{
	/*
	 * Quickly replace certain word (temporary solution)
	 * replaceAll() required
	 *
	 * Usage:
	 *  beautify('ass', '@$$');
	 *  beautify('cunt', ['c*t', 'c***', '@#$%']);
	 */

	'use strict';

	if(Array.isArray(replace))
		document.body.innerHTML=document.body.innerHTML.replaceAll(string, replace[Math.floor(Math.random()*replace.length)]);
	else
		document.body.innerHTML=document.body.innerHTML.replaceAll(string, replace);
}

if(typeof String.prototype.replaceAll === 'undefined')
	String.prototype.replaceAll=function(search, replacement)
	{
		/*
		 * compatibility with older browsers
		 * https://stackoverflow.com/questions/1144783/how-to-replace-all-occurrences-of-a-string-in-javascript
		 */

		return this.split(search).join(replacement);
	};