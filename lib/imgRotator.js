function imgRotator(id, pics)
{
	/*
	 * Rotate images on selected id
	 *
	 * Usage:
	 *  imgRotator('mydiv', ['/img1.png', '/img2.png', '/img3.png'])
	 */

	'use strict';

	id=document.getElementById(id);
	var i=1;
	var idLength=pics.length;
	setInterval(function() { id.src=pics[i++%idLength]; }, 2000);
}