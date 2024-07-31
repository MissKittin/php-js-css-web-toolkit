function csv2table(
	tableElement,
	data,
	firstRowIsHeader=false,
	cellDelimiter=','
){
	/*
	 * Build a table from CSV data
	 *
	 * Warning:
	 *  don't use cellDelimiters in cells
	 *  use another delimiter instead
	 *
	 * Note:
	 *  lines can be separated using LF (Unix) or CRLF (Windows)
	 *  empty lines are ignored
	 *  whitespace at the beginning and end of the cell are removed
	 *  if the content of the cell is in quotes
	 *   the quotes will be removed
	 *
	 * Hint:
	 *  use CSS or a loop through the table to format the table
	 *
	 * Usage:
		csv2table(
			document.getElementById('tableId'), // or something similar pointing to <table>
			'csv content', // here use function or innerHTML
			true, // use <th> for the first row
			';' // custom cell delimiter
		)
	 */

	var currentRow, currentCells, currentCellValue;
	var dataRows=data.split(/[\r\n]+/);
	var currentRowIndex=tableElement.rows.length;

	for(var i=0; i<dataRows.length; i++)
	{
		if(dataRows[i] === '')
			continue;

		currentRow=tableElement.insertRow(currentRowIndex);
		currentCells=dataRows[i].split(cellDelimiter);

		for(var y=0; y<currentCells.length; y++)
		{
			currentCellValue=currentCells[y].trim();

			if(
				(currentCellValue[0] === '"') &&
				(currentCellValue[currentCellValue.length-1] === '"')
			)
				currentCellValue=currentCellValue.substring(1, currentCellValue.length-1);

			if(firstRowIsHeader)
			{
				currentRow.innerHTML+='<th>'+currentCellValue+'</th>';
				continue;
			}

			currentRow.insertCell(y).innerHTML=currentCellValue;
		}

		if(firstRowIsHeader)
			firstRowIsHeader=false;

		currentRowIndex++;
	}
}