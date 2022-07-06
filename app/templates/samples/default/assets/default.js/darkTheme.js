if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
{
	console.log('Dark theme enabled');

	if(getCookie('app_dark_theme') !== 'true')
	{
		document.cookie='app_dark_theme=true';
		console.log('app_dark_theme cookie saved')
	}

	getCSS('/assets/default_dark.css');
	function setBodyOpacity()
	{
		if(getComputedStyle(document.body).backgroundColor === 'rgb(0, 0, 0)')
		{
			document.body.style.opacity=1;
			console.log(' /assets/default_dark.css applied');

			return 0;
		}

		setTimeout(setBodyOpacity, 30);
	}
	setBodyOpacity();
}
else
	console.log('Dark theme disabled');