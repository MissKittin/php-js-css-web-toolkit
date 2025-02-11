/*
 * JS library for quick creating windows-style icons
 * Two versions:
 *  standard - one image per icon
 *  sprite - one image with all icons
 *
 * Usage:
	addDesktopIcon(
		document.getElementById('desktop_div'),
		'./desktop-icon.png',
		'/desktop.html', // link, can be null
		'Icon label',
		'#000000' // label color
	);
	addDesktopSpriteIcon(
		document.getElementById('desktop_div'),
		'./desktop-icons.png',
		'33px', // icon X offset
		'/desktop.html', // link, can be null
		'Icon label',
		'#000000' // label color
	);
 */

function addDesktopIcon(
	outputBox,
	iconImage,
	link,
	label, labelColor
){
	/*
	 * Create box with win98-style icon
	 *
	 * Note:
	 *  the icons are stacked one below the other by default
	 *  add .addDesktopIconBox{float:left;}
	 *   to arrange the icons next to each other
	 *
	 * Usage:
		addDesktopIcon(
			document.getElementById('desktop_div'),
			'./desktop-icon.png',
			'/desktop.html', // can be null
			'Icon label',
			'#000000'
		);
	 */

	'use strict';

	var iconBox=document.createElement('div');
	iconBox.classList.add('addDesktopIconBox');
	iconBox.style.backgroundPosition='center top';
	iconBox.style.backgroundRepeat='no-repeat';
	iconBox.style.backgroundSize='32px';
	iconBox.style.padding='8px';
	iconBox.style.width='78px';
	iconBox.style.color=labelColor;
	iconBox.style.backgroundImage='url('+iconImage+')';

	var iconBoxLabel;

	if(link === null)
		iconBoxLabel=document.createElement('p');
	else
	{
		iconBoxLabel=document.createElement('a');
		iconBoxLabel.href=link;
	}

	iconBoxLabel.style.font='normal 12px Helvetica Neue, Arial, Helvetica, sans-serif';
	iconBoxLabel.style.margin='30px 0 0';
	iconBoxLabel.style.textAlign='center';
	iconBoxLabel.style.display='block';
	iconBoxLabel.style.textDecoration='none';
	iconBoxLabel.style.color=labelColor;
	iconBoxLabel.appendChild(
		document.createTextNode(label)
	);

	iconBox.appendChild(iconBoxLabel);

	outputBox.appendChild(iconBox);
}
function addDesktopSpriteIcon(
	outputBox,
	iconImage, iconXOffset,
	link,
	label, labelColor
){
	/*
	 * Create box with win98-style icon
	 * Sprite version
	 *
	 * Warning:
	 *  sprite image must be 32px height
	 *  and images must be in one row
	 *
	 * Note:
	 *  the icons are stacked one below the other by default
	 *  add .addDesktopIconBox{float:left;}
	 *   to arrange the icons next to each other
	 *
	 * Usage:
		addDesktopSpriteIcon(
			document.getElementById('desktop_div'),
			'./desktop-icons.png',
			'33px', // icon X offset
			'/desktop.html', // can be null
			'Icon label',
			'#000000'
		);
	 */

	'use strict';

	var iconBox=document.createElement('div');
	iconBox.classList.add('addDesktopIconBox');
	iconBox.style.width='78px';
	iconBox.style.padding='0 8px 8px 8px';

	var icon=document.createElement('div');
	icon.style.backgroundImage='url('+iconImage+')';
	icon.style.backgroundPositionX='-'+iconXOffset;
	icon.style.marginLeft='23px';
	icon.style.width='32px';
	icon.style.height='32px';

	var iconBoxLabel;

	if(link === null)
		iconBoxLabel=document.createElement('p');
	else
	{
		iconBoxLabel=document.createElement('a');
		iconBoxLabel.href=link;
	}

	iconBoxLabel.style.font='normal 12px Helvetica Neue, Arial, Helvetica, sans-serif';
	iconBoxLabel.style.margin='6px 0 5px 0';
	iconBoxLabel.style.textAlign='center';
	iconBoxLabel.style.display='block';
	iconBoxLabel.style.textDecoration='none';
	iconBoxLabel.style.color=labelColor;
	iconBoxLabel.appendChild(
		document.createTextNode(label)
	);

	iconBox.appendChild(icon);
	iconBox.appendChild(iconBoxLabel);

	outputBox.appendChild(iconBox);
}