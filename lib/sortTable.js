function sortTable(table)
{
	/*
	 * Adds a function to sort the table by clicking on the table header
	 *
	 * Warning:
	 *  first table row must be table header
	 *  can be <th> or <td>
	 *
	 * Usage:
	 *  sortTable(document.getElementById('my_table'))
	 * where my_table is the id of the <table> element
	 *
	 * Source:
	 *  https://www.w3schools.com/howto/howto_js_sort_table.asp
	 */

	'use strict';

	var tableColumns=table.rows[0].cells.length;

	for(var cell=0; cell<tableColumns; cell++)
	{
		table.rows[0].cells[cell].style.cursor='pointer';

		table.rows[0].cells[cell].addEventListener('click', function(){
			var n, table, rows, switching, i, x, y, shouldSwitch, dir, switchcount=0;

			n=this.cellIndex;
			table=this.parentElement.parentElement;
			switching=true;
			dir='asc';

			while(switching)
			{
				switching=false;
				rows=table.rows;

				for(i=1; i<(rows.length-1); i++)
				{
					shouldSwitch=false;
					x=rows[i].getElementsByTagName('td')[n];
					y=rows[i+1].getElementsByTagName('td')[n];

					if(dir === 'asc')
					{
						if(x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
						{
							shouldSwitch=true;
							break;
						}
					}
					else if(dir === 'desc')
						if(x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
						{
							shouldSwitch=true;
							break;
						}
				}

				if(shouldSwitch)
				{
					rows[i].parentNode.insertBefore(rows[i+1], rows[i]);

					switching=true;
					switchcount++;
				}
				else
					if((switchcount === 0) && (dir === 'asc'))
					{
						dir='desc';
						switching=true;
					}
			}
		}, false);
	}
}