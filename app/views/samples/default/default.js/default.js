console.log('Testing checkDate()');
if(checkDate('01','02', '25','06'))
	console.log(' Current date is between 01.02 and 25.06');
else
	console.log(' Current date is not between 01.02 and 25.06');
if(checkDate('27','06', '30','01'))
	console.log(' Current date is between 27.06 and 30.01');
else
	console.log(' Current date is not between 27.06 and 30.01');

console.log('Testing checkEaster()');
if(checkEaster(49))
	console.log(' Now is easter');
else
	console.log(' Today is not easter');