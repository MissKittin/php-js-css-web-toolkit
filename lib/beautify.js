function beautify(id, string, replace)
{
	/*
	 * Quickly replace certain word (temporary solution)
	 *
	 * Usage:
	 *  beautify(document.body, 'ass', '@$$');
	 *  beautify(document.body, 'cunt', ['c*t', 'c***', '@#$%']);
	 */

	'use strict';

	if(Array.isArray(replace))
		id.innerHTML=id.innerHTML.split(string).join( // .replaceAll(string, replace())
			replace[Math.floor(
				Math.random()*replace.length
			)]
		);
	else
		id.innerHTML=id.innerHTML.split(string).join(replace); // .replaceAll(string, replace);
}