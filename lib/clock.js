/*
 * Clock library
 *
 * Warning: all sprite images must be on one line next to each other
 *  offset 0 starts with the first left horizontal line
 *
 * Functions:
 *  clockDigital(document.getElementById('clock_div_id'), ':')
	clockDigitalImage(
		document.getElementById('clock_div_id'),
		[
			'./0.png',
			'./1.png',
			'./2.png',
			'./3.png',
			'./4.png',
			'./5.png',
			'./6.png',
			'./7.png',
			'./8.png',
			'./9.png'
		],
		'./separator.png'
	)
	clockDigitalImageSprite(
		document.getElementById('clock_div_id'),
		'./clock.png', // sprite image url
		'24px', // clock's height (the same as sprite image url)
		'16px', // clock's digit width
		'8px', // clock's separator width
		['0px', '16px', '32px', '48px', '64px', '80px', '96px', '112px', '128px', '144px'], // digit's offsets from 0 to 9
		'160px' // separator offset
	)
 *  clockAnalogCSS('clock_div_id', '#000000')
	clockAnalogImage(
		document.getElementById('clock_div_id'),
		[
			'./clock-face.png',
			'./hour-hand.png',
			'./minute-hand.png',
			'./second-hand.png',
			'./top-image.png'
		]
	)
	clockAnalogImageSprite(
		document.getElementById('clock_div_id'),
		'./clockspriteanalog.png', // sprite image url
		['400px', '800px', '1200px', '1600px'] // offsets: hour-hand minute-hand second-hand top-image
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
	 * Usage:
	 *  clockDigital(document.getElementById('clock_div_id'), ':');
	 *   where ':' is seprator
	 */

	'use strict';

	var currentTime=new Date();
	var currentHour=currentTime.getHours().toString();

	if(currentHour.length === 1)
		currentHour='0'+currentHour;

	var currentMinutes=currentTime.getMinutes().toString();

	if(currentMinutes.length === 1)
		currentMinutes='0'+currentMinutes;

	var currentSeconds=currentTime.getSeconds().toString();

	if(currentSeconds.length === 1)
		currentSeconds='0'+currentSeconds;

	id.innerHTML=currentHour+separator+currentMinutes+separator+currentSeconds;

	setTimeout(function(){
		clockDigital(
			id,
			separator
		);
	}, 1000);
}
function clockDigitalImage(
	id,
	pics, separator,
	setup=true
){
	/*
	 * Render digital clock in div from images
	 *
	 * Warning:
	 *  if browser cache is disabled
	 *  it may generate unnecessary data usage
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> and style it
	 *
	 * Usage:
		clockDigitalImage(
			document.getElementById('clock_div_id'),
			[
				'./0.png',
				'./1.png',
				'./2.png',
				'./3.png',
				'./4.png',
				'./5.png',
				'./6.png',
				'./7.png',
				'./8.png',
				'./9.png'
			],
			'./separator.png'
		);
	 * where [] has digit's urls
	 */

	var i; var x;
	var currentTime=new Date();
	var currentHour=currentTime.getHours().toString();

	if(currentHour.length === 1)
		currentHour='0'+currentHour;

	var currentMinutes=currentTime.getMinutes().toString();

	if(currentMinutes.length === 1)
		currentMinutes='0'+currentMinutes;

	var currentSeconds=currentTime.getSeconds().toString();

	if(currentSeconds.length === 1)
		currentSeconds='0'+currentSeconds;

	if(setup)
	{
		var output='';

		for(i=0; i<currentHour.length; i++)
			output+='<img src="'+pics[currentHour[i]]+'" alt="clock-'+currentHour[i]+'">';

		output+='<img src="'+separator+'" alt="separator">';

		for(i=0; i<currentMinutes.length; i++)
			output+='<img src="'+pics[currentMinutes[i]]+'" alt="clock-'+currentMinutes[i]+'">';

		output+='<img src="'+separator+'" alt="separator">';

		for(i=0; i<currentSeconds.length; i++)
			output+='<img src="'+pics[currentSeconds[i]]+'" alt="clock-'+currentSeconds[i]+'">';

		id.innerHTML=output;
	}

	for(
		i=0, x=0;
		i<currentHour.length;
		i++, x++
	)
		if(id.children[x].getAttribute('src') !== pics[currentHour[i]])
			id.children[x].src=pics[currentHour[i]];

	for(
		i=0, x=3;
		i<currentMinutes.length;
		i++, x++
	)
		if(id.children[x].getAttribute('src') !== pics[currentMinutes[i]])
			id.children[x].src=pics[currentMinutes[i]];

	for(
		i=0, x=6;
		i<currentSeconds.length;
		i++, x++
	)
		if(id.children[x].getAttribute('src') !== pics[currentSeconds[i]])
			id.children[x].src=pics[currentSeconds[i]];

	setTimeout(function(){
		clockDigitalImage(
			id,
			pics,
			separator,
			false
		);
	}, 1000);
}
function clockDigitalImageSprite(
	clockContainer,
	spritePic,
	clockHeight, digitWidth, separatorWidth,
	picsOffsets, separatorOffset,
	setup=true
){
	/*
	 * Render digital clock in div from sprite image
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> and style it
	 *
	 * Sprite image layout:
	 *  put all images side by side on one line from 0 to 9
	 *  all digits and separator must be of the same height
	 *  all digits must be of the same width (except separator)
	 *
	 * Usage:
		clockDigitalImageSprite(
			document.getElementById('clock_div_id'),
			'./clock.png', // sprite image url
			'24px', // clock's height (the same as sprite image url)
			'16px', // clock's digit width
			'8px', // clock's separator width
			['0px', '16px', '32px', '48px', '64px', '80px', '96px', '112px', '128px', '144px'], // digit's offsets from 0 to 9
			'160px' // separator offset (at the end)
		);
	 * where digit's offsets are in sequence: 0, 1, 2, 3, 4, 5, 6, 8, 9
	 */

	if(setup)
	{
		clockContainer.innerHTML=' \
			<div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
			</div> \
		';

		clockContainer.children[0].style.display='flex';
		clockContainer.children[0].style.height=clockHeight;

		for(var i=0; i<8; i++)
		{
			clockContainer.children[0].children[i].style.backgroundImage='url('+spritePic+')';

			if(
				(i === 2) ||
				(i === 5)
			){
				clockContainer.children[0].children[i].style.width=separatorWidth;
				clockContainer.children[0].children[i].style.backgroundPositionX='-'+separatorOffset;

				continue;
			}

			clockContainer.children[0].children[i].style.width=digitWidth;
		}
	}

	var currentTime=new Date();
	var currentHour=currentTime.getHours().toString();

	if(currentHour.length === 1)
		currentHour='0'+currentHour;

	var currentMinutes=currentTime.getMinutes().toString();

	if(currentMinutes.length === 1)
		currentMinutes='0'+currentMinutes;

	var currentSeconds=currentTime.getSeconds().toString();

	if(currentSeconds.length === 1)
		currentSeconds='0'+currentSeconds;

	var digits=clockContainer.children[0].children;
	digits[0].style.backgroundPositionX='-'+picsOffsets[currentHour[0]];
	digits[1].style.backgroundPositionX='-'+picsOffsets[currentHour[1]];
	digits[3].style.backgroundPositionX='-'+picsOffsets[currentMinutes[0]];
	digits[4].style.backgroundPositionX='-'+picsOffsets[currentMinutes[1]];
	digits[6].style.backgroundPositionX='-'+picsOffsets[currentSeconds[0]];
	digits[7].style.backgroundPositionX='-'+picsOffsets[currentSeconds[1]];

	setTimeout(function(){
		clockDigitalImageSprite(
			clockContainer,
			spritePic,
			clockHeight,
			digitWidth,
			separatorWidth,
			picsOffsets,
			separatorOffset,
			false
		);
	}, 1000);
}
function clockAnalogCSS(
	clockContainer, color,
	animate=true, setup=true,
	loops=[0, 0, 0]
){
	/*
	 * Draw analog clock in CSS
	 *
	 * HTML template:
	 *  create <div id="clock_div_id"></div> with appropriate styles (width, height)
	 *  this div must be square!
	 *
	 * Usage:
	 *  clockAnalogCSS(document.getElementById('clock_div_id'), '#000000');
	 *   where #000000 is clock color
	 *   if you want background-color, define it for the #clock_div_id
	 *   you can also disable animations - add false after last argument
	 */

	var i; var x;
	var date=new Date();
	var dateSeconds=date.getSeconds()*6;
	var dateMinutes=date.getMinutes()*6;
	var dateHours=(date.getHours()%12)*30;
	var dateSecondsMod=dateSeconds%360;
	var dateMinutesMod=dateMinutes%360;

	if(dateSecondsMod === 0)
		loops[0]++;

	if(
		(dateMinutesMod === 0) &&
		(dateSecondsMod === 0)
	)
		loops[1]++;

	if(
		(dateHours%360 === 0) &&
		(dateMinutesMod === 0) &&
		(dateSecondsMod === 0)
	)
		loops[2]++;

	dateSeconds+=loops[0]*360;
	dateMinutes+=loops[1]*360;
	dateHours+=loops[2]*360;

	if(setup)
	{
		var style=function(styles, x=-1, y=-1)
		{
			if(x === -1)
			{
				for(var [styleParam, styleValue] of Object.entries(styles))
					clockContainer.children[0].style[styleParam]=styleValue;

				return;
			}

			if(y === -1)
			{
				for(var [styleParam, styleValue] of Object.entries(styles))
					clockContainer.children[0].children[x].style[styleParam]=styleValue;

				return;
			}

			for(var [styleParam, styleValue] of Object.entries(styles))
				clockContainer.children[0].children[x].children[y].style[styleParam]=styleValue;
		};

		// Big 12 9 3 6
			var clockContainerContent=' \
				<div> \
					<div></div> \
					<div></div> \
					<div></div> \
					<div></div> \
				</div> \
			';

		// Medium 12 9 3 6 Deg 30 60
			clockContainerContent+=' \
				<div> \
					<div></div> \
					<div></div> \
					<div></div> \
					<div></div> \
				</div> \
				<div> \
					<div></div> \
					<div></div> \
					<div></div> \
					<div></div> \
				</div> \
			';

		for(i=6; i<90; i=i+6) // Small 12 9 3 6 Deg 6 12 18 24 36 42 48 54 66 72 78 84
			if(
				(i !== 30) &&
				(i !== 60)
			)
				clockContainerContent+=' \
					<div> \
						<div></div> \
						<div></div> \
						<div></div> \
						<div></div> \
					</div> \
				';

		// clock hands and center dot
			clockContainerContent+=' \
				<div> \
					<div></div> \
				</div> \
				<div> \
					<div></div> \
				</div> \
				<div> \
					<div></div> \
				</div> \
				<div></div> \
			';

		clockContainer.innerHTML='<div>'+clockContainerContent+'</div>';
		style({
			'position': 'relative',
			'borderRadius': '50%',
			'width': '100%',
			'height': '100%'
		});

		// Big 12 9 3 6
			style({
				'position': 'absolute',
				'width': '100%',
				'height': '100%'
			}, 0);
			style({
				'position': 'absolute',
				'left': '49.5%',
				'width': '1%',
				'height': '7.5%',
				'backgroundColor': color
			}, 0, 0);
			style({
				'position': 'absolute',
				'top': '49.5%',
				'width': '7.5%',
				'height': '1%',
				'backgroundColor': color
			}, 0, 1);
			style({
				'position': 'absolute',
				'top': '49.5%',
				'left': '92.5%',
				'width': '7.5%',
				'height': '1%',
				'backgroundColor': color
			}, 0, 2);
			style({
				'position': 'absolute',
				'top': '92.5%',
				'left': '49.5%',
				'width': '1%',
				'height': '7.5%',
				'backgroundColor': color
			}, 0, 3);

		// Medium 12 9 3 6 Deg 30 60
		for(
			i=30, x=1;
			i<90;
			i=i+30, x++
		){
			style({
				'transform': 'rotate('+i+'deg)',
				'position': 'absolute',
				'width': '100%',
				'height': '100%'
			}, x);
			style({
				'position': 'absolute',
				'left': '50%',
				'width': '0.5%',
				'height': '5%',
				'backgroundColor': color
			}, x, 0);
			style({
				'position': 'absolute',
				'top': '50%',
				'width': '5%',
				'height': '0.5%',
				'backgroundColor': color
			}, x, 1);
			style({
				'position': 'absolute',
				'top': '50%',
				'left': '95%',
				'width': '5%',
				'height': '0.5%',
				'backgroundColor': color
			}, x, 2);
			style({
				'position': 'absolute',
				'top': '95%',
				'left': '50%',
				'width': '0.5%',
				'height': '5%',
				'backgroundColor': color
			}, x, 3);
		}

		for(i=6; i<90; i=i+6) // Small 12 9 3 6 Deg 6 12 18 24 36 42 48 54 66 72 78 84
			if(
				(i !== 30) &&
				(i !== 60)
			){
				style({
					'transform': 'rotate('+i+'deg)',
					'position': 'absolute',
					'width': '100%',
					'height': '100%'
				}, x);
				style({
					'position': 'absolute',
					'left': '50%',
					'width': '0.5%',
					'height': '2.5%',
					'backgroundColor': color
				}, x, 0);
				style({
					'position': 'absolute',
					'top': '50%',
					'width': '2.5%',
					'height': '0.5%',
					'backgroundColor': color
				}, x, 1);
				style({
					'position': 'absolute',
					'top': '50%',
					'left': '97%',
					'width': '2.5%',
					'height': '0.5%',
					'backgroundColor': color
				}, x, 2);
				style({
					'position': 'absolute',
					'top': '97%',
					'left': '50%',
					'width': '0.5%',
					'height': '2.5%',
					'backgroundColor': color
				}, x, 3);

				x++;
			}

			// clock hands and center dot
				style({
					'transform': 'rotate('+dateHours+'deg)',
					'position': 'absolute',
					'width': '100%',
					'height': '100%'
				}, x);
				style({
					'position': 'absolute',
					'top': '20%',
					'left': '48.5%',
					'width': '3.5%',
					'height': '30%',
					'backgroundColor': color
				}, x, 0);

				style({
					'transform': 'rotate('+dateMinutes+'deg)',
					'position': 'absolute',
					'width': '100%',
					'height': '100%'
				}, ++x);
				style({
					'position': 'absolute',
					'top': '8%',
					'left': '49%',
					'width': '2%',
					'height': '49.5%',
					'backgroundColor': color
				}, x, 0);

				style({
					'transform': 'rotate('+dateSeconds+'deg)',
					'position': 'absolute',
					'width': '100%',
					'height': '100%'
				}, ++x);
				style({
					'position': 'absolute',
					'top': '5%',
					'left': '49.5%',
					'width': '1%',
					'height': '55%',
					'backgroundColor': color
				}, x, 0);

				style({
					'position': 'absolute',
					'top': '44.75%',
					'left': '44.75%',
					'borderRadius': '50%',
					'width': '10.5%',
					'height': '10.5%',
					'backgroundColor': color
				}, ++x);

		if(animate)
		{
			var handIds=[17, 16, 15];

			for(i in handIds)
				style({
					'transition': 'transform 0.2s cubic-bezier(.4, 2.08, .55, .44)'
				}, handIds[i]);
		}
	}

	var clockRoot=clockContainer.children[0];
	clockRoot.children[17].style.transform='rotate('+dateSeconds+'deg)';
	clockRoot.children[16].style.transform='rotate('+dateMinutes+'deg)';
	clockRoot.children[15].style.transform='rotate('+dateHours+'deg)';

	setTimeout(function(){
		clockAnalogCSS(
			clockContainer, color,
			false, false,
			loops
		);
	}, 1000);
}
function clockAnalogImage(
	clockContainer, pics,
	animate=true, setup=true,
	loops=[0, 0, 0]
){
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
			document.getElementById('clock_div_id'),
			[
				'./clock-face.png',
				'./hour-hand.png',
				'./minute-hand.png',
				'./second-hand.png',
				'./top-image.png'
			]
		);
	 * where [] is an array with the urls
	 * you can also disable animations - add false after last argument
	 */

	var date=new Date();
	var dateSeconds=date.getSeconds()*6;
	var dateMinutes=date.getMinutes()*6;
	var dateHours=(date.getHours()%12)*30;
	var dateSecondsMod=dateSeconds%360;
	var dateMinutesMod=dateMinutes%360;

	if(dateSecondsMod === 0)
		loops[0]++;

	if(
		(dateMinutesMod === 0) &&
		(dateSecondsMod === 0)
	)
		loops[1]++;

	if(
		(dateHours%360 === 0) &&
		(dateMinutesMod === 0) &&
		(dateSecondsMod === 0)
	)
		loops[2]++;

	dateSeconds+=loops[0]*360;
	dateMinutes+=loops[1]*360;
	dateHours+=loops[2]*360;

	if(setup)
	{
		var style=function(styles, x=-1)
		{
			if(x === -1)
			{
				for(var [styleParam, styleValue] of Object.entries(styles))
					clockContainer.children[0].style[styleParam]=styleValue;

				return;
			}

			for(var [styleParam, styleValue] of Object.entries(styles))
				clockContainer.children[0].children[x].style[styleParam]=styleValue;
		};

		clockContainer.style.position='relative';

		clockContainer.innerHTML=' \
			<div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
			</div> \
		';

		style({
			'backgroundImage': 'url('+pics[0]+')',
			'backgroundRepeat': 'no-repeat',
			'position': 'absolute',
			'width': '100%',
			'height': '100%'
		});
		style({
			'backgroundImage': 'url('+pics[1]+')',
			'backgroundRepeat': 'no-repeat',
			'transform': 'rotate('+dateHours+'deg)',
			'position': 'absolute',
			'width': '100%',
			'height': '100%'
		}, 0);
		style({
			'backgroundImage': 'url('+pics[2]+')',
			'backgroundRepeat': 'no-repeat',
			'transform': 'rotate('+dateMinutes+'deg)',
			'position': 'absolute',
			'width': '100%',
			'height': '100%'
		}, 1);
		style({
			'backgroundImage': 'url('+pics[3]+')',
			'backgroundRepeat': 'no-repeat',
			'transform': 'rotate('+dateSeconds+'deg)',
			'position': 'absolute',
			'width': '100%',
			'height': '100%'
		}, 2);
		style({
			'backgroundImage': 'url('+pics[4]+')',
			'backgroundRepeat': 'no-repeat',
			'position': 'absolute',
			'width': '100%',
			'height': '100%'
		}, 3);

		if(animate)
		{
			var handIds=[2, 1, 0];

			for(i in handIds)
				style({
					'transition': 'transform 0.2s cubic-bezier(.4, 2.08, .55, .44)'
				}, handIds[i]);
		}
	}

	var clockRoot=clockContainer.children[0];
	clockRoot.children[2].style.transform='rotate('+dateSeconds+'deg)';
	clockRoot.children[1].style.transform='rotate('+dateMinutes+'deg)';
	clockRoot.children[0].style.transform='rotate('+dateHours+'deg)';

	setTimeout(function(){
		clockAnalogImage(
			clockContainer,
			false,
			false,
			false,
			loops
		);
	}, 1000);
}
function clockAnalogImageSprite(
	clockContainer,
	spritePic, picsOffsets,
	animate=true, setup=true,
	loops=[0, 0, 0]
){
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
	 *  to create sprite image, put all images side by side on one line:
	 *   [clock-face hour-hand minute-hand second-hand top-image]
	 *  put clock-face as first image (on offset 0)
	 *
	 * Usage:
		clockAnalogImageSprite(
			document.getElementById('clock_div_id'),
			'./clockspriteanalog.png', // sprite image url
			['400px', '800px', '1200px', '1600px']
		);
	 * where [] has image's offsets in sequence: hour-hand, minute-hand, second-hand, top-image
	 *  clock face has always offset 0
	 * you can also disable animations - add false after last argument
	 */

	var date=new Date();
	var dateSeconds=date.getSeconds()*6;
	var dateMinutes=date.getMinutes()*6;
	var dateHours=(date.getHours()%12)*30;
	var dateSecondsMod=dateSeconds%360;
	var dateMinutesMod=dateMinutes%360;

	if(dateSecondsMod === 0)
		loops[0]++;

	if(
		(dateMinutesMod === 0) &&
		(dateSecondsMod === 0)
	)
		loops[1]++;

	if(
		(dateHours%360 === 0) &&
		(dateMinutesMod === 0) &&
		(dateSecondsMod === 0)
	)
		loops[2]++;

	dateSeconds+=loops[0]*360;
	dateMinutes+=loops[1]*360;
	dateHours+=loops[2]*360;

	if(setup)
	{
		var style=function(styles, x=-1)
		{
			if(x === -1)
			{
				for(var [styleParam, styleValue] of Object.entries(styles))
					clockContainer.children[0].style[styleParam]=styleValue;

				return;
			}

			for(var [styleParam, styleValue] of Object.entries(styles))
				clockContainer.children[0].children[x].style[styleParam]=styleValue;
		};

		clockContainer.innerHTML=' \
			<div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
				<div></div> \
			</div> \
		';

		style({
			'position': 'relative',
			'borderRadius': '50%',
			'width': '100%',
			'height': '100%'
		});
		style({
			'position': 'absolute',
			'backgroundImage': 'url('+spritePic+')',
			'width': '100%',
			'height': '100%'
		}, 0);
		style({
			'transform': 'rotate('+dateHours+'deg)',
			'position': 'absolute',
			'backgroundImage': 'url('+spritePic+')',
			'backgroundPositionX': '-'+picsOffsets[0],
			'width': '100%',
			'height': '100%'
		}, 1);
		style({
			'transform': 'rotate('+dateMinutes+'deg)',
			'position': 'absolute',
			'backgroundImage': 'url('+spritePic+')',
			'backgroundPositionX': '-'+picsOffsets[1],
			'width': '100%',
			'height': '100%'
		}, 2);
		style({
			'transform': 'rotate('+dateSeconds+'deg)',
			'position': 'absolute',
			'backgroundImage': 'url('+spritePic+')',
			'backgroundPositionX': '-'+picsOffsets[2],
			'width': '100%',
			'height': '100%'
		}, 3);
		style({
			'position': 'absolute',
			'backgroundImage': 'url('+spritePic+')',
			'backgroundPositionX': '-'+picsOffsets[3],
			'width': '100%',
			'height': '100%'
		}, 4);

		if(animate)
		{
			var handIds=[3, 2, 1];

			for(i in handIds)
				style({
					'transition': 'transform 0.2s cubic-bezier(.4, 2.08, .55, .44)'
				}, handIds[i]);
		}
	}

	var clockRoot=clockContainer.children[0].children;
	clockRoot[3].style.transform='rotate('+dateSeconds+'deg)';
	clockRoot[2].style.transform='rotate('+dateMinutes+'deg)';
	clockRoot[1].style.transform='rotate('+dateHours+'deg)';

	setTimeout(function(){
		clockAnalogImageSprite(
			clockContainer,
			spritePic,
			picsOffsets,
			false,
			false,
			loops
		);
	}, 1000);
}