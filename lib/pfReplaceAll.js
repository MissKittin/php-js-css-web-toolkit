/*
 * replaceAll() polyfill
 *
 * Source:
 *  https://stackoverflow.com/questions/1144783/how-to-replace-all-occurrences-of-a-string-in-javascript
 */

if(typeof String.prototype.replaceAll === 'undefined')
	String.prototype.replaceAll=function(search, replacement)
	{
		return this.split(search).join(replacement);
	};