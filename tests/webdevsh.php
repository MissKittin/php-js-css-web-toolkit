<?php
	/*
	 * webdevsh.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  curl extension is required
	 */

	if(!extension_loaded('curl'))
	{
		echo 'curl extension is not loaded'.PHP_EOL;
		exit(1);
	}

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing webdevsh_css_minifier';
		if(webdevsh_css_minifier('
			body {
				background-color: #aaaaaa;
				color: #000000;
				opacity: 0;
			}
			a, a:hover, a:visited {
				color: #aa0000;
				text-decoration: none;
			}
			a:after {
				content: " \2500\25B6";
			}
			input {
				border-radius: 10px;
				margin: 5px;
				background-color: #cccccc;
			}
		') === 'body{background-color:#aaa;color:#000;opacity:0}a,a:hover,a:visited{color:#a00;text-decoration:none}a:after{content:" \2500\25B6"}input{border-radius:10px;margin:5px;background-color:#ccc}')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing webdevsh_js_minifier';
		if(webdevsh_js_minifier('
						document.addEventListener(\'DOMContentLoaded\', function(){
				function getCookie(name)
			{
				// Read cookie value

				\'use strict\';

				var value=\'; \'+document.cookie;
				var parts=value.split(\'; \' + name + \'=\');
				if(parts.length === 2)
				return parts.pop().split(\';\').shift();
			}	/*
			 * Easily download and apply assets at run time
			 * For CSS and JS files
			 */

			function getCSS(file)
			{
				/*
				 * Download and apply css at run time
				 * Usage:
				 *  getCSS(\'/style.css\')
				 */

				\'use strict\';

				var link=document.createElement(\'link\');
				link.rel=\'stylesheet\';
				link.type=\'text/css\';
				link.href=location.origin + file;
				document.getElementsByTagName(\'head\')[0].appendChild(link);
			}
			function getJS(file)
			{
				/*
				 * Download and run js at run time
				 * Usage:
				 *  getJS(\'/script.css\')
				 */

				\'use strict\';

				var script=document.createElement(\'script\');
				script.src=location.origin + file;
				document.getElementsByTagName(\'head\')[0].appendChild(script);
			}	if(window.matchMedia && window.matchMedia(\'(prefers-color-scheme: dark)\').matches)
			{
				console.log(\'Dark theme enabled\');

				if(getCookie(\'app_dark_theme\') !== \'true\')
				{
					document.cookie=\'app_dark_theme=true\';
					console.log(\'app_dark_theme cookie saved\')
				}

				getCSS(\'/assets/default_dark.css\');
				function setBodyOpacity()
				{
					if(getComputedStyle(document.body).backgroundColor === \'rgb(0, 0, 0)\')
					{
						document.body.style.opacity=1;
						console.log(\' /assets/default_dark.css applied\');

						return 0;
					}

					setTimeout(setBodyOpacity, 30);
				}
				setBodyOpacity();
			}
			else
				console.log(\'Dark theme disabled\');
				/*
			 * Time period checking library
			 * from Simpleblog project
			 *
			 * Warning:
			 *  checkEaster() requires checkDate()
			 *
			 * Functions:
			 *  checkDate -> check time if is between DD.MM - DD.MM (or D.M - D.M)
			 *  checkEaster -> easter extension for checkDate
			 *
			 * Usage:
			 *  checkDate(20,04, 27,08)
			 *  checkDate(24,06, 14,02)
			 *  checkEaster(49)
			 */

			function checkDate(startDay, startMonth, endDay, endMonth)
			{
				\'use strict\';

				var currentDate=new Date(new Date().toISOString().slice(0, 10)).getTime();
				var currentYear=new Date().getFullYear();

				var calculateBetweenYears=function(
					startMonth,
					currentDate,
					currentYear,
					endMonth,
					endDay,
					startDay
				){
					var currentMonth=new Date().getMonth()+1; // !!! new Date().getMonth()+1 === date(\'m\')

					if(currentMonth == startMonth)
					{
						if(new Date().getDate() >= startDay)
							return true;
					}
					else if(currentMonth < startMonth)
					{
						if(currentDate <= Date.parse(currentYear+\'-\'+endMonth+\'-\'+endDay))
							return true;
					}
					else
						if(currentDate >= Date.parse(currentYear+\'-\'+startMonth+\'-\'+startDay))
							return true;

					return false;
				};

				if(startMonth <= endMonth)
				{
					if((startMonth === endMonth) && (startDay > endDay))
						return calculateBetweenYears(
							startMonth,
							currentDate,
							currentYear,
							endMonth,
							endDay,
							startDay
						);

					if
					(
						(currentDate >= Date.parse(currentYear+\'-\'+startMonth+\'-\'+startDay))
						&&
						(currentDate <= Date.parse(currentYear+\'-\'+endMonth+\'-\'+endDay))
					)
						return true;
				}
				else
					return calculateBetweenYears(
						startMonth,
						currentDate,
						currentYear,
						endMonth,
						endDay,
						startDay
					);

				return false;
			}
			function checkEaster(easterDays)
			{
				\'use strict\';

				var calculateEaster=function(thisYear)
				{
					if((thisYear >= 1900) && (thisYear <= 2099))
						var tab=[24, 5];
					else if((thisYear >= 2100) && (thisYear <= 2199))
						var tab=[24, 6];
					else if((thisYear >= 2200) && (thisYear <= 2299))
						var tab=[25, 0];
					else if((thisYear >= 2300) && (thisYear <= 2399))
						var tab=[26, 1];
					else if((thisYear >= 2400) && (thisYear <= 2499))
						var tab=[25, 1];
					else
						return false;

					var a=thisYear%19;
					var b=thisYear%4;
					var c=thisYear%7;
					var d=(a*19+tab[0])%30;
					var e=((2*b)+(4*c)+(6*d)+tab[1])%7;
					var easterDay=22+d+e;
					var easterMonth=3;

					while(easterDay > 31)
					{
						easterDay=easterDay-31;
						easterMonth++;
					}

					var easter=[easterDay, easterMonth];
					return easter;
				};

				var easterStart=calculateEaster(new Date().getFullYear());
				if(easterStart === false)
					return false;

				var easterEndDay=easterStart[0]+easterDays;
				var easterEndMonth=easterStart[1];

				while(easterEndDay > 30)
				{
					if(easterEndMonth%2 === 0)
						easterEndDay-=30;
					else
						easterEndDay-=31;

					easterEndMonth++;
				}

				return checkDate(
					easterStart[0],
					easterStart[1],
					easterEndDay,
					easterEndMonth
				);
			}	console.log(\'Testing checkDate()\');
			if(checkDate(\'01\',\'02\', \'25\',\'06\'))
				console.log(\' Current date is between 01.02 and 25.06\');
			else
				console.log(\' Current date is not between 01.02 and 25.06\');
			if(checkDate(\'27\',\'06\', \'30\',\'01\'))
				console.log(\' Current date is between 27.06 and 30.01\');
			else
				console.log(\' Current date is not between 27.06 and 30.01\');

			console.log(\'Testing checkEaster()\');
			if(checkEaster(49))
				console.log(\' Now is easter\');
			else
				console.log(\' Today is not easter\');});
		') === 'document.addEventListener("DOMContentLoaded",function(){if(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches){var b,a;function d(){if("rgb(0, 0, 0)"===getComputedStyle(document.body).backgroundColor)return document.body.style.opacity=1,console.log(" /assets/default_dark.css applied"),0;setTimeout(d,30)}console.log("Dark theme enabled"),"true"!==function(b){"use strict";var a=("; "+document.cookie).split("; "+b+"=");if(2===a.length)return a.pop().split(";").shift()}("app_dark_theme")&&(document.cookie="app_dark_theme=true",console.log("app_dark_theme cookie saved")),b="/assets/default_dark.css",(a=document.createElement("link")).rel="stylesheet",a.type="text/css",a.href=location.origin+b,document.getElementsByTagName("head")[0].appendChild(a),d()}else console.log("Dark theme disabled");function c(c,a,d,b){"use strict";var e=new Date(new Date().toISOString().slice(0,10)).getTime(),f=new Date().getFullYear();return!(a<=b)||a===b&&c>d?function(a,b,c,f,g,d){var e=new Date().getMonth()+1;if(e==a){if(new Date().getDate()>=d)return!0}else if(e<a){if(b<=Date.parse(c+"-"+f+"-"+g))return!0}else if(b>=Date.parse(c+"-"+a+"-"+d))return!0;return!1}(a,e,f,b,d,c):!!(e>=Date.parse(f+"-"+a+"-"+c)&&e<=Date.parse(f+"-"+b+"-"+d))}console.log("Testing checkDate()"),c("01","02","25","06")?console.log(" Current date is between 01.02 and 25.06"):console.log(" Current date is not between 01.02 and 25.06"),c("27","06","30","01")?console.log(" Current date is between 27.06 and 30.01"):console.log(" Current date is not between 27.06 and 30.01"),console.log("Testing checkEaster()"),function(e){"use strict";var a=function(a){if(a>=1900&&a<=2099)var b=[24,5];else if(a>=2100&&a<=2199)var b=[24,6];else if(a>=2200&&a<=2299)var b=[25,0];else if(a>=2300&&a<=2399)var b=[26,1];else if(!(a>=2400)||!(a<=2499))return!1;else var b=[25,1];for(var f=a%19,g=a%4,h=a%7,d=(19*f+b[0])%30,i=(2*g+4*h+6*d+b[1])%7,c=22+d+i,e=3;c>31;)c-=31,e++;return[c,e]}(new Date().getFullYear());if(!1===a)return!1;for(var b=a[0]+49,d=a[1];b>30;)d%2==0?b-=30:b-=31,d++;return c(a[0],a[1],b,d)}(49)?console.log(" Now is easter"):console.log(" Today is not easter")})')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>