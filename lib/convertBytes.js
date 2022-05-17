function convertBytes(input, returnArray=false)
{
	// Automatically convert input number to human-readable form

	var unit, output;

	// don't print 0 in empty cell
	if(input === '')
		return '';

	// don't print unit in cell with 0
	if(input === 0)
		return 0;

	var depth=0;
	while(input >= 1024)
	{
		input=input/1024;
		depth++;
	}

	switch(depth)
	{
		case 0: unit='B'; break;
		case 1: unit='kB'; break;
		case 2: unit='MB'; break;
		case 3: unit='GB'; break;
		case 4: unit='TB'; break;
		case 5: unit='PB'; break;
		default: unit='?B';
	}

	if(depth === 0) // "TypeError: input.toFixed is not a function" workaround
		output=input;
	else
		output=input.toFixed(1);

	if(returnArray)
		return [
			output,
			unit
		];

	return output+unit;
}