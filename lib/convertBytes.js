function convertBytes(input)
{
	// Automatically convert input number to human-readable form

	'use strict';

	if(input === '') return ''; // don't print 0 in empty cell
	if(input === 0) return 0; // don't print unit in cell with 0

	var depth=0;
	while(input >= 1024)
	{
		input=input/1024;
		depth++;
	}
	switch(depth)
	{
		case 0: var unit='B'; break;
		case 1: var unit='kB'; break;
		case 2: var unit='MB'; break;
		case 3: var unit='GB'; break;
		case 4: var unit='TB'; break;
		case 5: var unit='PB'; break;
		default: var unit='?B';
	}
	if(depth === 0) // "TypeError: input.toFixed is not a function" workaround
		return input + unit;
	else
		return input.toFixed(1) + unit;
}