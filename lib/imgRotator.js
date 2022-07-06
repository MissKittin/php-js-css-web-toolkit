function imgRotator(id, timeout, pics)
{
	/*
	 * Rotate images on selected id
	 *
	 * Usage:
	 *  imgRotator(document.getElementById('myimgtag'), 2000, ['/imgstart.png', '/img2.png', '/img3.png'])
	 * Where myimgtag is
	 *  <img id="myimgtag" src="/imgstart.png">
	 */

	'use strict';

	var i=1;
	var idLength=pics.length;

	setInterval(function(){
		id.src=pics[i++%idLength];
	}, timeout);
}