/*
 * Easily make epileptic impressions
 *
 * Functions:
 *  flashBackground(document.getElementById('div_id'), 50, 'black', 'pink', 'blue', 'red');
 *  flashText(document.getElementById('text_id'), 50, 'white', 'red', 'purple', 'orange');
 */

function flashBackground(
	id,
	time,
	colorA, colorB, colorC, colorD
){
	/*
	 * Nice function for creating epileptic impressions
	 *
	 * Usage:
		flashBackground(
			document.getElementById('div_id'),
			50,
			'black', 'pink', 'blue', 'red'
		);
	 */

	'use strict';

	id.style.backgroundColor=colorA;

	setTimeout(function(){
		flashBackground(
			id,
			time,
			colorB, colorC, colorD, colorA
		);
	}, time);
}
function flashText(
	id,
	time,
	colorA, colorB, colorC, colorD
){
	/*
	 * Nice function as flashBackground() but for text
	 *
	 * Usage:
		flashText(
			document.getElementById('text_id'),
			50,
			'white', 'red', 'purple', 'orange'
		);
	 */

	'use strict';

	id.style.color=colorA;

	setTimeout(function(){
		flashText(
			id,
			time,
			colorB, colorC, colorD, colorA
		);
	}, time);
}