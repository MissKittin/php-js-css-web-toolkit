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
	'use strict';

	var currentDate=new Date(
		new Date(
			Date.now()-(new Date()).getTimezoneOffset()*60000
		)
		.toISOString()
		.slice(0, 10)
	).getTime();

	var currentYear=new Date().getFullYear();

	var calculateBetweenYears=function(
		startMonth,
		currentDate,
		currentYear,
		endMonth,
		endDay,
		startDay
	){
		var currentMonth=new Date().getMonth()+1; // !!! new Date().getMonth()+1 === date('m')

		if(currentMonth == startMonth)
		{
			if(new Date().getDate() >= startDay)
				return true;

			return false;
		}

		if(currentMonth < startMonth)
		{
			if(currentDate <= Date.parse(currentYear+'-'+endMonth+'-'+endDay))
				return true;

			return false;
		}

		if(currentDate >= Date.parse(currentYear+'-'+startMonth+'-'+startDay))
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

		if(
			(currentDate >= Date.parse(currentYear+'-'+startMonth+'-'+startDay)) &&
			(currentDate <= Date.parse(currentYear+'-'+endMonth+'-'+endDay))
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
	'use strict';

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
}