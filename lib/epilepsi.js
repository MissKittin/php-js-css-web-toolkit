/*
 * Easily make epileptic impressions
 * Functions:
 *  flashBackground('div_id', 50, 'black', 'pink', 'blue', 'red')
 *  flashText('text_id', 50, 'white', 'red', 'purple', 'orange')
 */

function flashBackground(id, time, colorA, colorB, colorC, colorD)
{
	/*
	 * Nice function for creating epileptic impressions
	 *
	 * Usage:
	 *  flashBackground('div_id', 50, 'black', 'pink', 'blue', 'red')
	 */

	'use strict';

	document.getElementById(id).style.backgroundColor=colorA;
	setTimeout('flashBackground("'+id+'", '+time+', "'+colorB+'", "'+colorC+'", "'+colorD+'", "'+colorA+'")', time);
}
function flashText(id, time, colorA, colorB, colorC, colorD)
{
	/*
	 * Nice function as flashBackground() but for text
	 *
	 * Usage:
	 *  flashText('text_id', 50, 'white', 'red', 'purple', 'orange')
	 */

	'use strict';

	document.getElementById(id).style.color=colorA;
	setTimeout('flashText("'+id+'", '+time+', "'+colorB+'", "'+colorC+'", "'+colorD+'", "'+colorA+'")', time);
}