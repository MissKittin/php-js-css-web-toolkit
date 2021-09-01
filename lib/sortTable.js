function sortTable(tableId)
{
	/*
	 * Adds table sort by clicking <th>
	 * first table row must be table header
	 */

	'use strict';

	var table=document.getElementById(tableId);
	for(var cell=0; cell<table.rows[0].cells.length; cell++)
	{
		table.rows[0].cells[cell].style.cursor='pointer';
		table.rows[0].cells[cell].addEventListener('click', function(){
			// https://www.w3schools.com/howto/howto_js_sort_table.asp

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