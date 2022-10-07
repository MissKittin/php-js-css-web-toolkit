/*
 * JS library for quick creating windows-style icons
 * Two versions: standard - one image per icon; and sprite - one image with all icons
 *
 * Usage:
 *  addDesktopIcon(document.getElementById('desktop_div'), './desktop-icon.png', '/desktop.html', 'Icon label', '#000')
 *  addDesktopSpriteIcon(document.getElementById('desktop_div'), './desktop-icons.png', '33px', '/desktop.html', 'Icon label', '#000')
 */

function addDesktopIcon(outputBox, iconImage, link, label, labelColor)
{
	/*
	 * Create box with win98-style icon
	 *
	 * Usage:
	 *  addDesktopIcon(document.getElementById('desktop_div'), './desktop-icon.png', '/desktop.html', 'Icon label', '#000')
	 * ('/desktop.html' can be null)
	 *
	 * The icons are stacked one below the other by default.
	 * Add .addDesktopIconBox{float:left;} to arrange the icons next to each other.
	 */

	'use strict';

	var iconStyle='background-position: center top; background-repeat: no-repeat; background-size: 32px; padding: 8px; width: 78px;';
	var labelStyle='font: normal 12px Helvetica Neue, Arial, Helvetica, sans-serif; margin: 30px 0 0; text-align: center; display: block; text-decoration: none;';

	var outputContent='<div class="addDesktopIconBox" style="'+iconStyle+' color: '+labelColor+'; background-image: url('+iconImage+');">';

	if(link === null)
		outputContent+='<p style="'+labelStyle+' color: '+labelColor+';">'+label+'</p>';
	else
		outputContent+='<a style="'+labelStyle+' color: '+labelColor+';" href="'+link+'">'+label+'</a>';

	outputBox.innerHTML+=outputContent+'</div>';
}
function addDesktopSpriteIcon(outputBox, iconImage, iconXOffset, link, label, labelColor)
{
	/*
	 * Create box with win98-style icon
	 * Sprite version
	 *
	 * Warning: sprite image must be 32px height and images must be in one row.
	 *
	 * Usage:
	 *  addDesktopSpriteIcon(document.getElementById('desktop_div'), './desktop-icons.png', '33px', '/desktop.html', 'Icon label', '#000')
	 * where '33px' is icon X offset
	 *  ('/desktop.html' can be null)
	 *
	 * The icons are stacked one below the other by default.
	 * Add .addDesktopIconBox{float:left;} to arrange the icons next to each other.
	 */

	'use strict';

	var labelStyle='font: normal 12px Helvetica Neue, Arial, Helvetica, sans-serif; text-align: center; text-decoration: none; display: block; margin: 6px 0 5px 0;';

	var outputContent=' \
		<div class="addDesktopIconBox" style="width: 78px; padding: 0 8px 8px 8px;"> \
			<div style="background-image: url('+iconImage+'); background-position-x: -'+iconXOffset+'; margin-left: 23px; width: 32px; height: 32px;"></div> \
	';

	if(link === null)
		outputContent+='<p style="'+labelStyle+' color: '+labelColor+';">'+label+'</p>';
	else
		outputContent+='<a style="'+labelStyle+' color: '+labelColor+';" href="'+link+'">'+label+'</a>';

	outputBox.innerHTML+=outputContent+'</div>';
}