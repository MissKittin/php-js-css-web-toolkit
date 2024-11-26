<?php
	/*
	 * HTML calendar widget
	 *
	 * Functions:
	 *  mk_calendar_m2w(int_month, int_year)
	 *  mk_calendar_table(mk_calendar_m2w(int_month, int_year), array_highlighting) // the first day of the week is Sunday
	 *  mk_calendar_table(mk_calendar_m2w(int_month, int_year, 2), array_highlighting) // the first day of the week is Monday
	 */

	class mk_calendar_exception extends Exception {}

	function mk_calendar_m2w(
		int $month, int $year,
		int $offset=1 // for mk_calendar_table()
	){
		/*
		 * Days of the month to days of the week converter
		 *
		 * Note:
		 *  throws an mk_calendar_exception on error
		 *
		 * Usage:
			$sliced_month=mk_calendar_m2w(10, 2024);
		 *
		 * Example output array:
			[
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
			($month < 0) ||
			($month > 12)
		)
			throw new mk_calendar_exception(
				'month lower than 0 or greater than 12'
			);

		if($year < 1)
			throw new mk_calendar_exception(
				'year is lower than 1'
			);

		$date=mktime(12, 0, 0, $month, 1, $year);
		$offset=date('w', $date)-$offset;
		$days_in_month=(int)date('t', $date);
		$output_array=[];

		for($day=1; $day<=$days_in_month; ++$day)
			$output_array[$day]=($day+$offset)%7;

		return $output_array;
	}
	function mk_calendar_table(
		array $mk_calendar_m2w,
		array $highlighting=[]
	){
		/*
		 * Table with calendar
		 * Simple widget with events
		 *
		 * Note:
		 *  the first day of the week is Sunday
		 *  if you want to set Monday as the first day of the week
		 *   add a third parameter with value 2
		 *   to the mk_calendar_m2w function, e.g.:
		 *    mk_calendar_m2w(10, 2024, 2)
		 *
		 * Example usage:
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
				<?php
					echo mk_calendar_table(mk_calendar_m2w(10, 2024), [
						// this parameter is optional
						4=>[ // 04.10.2024 is a special day
							'params'=>'class="calendar_event"', // add HTML parameters to <td>, optional
							'link'=>'href="/events/2024/10/04"' // insert day number as link, optional
						],
						12=>[ // 12.10.2024 is a special day
							'params'=>'class="calendar_event"', // add HTML parameters to <td>, optional
							'link'=>'href="/events/2024/10/12"' // insert day number as link, optional
						]
					]);
				?>
			</table>
		 */

		$output='<tr>';

		for($i=1; $i<=$mk_calendar_m2w[1]; ++$i)
			$output.='<td></td>';

		foreach($mk_calendar_m2w as $day=>$day_of_the_week)
		{
			if($day_of_the_week === 0)
				$output.='</tr><tr>';

			$output.='<td';

			if(isset($highlighting[$day]['params']))
				$output.=' '.$highlighting[$day]['params'];

			$output.='>';

			if(isset($highlighting[$day]['link']))
			{
				$output.='<a '.$highlighting[$day]['link'].'>'.$day.'</a></td>';
				continue;
			}

			$output.=$day.'</td>';
		}

		for($i=end($mk_calendar_m2w); $i<6; ++$i)
			$output.='<td></td>';

		return $output.'</tr>';
	}
?>