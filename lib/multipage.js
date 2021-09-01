/*
 * multipage.js
 * Element switching library
 *
 * Usage:
 *  multipage('app', 'home', 'not_found')
 *  switchElement('switch_container', 'element_id')
 */

function multipage(id, main, notFound)
{
	/*
	 * multipage.js
	 * Put several pages in one HTML file
	 *
	 * HTML template:
	 *  create app main div in <body> with style="display: none;" [1]
	 *  create divs in the app's main div - these will be separate pages [2]
	 *  switch page link has #! prefix, eg: <a href="#!page1">Switch to page no1</a>
	 *   href="#!" will switch the app to the main page [B]
	 *   href="#element_id" works, but goes to the main [B] on page refresh
	 *   all links without #! will be treated as normal links (eg /anotherpage or http://example.com)
	 *  you can add <noscript> tag
	 *  start application
	 *
	 * Starting application:
	 *  add <script> to the end of the <body> (or put this in the DOMContentLoaded event)
	 *   multipage('app', 'home', 'not_found')
	 *  where:
	 *   'app' is application container id [1]
	 *   'home' is default div id in the application container [2] [B]
	 *   'not_found' is 404 div id in the application container (can be the same as second arg) [2]
	 */

	'use strict';

	var app=document.getElementById(id);

	var appPages=app.children;
	for(var i=0; i<appPages.length; i++)
		appPages[i].style.display='none';

	if(window.location.hash)
	{
		if(window.location.hash.substring(2) === '')
			app.querySelector('#'+main).style.display='block';
		else
		{
			var appLocation=app.querySelector('#'+window.location.hash.substring(2));
			if(appLocation)
				appLocation.style.display='block'; // selected
			else
				app.querySelector('#'+notFound).style.display='block';
		}
	}
	else
		app.querySelector('#'+main).style.display='block';

	// link click
	window.onhashchange=function(a, b=id, c=main, d=notFound) // a is dummy arg
	{
		var app=document.getElementById(b);

		if((window.location.hash.substring(0, 1) === '#') && (window.location.hash.substring(0, 2) !== '#!')) // go to #element_id
			app.querySelector(window.location.hash).scrollIntoView(); // #element_id
		else
		{
			var i;

			var appPages=app.children;
			for(i=0; i<appPages.length; i++)
				appPages[i].style.display='none';

			if(window.location.hash)
			{
				for(i=0; i<appPages.length; i++)
					appPages[i].style.display='none';

				if(window.location.hash.substring(2) === '')
					app.querySelector('#'+c).style.display='block'; // main
				else
				{
					var appLocation=app.querySelector('#'+window.location.hash.substring(2));
					if(appLocation)
						appLocation.style.display='block'; // selected
					else
						app.querySelector('#'+d).style.display='block'; // notFound
				}
			}
			else
				app.querySelector('#'+c).style.display='block'; // main
		}
	};

	app.style.display='block'; // start app
}
function switchElement(mainId, selectedId, styleDisplay='block')
{
	/*
	 * multipage.js
	 * Lite version - element switcher
	 *
	 * HTML template:
	 *  create container with id (eg. switch_container) with elements for switching
	 *  create elements inside switch_container with style="display: none;", one without
	 *   assign ids for all elements
	 *
	 * Linking:
	 *  <a onclick="switchElement('switch_container', 'element_id');">Switch to element_id</a>
	 *  note: link without href is not styled
	 */

	var mainElement=document.getElementById(mainId);

	var allElements=mainElement.children;
	for(var i=0; i<allElements.length; i++)
		allElements[i].style.display='none';

	mainElement.querySelector('#'+selectedId).style.display=styleDisplay;
}