function linkify(inputString)
{
	/*
	 * Convert plaintext links to anchors
	 * converts all strings with http:// https:// ftp:// and file://
	 * and strings with www. without protocol specified
	 *
	 * Author: rooseve (https://github.com/rooseve)
	 *
	 * Usage:
	 *  document.getElementById('block').innerHTML=linkify(document.getElementById('block').innerHTML);
	 * or
		var blocks=document.getElementsByClassName('block');
		var i;
		for(i=0; i<blocks.length; i++)
			blocks[i].innerHTML=linkify(blocks[i].innerHTML);
	 *
	 * Sources:
	 *  https://stackoverflow.com/questions/20419989/linkify-clickable-text-urls-but-ignore-those-already-wrapped-in-a-hrefs
	 *  http://jsfiddle.net/rooseve/4qa5Z/1/
	 *  https://stackoverflow.com/questions/37684/how-to-replace-plain-urls-with-links
	 */

	'use strict';

	inputString='>'+inputString+'<';
	inputString=inputString.replace(/>([^<>]+)(?!<\/a)</g, function(match, txt){
		return '>'+txt.replace(/(\b(https?|ftp|file):\/\/[-A-Z0-9+&amp;&@#\/%?=~_|!:,.;]*[-A-Z0-9+&amp;&@#\/%=~_|])/ig, '<a href="$1">$1</a>')+'<';
	});
	inputString=inputString.replace(/>([^<>]+)(?!<\/a)</g, function(match, txt){
		return '>'+txt.replace(/\b(www\.[\S]+)\b/gi, '<a href="http://$1">$1</a>')+'<';
	});

	return inputString.substring(1, inputString.length-1);
}