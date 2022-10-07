/*
 * Text beautifier
 * This library contains beautify()
 * and replaceAll() for older browsers
 */

function beautify(id, string, replace)
{
	/*
	 * Quickly replace certain word (temporary solution)
	 *
	 * Warning:
	 *  replaceAll() is required
	 *
	 * Usage:
	 *  beautify(document.body, 'ass', '@$$');
	 *  beautify(document.body, 'cunt', ['c*t', 'c***', '@#$%']);
	 */

	'use strict';

	if(Array.isArray(replace))
		id.innerHTML=id.innerHTML.replaceAll(
			string,
			replace[Math.floor(
				Math.random()*replace.length
			)]
		);
	else
		id.innerHTML=id.innerHTML.replaceAll(string, replace);
}

if(typeof String.prototype.replaceAll === 'undefined')
	String.prototype.replaceAll=function(search, replacement)
	{
		/*
		 * Compatibility with older browsers
		 *
		 * Source:
		 *  https://stackoverflow.com/questions/1144783/how-to-replace-all-occurrences-of-a-string-in-javascript
		 */

		return this.split(search).join(replacement);
	};