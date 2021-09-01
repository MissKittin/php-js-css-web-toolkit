/*
 * Time period checking library
 * from simpleblog project
 * javascript version
 */

function checkDate(startDay, startMonth, endDay, endMonth)
{
	// Check time if is between DD.MM - DD.MM (or D.M - D.M)
	// example usage: checkDate(20,04, 27,08)
	// example usage: checkDate(24,06, 14,02)

	'use strict';

	var currentDate=new Date().getTime()/1000;
	var thisYear=new Date().getFullYear();

	if(startMonth < endMonth) // in the same year
		var newYear=thisYear;
	else // between new year
	{
		if((currentDate <= new Date(thisYear + '.12.31').getTime()) && (new Date().getMonth() < 10))
			var newYear=thisYear+1; // old year
		else
		{
			// new year
			var newYear=thisYear;
			thisYear=thisYear-1;
		}
	}

	var startDate=new Date(thisYear + '.' + startMonth + '.' + startDay).getTime() / 1000;
	var endDate=new Date(newYear + '.' + endMonth + '.' + endDay).getTime() / 1000;

	if((currentDate >= startDate) && (currentDate <= endDate))
		return true;

	return false;
}
function checkEaster(easterDays)
{
	// checkDate extension for easter
	// example usage: checkEaster(49)
	// checkDate() required

	'use strict';

	var calculateEaster=function()
	{
		var thisYear=new Date().getFullYear(); // cache

		// constants
		if((thisYear >= 1900) && (thisYear <= 2099))
		{
			var tabA=24; var tabB=5;
		}
		else if((thisYear >= 2100) && (thisYear <= 2199))
		{
			var tabA=24; var tabB=6;
		}
		else if((thisYear >= 2200) && (thisYear <= 2299))
		{
			var tabA=25; var tabB=0;
		}
		else if((thisYear >= 2300) && (thisYear <= 2399))
		{
			var tabA=26; var tabB=1;
		}
		else if((thisYear >= 2400) && (thisYear <= 2499))
		{
			var tabA=25; var tabB=1;
		}
		else
			return false;

		var a=thisYear % 19;
		var b=thisYear % 4;
		var c=thisYear % 7;
		var d=(a * 19 + 24) % 30;
		var e=((2 * b)+(4 * c)+(6 * d)+5) % 7;
		var easterDay=22 + d + e; var easterMonth=3;

		// correct start date
		while(easterDay > 31)
		{
			easterDay=easterDay - 31;
			easterMonth++;
		}

		var easter=[easterDay, easterMonth];
		return easter;
	};

	// calculate end date
	var easterStart=calculateEaster(); if(easterStart == false) return false;
	var easterEndDay=easterStart[0]+easterDays; var easterEndMonth=easterStart[1];

	// correct end date
	while(easterEndDay > 30)
	{
		if(easterEndMonth%2 == 0)
			easterEndDay=easterEndDay-30;
		else
			easterEndDay=easterEndDay-31;
		easterEndMonth++;
	}

	return checkDate(easterStart[0],easterStart[1], easterEndDay, easterEndMonth);
}