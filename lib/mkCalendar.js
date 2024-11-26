/*
 * HTML calendar widget
 *
 * Functions:
 *  mkCalendarM2W(int_month, int_year)
 *  mkCalendarTable(mkCalendarM2W(int_month, int_year), array_highlighting) // the first day of the week is Sunday
 *  mkCalendarTable(mkCalendarM2W(int_month, int_year, 2), array_highlighting) // the first day of the week is Monday
 */

function mkCalendarM2W(
	month, year,
	offset=1 // for mkCalendarTable()
){
	/*
	 * Days of the month to days of the week converter
	 *
	 * Note:
	 *  returns false on error
	 *
	 * Usage:
		var sliced_month=mkCalendarM2W(10, 2024);
	 *
	 * Example output array:
		[
			0=>null
			1=>2 // Tuesday
			2=>3 // Wednesday
			3=>4 // Thursday
			4=>5 // Friday
			5=>6 // Saturday
			6=>0 // Sunday
			7=>1 // Monday
			8=>2 // Tuesday
			...
			31=>4 // Thursday
		]
	 */

	if(
		(month < 0) ||
		(month > 12)
	)
		return false;

	if(year < 1)
		return false;

	var date=new Date(year, month-1, 1, 12, 0, 0, 0);
	offset=date.getDay()-offset;
	var daysInMonth=(new Date(year, month, 0)).getDate();
	var outputArray=[];

	for(var day=1; day<=daysInMonth; day++)
		outputArray[day]=(day+offset)%7;

	return outputArray;
}
function mkCalendarTable(mkCalendarM2W, highlighting=[])
{
	/*
	 * Table with calendar
	 * Simple widget with events
	 *
	 * Note:
	 *  the first day of the week is Sunday
	 *  if you want to set Monday as the first day of the week
	 *   add a third parameter with value 2
	 *   to the mkCalendarM2W function, e.g.:
	 *    mkCalendarM2W(10, 2024, 2)
	 *
	 * Example usage:
	 *  add to script block:
		document.addEventListener('DOMContentLoaded', function(){
			document.getElementById('calendar').innerHTML+=mkCalendarTable(mkCalendarM2W(10, 2024), {
				// this parameter is optional
				4: { // 04.10.2024 is a special day
					'params': 'class="calendar_event"', // add HTML parameters to <td>, optional
					'link': 'href="/events/2024/10/04"' // insert day number as link, optional
				},
				12: { // 12.10.2024 is a special day
					'params': 'class="calendar_event"', // add HTML parameters to <td>, optional
					'link': 'href="/events/2024/10/12"' // insert day number as link, optional
				}
			});
		}, false);
	 * and insert html code:
		<style>
			#calendar {
				font-family: monospace;
			}
			#calendar,
			#calendar th,
			#calendar td {
				border: 1px solid #000000;
				border-collapse: collapse;
				text-align: center;
			}
			#calendar .calendar_event {
				background-color: #009900;
			}
			#calendar .calendar_event a,
			#calendar .calendar_event a:hover,
			#calendar .calendar_event a:visited {
				color: #ffff00;
				text-decoration: none;
			}
		</style>
		<table id="calendar">
			<tr>
				<th>Su</th>
				<th>Mo</th>
				<th>Tu</th>
				<th>We</th>
				<th>Th</th>
				<th>Fr</th>
				<th>Sa</th>
			</tr>
		</table>
	 */

	if(mkCalendarM2W === false)
		return false

	var output='<tr>';

	for(var day=1; day<=mkCalendarM2W[1]; day++)
		output+='<td></td>';

	for(day=1; day<mkCalendarM2W.length; day++)
	{
		if(mkCalendarM2W[day] === 0)
			output+='</tr><tr>';

		output+='<td';

		if(
			(highlighting[day] !== undefined) &&
			(highlighting[day]['params'] !== undefined)
		)
			output+=' '+highlighting[day]['params'];

		output+='>';

		if(
			(highlighting[day] !== undefined) &&
			(highlighting[day]['link'] !== undefined)
		){
			output+='<a '+highlighting[day]['link']+'>'+day+'</a></td>';
			continue;
		}

		output+=day+'</td>';
	}

	for(var i=mkCalendarM2W[mkCalendarM2W.length-1]; i<6; i++)
		output+='<td></td>';

	return output+'</tr>';
}