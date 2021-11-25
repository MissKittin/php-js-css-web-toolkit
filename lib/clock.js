/*
 * Clock library
 *
 * Warning: all sprite images must be on one line next to each other
 *  offset 0 starts with the first left horizontal line
 *
 * Functions:
 *  clockDigital('clock_div_id', ':')
	clockDigitalImage(
		'clock_div_id',
		['./0.png', './1.png', './2.png', './3.png', './4.png', './5.png', './6.png', './7.png', './8.png', './9.png'],
		'./separator.png'
	)
	clockDigitalImageSprite(
		'clock_div_id',
		'./clock.png', // sprite image url
		'24px', // clock's height (the same as sprite image url)
		'16px', // clock's digit width
		'8px', // clock's separator width
		['0px', '16px', '32px', '48px', '64px', '80px', '96px', '112px', '128px', '144px'], // digit's offsets
		'160px' // separator offset
	)
 *  clockAnalogCSS('clock_div_id', '#000000')
	clockAnalogImage(
		'clock_div_id',
		['./clock-face.png', './hour-hand.png', './minute-hand.png', './second-hand.png', './top-image.png']
	)
	clockAnalogImageSprite(
		'clock_div_id',
		'./clockspriteanalog.png', // sprite image url
		['400px', '800px', '1200px', '1600px'] // offsets
	)
 */

function clockDigital(id, separator)
{
	/*
	 * Render simple digital clock in div
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> and style it
	 *
	 * Usage: clockDigital('clock_div_id', ':')
	 *  where ':' is seprator
	 */

	'use strict';

	var currentTime=new Date();
	var currentHour=currentTime.getHours().toString();
		if(currentHour.length === 1) currentHour='0'+currentHour;
	var currentMinutes=currentTime.getMinutes().toString();
		if(currentMinutes.length === 1) currentMinutes='0'+currentMinutes;
	var currentSeconds=currentTime.getSeconds().toString();
		if(currentSeconds.length === 1) currentSeconds='0'+currentSeconds;

	document.getElementById(id).innerHTML=currentHour+separator+currentMinutes+separator+currentSeconds;

	setTimeout('clockDigital("'+id+'", "'+separator+'")', 1000);
}
function clockDigitalImage(id, pics, separator)
{
	/*
	 * Render digital clock in div from images
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> and style it
	 *
	 * Usage:
		clockDigitalImage(
			'clock_div_id',
			['./0.png', './1.png', './2.png', './3.png', './4.png', './5.png', './6.png', './7.png', './8.png', './9.png'],
			'./separator.png'
		)
	 * where [] has digit's urls
	 */

	'use strict';
	var i;

	var currentTime=new Date();
	var currentHour=currentTime.getHours().toString();
		if(currentHour.length === 1) currentHour='0'+currentHour;
	var currentMinutes=currentTime.getMinutes().toString();
		if(currentMinutes.length === 1) currentMinutes='0'+currentMinutes;
	var currentSeconds=currentTime.getSeconds().toString();
		if(currentSeconds.length === 1) currentSeconds='0'+currentSeconds;

	var output='';
	for(i=0; i<currentHour.length; i++)
		output+='<img src="'+pics[currentHour[i]]+'" alt="clock-'+currentHour[i]+'">';
	output+='<img src="'+separator+'" alt="separator">';
	for(i=0; i<currentMinutes.length; i++)
		output+='<img src="'+pics[currentMinutes[i]]+'" alt="clock-'+currentMinutes[i]+'">';
	output+='<img src="'+separator+'" alt="separator">';
	for(i=0; i<currentSeconds.length; i++)
		output+='<img src="'+pics[currentSeconds[i]]+'" alt="clock-'+currentSeconds[i]+'">';
	document.getElementById(id).innerHTML=output;

	setTimeout('clockDigitalImage("'+id+'", '+JSON.stringify(pics)+', "'+separator+'")', 1000);
}
function clockDigitalImageSprite(id, spritePic, clockHeight, digitWidth, separatorWidth, picsOffsets, separatorOffset, setup=true)
{
	/*
	 * Render digital clock in div from sprite image
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> and style it
	 *
	 * Sprite image layout:
	 *  put all images side by side on one line
	 *  all digits and separator must be of the same height
	 *  all digits must be of the same width (except separator)
	 *
	 * Usage:
		clockDigitalImageSprite(
			'clock_div_id',
			'./clock.png', // sprite image url
			'24px', // clock's height (the same as sprite image url)
			'16px', // clock's digit width
			'8px', // clock's separator width
			['0px', '16px', '32px', '48px', '64px', '80px', '96px', '112px', '128px', '144px'], // digit's offsets
			'160px' // separator offset
		)
	 * where digit's offsets are in sequence: 0, 1, 2, 3, 4, 5, 6, 8, 9
	 */

	var clockContainer=document.getElementById(id);

	if(setup)
	{
		clockContainer.innerHTML=' \
			<div style="display: flex; height: '+clockHeight+';"> \
				<div style="background-image: url('+spritePic+'); width: '+digitWidth+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+digitWidth+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+separatorWidth+'; background-position-x: -'+separatorOffset+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+digitWidth+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+digitWidth+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+separatorWidth+'; background-position-x: -'+separatorOffset+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+digitWidth+';"></div> \
				<div style="background-image: url('+spritePic+'); width: '+digitWidth+';"></div> \
			</div> \
		';
	}

	var currentTime=new Date();
	var currentHour=currentTime.getHours().toString();
		if(currentHour.length === 1) currentHour='0'+currentHour;
	var currentMinutes=currentTime.getMinutes().toString();
		if(currentMinutes.length === 1) currentMinutes='0'+currentMinutes;
	var currentSeconds=currentTime.getSeconds().toString();
		if(currentSeconds.length === 1) currentSeconds='0'+currentSeconds;

	var digits=clockContainer.children[0].children;
	digits[0].style.backgroundPositionX='-'+picsOffsets[currentHour[0]];
	digits[1].style.backgroundPositionX='-'+picsOffsets[currentHour[1]];
	digits[3].style.backgroundPositionX='-'+picsOffsets[currentMinutes[0]];
	digits[4].style.backgroundPositionX='-'+picsOffsets[currentMinutes[1]];
	digits[6].style.backgroundPositionX='-'+picsOffsets[currentSeconds[0]];
	digits[7].style.backgroundPositionX='-'+picsOffsets[currentSeconds[1]];

	setTimeout('clockDigitalImageSprite("'+id+'", "'+spritePic+'", "'+clockHeight+'", "'+digitWidth+'", "'+separatorWidth+'", '+JSON.stringify(picsOffsets)+', "'+separatorOffset+'", false)', 1000);
}
function clockAnalogCSS(id, color, animate=true, setup=true, loops=[0, 0, 0])
{
	/*
	 * Draw analog clock in CSS
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> with appropriate styles (width, height)
	 *  this div must be square!
	 *
	 * Usage: clockAnalogCSS('clock_div_id', '#000000')
	 *  where #000000 is clock color
	 *  if you want background-color, define it for the #clock_div_id
	 *  you can also disable animations - add false after last argument
	 */

	var i;

	var clockContainer=document.getElementById(id);

	var date=new Date();
	var dateSeconds=date.getSeconds()*6;
	var dateMinutes=date.getMinutes()*6;
	var dateHours=(date.getHours()%12)*30;

	if(dateSeconds%360 === 0) loops[0]++;
	if(dateMinutes%360 === 0) loops[1]++;
	if(dateHours%360 === 0) loops[2]++;

	dateSeconds+=loops[0]*360;
	dateMinutes+=loops[1]*360;
	dateHours+=loops[2]*360;

	if(setup)
	{
		// Big 12 9 3 6
		var clockContainerContent=' \
			<div style="position: absolute; width: 100%; height: 100%;"> \
				<div style="position: absolute; left: 49.5%; width: 1%; height: 7.5%; background-color: '+color+';"></div> \
				<div style="position: absolute; top: 49.5%; width: 7.5%; height: 1%; background-color: '+color+';"></div> \
				<div style="position: absolute; top: 49.5%; left: 92.5%; width: 7.5%; height: 1%; background-color: '+color+';"></div> \
				<div style="position: absolute; top: 92.5%; left: 49.5%; width: 1%; height: 7.5%; background-color: '+color+';"></div> \
			</div> \
		';

		for(i=30; i<90; i=i+30) // Medium 12 9 3 6 Deg 30 60
			clockContainerContent+=' \
				<div style="transform: rotate('+i+'deg); position: absolute; width: 100%; height: 100%;"> \
					<div style="position: absolute; left: 50%; width: 0.5%; height: 5%; background-color: '+color+';"></div> \
					<div style="position: absolute; top: 50%; width: 5%; height: 0.5%; background-color: '+color+';"></div> \
					<div style="position: absolute; top: 50%; left: 95%; width: 5%; height: 0.5%; background-color: '+color+';"></div> \
					<div style="position: absolute; top: 95%; left: 50%; width: 0.5%; height: 5%; background-color: '+color+';"></div> \
				</div> \
			';

		for(i=6; i<90; i=i+6) // Small 12 9 3 6 Deg 6 12 18 24 36 42 48 54 66 72 78 84
			if((i !== 30) && (i !== 60))
				clockContainerContent+=' \
					<div style="transform: rotate('+i+'deg); position: absolute; width: 100%; height: 100%;"> \
						<div style="position: absolute; left: 50%; width: 0.5%; height: 2.5%; background-color: '+color+';"></div> \
						<div style="position: absolute; top: 50%; width: 2.5%; height: 0.5%; background-color: '+color+';"></div> \
						<div style="position: absolute; top: 50%; left: 97%; width: 2.5%; height: 0.5%; background-color: '+color+';"></div> \
						<div style="position: absolute; top: 97%; left: 50%; width: 0.5%; height: 2.5%; background-color: '+color+';"></div> \
					</div> \
				';

		clockContainerContent+=' \
			<div style="transform: rotate('+dateHours+'deg); position: absolute; width: 100%; height: 100%;"> \
				<div style="position: absolute; top: 20%; left: 48.5%; width: 3.5%; height: 30%; background-color: '+color+';"></div> \
			</div> \
			<div style="transform: rotate('+dateMinutes+'deg); position: absolute; width: 100%; height: 100%;"> \
				<div style="position: absolute; top: 8%; left: 49%; width: 2%; height: 49.5%; background-color: '+color+';"></div> \
			</div> \
			<div style="transform: rotate('+dateSeconds+'deg); position: absolute; width: 100%; height: 100%;"> \
				<div style="position: absolute; top: 5%; left: 49.5%; width: 1%; height: 55%; background-color: '+color+';"></div> \
			</div> \
			<div style="position: absolute; top: 44.75%; left: 44.75%; border-radius: 50%; width: 10.5%; height: 10.5%; background-color: '+color+';"></div> \
		';

		clockContainer.innerHTML='<div style="position: relative; border-radius: 50%; width: 100%; height: 100%;">' + clockContainerContent + '</div>';

		if(animate)
		{
			var handIds=[17, 16, 15];
			for(i in handIds)
				clockContainer.children[0].children[handIds[i]].style.transition='transform 0.2s cubic-bezier(.4, 2.08, .55, .44)';
		}
	}

	var clockRoot=clockContainer.children[0];
	clockRoot.children[17].style.transform='rotate('+dateSeconds+'deg)';
	clockRoot.children[16].style.transform='rotate('+dateMinutes+'deg)';
	clockRoot.children[15].style.transform='rotate('+dateHours+'deg)';

	setTimeout('clockAnalogCSS("'+id+'", "'+color+'", false, false, '+JSON.stringify(loops)+')', 1000);
}
function clockAnalogImage(id, pics, animate=true, setup=true, loops=[0, 0, 0])
{
	/*
	 * Render analog clock in div from images
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> with width and height (position: relative will be added automatically)
	 *  this div must be square!
	 *
	 * Images' layout:
	 *  all images must be the same width and height and must be squares
	 *  the images are stacked one on top of the other from the bottom in the following order: clock_face hour_hand minute_hand second_hand top_dot
	 *  all arrows must be at 12 o'clock and must be drawn from the center of the square to the top margin (the margin depends on the type of hand)
	 *  the last image is top_dot - a dot in the center of the clock that covers the common point of all hands
	 *
	 * Usage:
		clockAnalogImage(
			'clock_div_id',
			['./clock-face.png', './hour-hand.png', './minute-hand.png', './second-hand.png', './top-image.png']
		)
	 * where [] is an array with the urls
	 * you can also disable animations - add false after last argument
	 */

	var clockContainer=document.getElementById(id);

	var date=new Date();
	var dateSeconds=date.getSeconds()*6;
	var dateMinutes=date.getMinutes()*6;
	var dateHours=(date.getHours()%12)*30;

	if(dateSeconds%360 === 0) loops[0]++;
	if(dateMinutes%360 === 0) loops[1]++;
	if(dateHours%360 === 0) loops[2]++;

	dateSeconds+=loops[0]*360;
	dateMinutes+=loops[1]*360;
	dateHours+=loops[2]*360;

	if(setup)
	{
		clockContainer.style.position='relative';
		clockContainer.innerHTML=' \
			<div style="background-image: url('+pics[0]+'); background-repeat: no-repeat; position: absolute; width: 100%; height: 100%;"> \
				<div style="background-image: url('+pics[1]+'); background-repeat: no-repeat; transform: rotate('+dateHours+'deg); position: absolute; width: 100%; height: 100%;"></div> \
				<div style="background-image: url('+pics[2]+'); background-repeat: no-repeat; transform: rotate('+dateMinutes+'deg); position: absolute; width: 100%; height: 100%;"></div> \
				<div style="background-image: url('+pics[3]+'); background-repeat: no-repeat; transform: rotate('+dateSeconds+'deg); position: absolute; width: 100%; height: 100%;"></div> \
				<div style="background-image: url('+pics[4]+'); background-repeat: no-repeat; position: absolute; width: 100%; height: 100%;"></div> \
			</div> \
		';

		if(animate)
		{
			var handIds=[2, 1, 0];
			for(i in handIds)
				clockContainer.children[0].children[handIds[i]].style.transition='transform 0.2s cubic-bezier(.4, 2.08, .55, .44)';
		}
	}

	var clockRoot=clockContainer.children[0];
	clockRoot.children[2].style.transform='rotate('+dateSeconds+'deg)';
	clockRoot.children[1].style.transform='rotate('+dateMinutes+'deg)';
	clockRoot.children[0].style.transform='rotate('+dateHours+'deg)';

	setTimeout('clockAnalogImage("'+id+'", false, false, false, '+JSON.stringify(loops)+')', 1000);
}
function clockAnalogImageSprite(id, spritePic, picsOffsets, animate=true, setup=true, loops=[0, 0, 0])
{
	/*
	 * Render analog clock in div from sprite image
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> with width and height
	 *  this div must be square!
	 *
	 * Images' layout:
	 *  all images must be the same width and height and must be squares
	 *  the images are stacked one on top of the other from the bottom in the following order: clock_face hour_hand minute_hand second_hand top_dot
	 *  all arrows must be at 12 o'clock and must be drawn from the center of the square to the top margin
	 *  the last image is top_dot - a dot in the center of the clock that covers the common point of all hands
	 * Sprite image layout:
	 *  to create sprite image, put all images side by side on one line
	 *  put clock_face as first image (on offset 0)
	 *
	 * Usage:
		clockAnalogImageSprite(
			'clock_div_id',
			'./clockspriteanalog.png', // sprite image url
			['400px', '800px', '1200px', '1600px']
		)
	 * where [] has image's offsets in sequence: hour_hand, minute_hand, second_hand, clock_top
	 *  clock face has always offset 0
	 * you can also disable animations - add false after last argument
	 */

	var clockContainer=document.getElementById(id);

	var date=new Date();
	var dateSeconds=date.getSeconds()*6;
	var dateMinutes=date.getMinutes()*6;
	var dateHours=(date.getHours()%12)*30;

	if(dateSeconds%360 === 0) loops[0]++;
	if(dateMinutes%360 === 0) loops[1]++;
	if(dateHours%360 === 0) loops[2]++;

	dateSeconds+=loops[0]*360;
	dateMinutes+=loops[1]*360;
	dateHours+=loops[2]*360;

	if(setup)
	{
		clockContainer.innerHTML=' \
			<div style="position: relative; border-radius: 50%; width: 100%; height: 100%;"> \
				<div style="position: absolute; background-image: url('+spritePic+'); width: 100%; height: 100%;"></div> \
				<div style="transform: rotate('+dateHours+'deg); position: absolute; background-image: url('+spritePic+'); background-position-x: -'+picsOffsets[0]+'; width: 100%; height: 100%;"></div> \
				<div style="transform: rotate('+dateMinutes+'deg); position: absolute; background-image: url('+spritePic+'); background-position-x: -'+picsOffsets[1]+'; width: 100%; height: 100%;"></div> \
				<div style="transform: rotate('+dateSeconds+'deg); position: absolute; background-image: url('+spritePic+'); background-position-x: -'+picsOffsets[2]+'; width: 100%; height: 100%;"></div> \
				<div style="position: absolute; background-image: url('+spritePic+'); background-position-x: -'+picsOffsets[3]+'; width: 100%; height: 100%;"></div> \
			</div> \
		';

		if(animate)
		{
			var handIds=[3, 2, 1];
			for(i in handIds)
				clockContainer.children[0].children[handIds[i]].style.transition='transform 0.2s cubic-bezier(.4, 2.08, .55, .44)';
		}
	}

	var clockRoot=clockContainer.children[0].children;
	clockRoot[3].style.transform='rotate('+dateSeconds+'deg)';
	clockRoot[2].style.transform='rotate('+dateMinutes+'deg)';
	clockRoot[1].style.transform='rotate('+dateHours+'deg)';

	setTimeout('clockAnalogImageSprite("'+id+'", "'+spritePic+'", '+JSON.stringify(picsOffsets)+', false, false, '+JSON.stringify(loops)+')', 1000);
}