<?php
	/*
	 * Time period checking library
	 * from simpleblog project
	 * PHP version
	 *
	 * check_date()
	 *  basic function - check time if is between DD.MM - DD.MM (or D.M - D.M)
	 *  example usage: check_easter(20,04, 27,08)
	 *  example usage: check_easter(24,06, 14,02)
	 * check_easter()
	 *  extension for check_date()
	 *  example usage: check_easter(49)
	 *  check_date() required
	 * check_easter_with_cache()
	 *  extension for check_date() that uses pre-calculated tables
	 *  example usage: check_easter_with_cache(49, './var/check_easter_cache.php')
	 *  check_date() required
	 * check_easter_make_cache()
	 *  tables pre-calculation for check_easter_with_cache()
	 *  mainly for offline usage
	 *  example usage: check_easter_make_cache('./var/check_easter_cache.php')
	 */

	function check_date($start_day, $start_month, $end_day, $end_month)
	{
		$current_date=date('Y-m-d', strtotime(date('Y-m-d'))); // unix timestamp
		$this_year=date('Y');

		if($start_month < $end_month) // in the same year
			$new_year=$this_year;
		else // between new year
		{
			if(($current_date <= date('Y-m-d', strtotime('12/31/'.$this_year))) && (date('m') > 11))
				$new_year=++$this_year; // old year
			else
			{
				// new year
				$new_year=$this_year;
				$this_year=--$this_year;
			}
		}

		$start_date=date('Y-m-d', strtotime($start_month.'/'.$start_day.'/'.$this_year));
		$end_date=date('Y-m-d', strtotime($end_month.'/'.$end_day.'/'.$new_year));

		if(($current_date >= $start_date) && ($current_date <= $end_date))
			return true;

		return false;
	}
	function check_easter($easter_days)
	{
		$calculate_easter=function()
		{
			$this_year=date('Y'); // cache year

			// constants
			if(($this_year >= 1900) && ($this_year <= 2099))
			{
				$tabA=24; $tabB=5;
			}
			else if(($this_year >= 2100) && ($this_year <= 2199))
			{
				$tabA=24; $tabB=6;
			}
			else if(($this_year >= 2200) && ($this_year <= 2299))
			{
				$tabA=25; $tabB=0;
			}
			else if(($this_year >= 2300) && ($this_year <= 2399))
			{
				$tabA=26; $tabB=1;
			}
			else if(($this_year >= 2400) && ($this_year <= 2499))
			{
				$tabA=25; $tabB=1;
			}
			else
				return false;

			$a=$this_year % 19;
			$b=$this_year % 4;
			$c=$this_year % 7;
			$d=($a * 19 + 24) % 30;
			$e=((2 * $b)+(4 * $c)+(6 * $d)+5) % 7;
			$easter['day']=22 + $d + $e; $easter['month']=3;

			// correct start date
			while($easter['day'] > 31)
			{
				$easter['day']=$easter['day'] - 31;
				++$easter['month'];
			}

			return $easter;
		};

		// calculate end date
		$easter_start=$calculate_easter(); if($easter_start === false) { return false; }
		$easter_end['day']=$easter_start['day']+$easter_days; $easter_end['month']=$easter_start['month'];

		// correct end date
		while($easter_end['day'] > 30)
		{
			if($easter_end['month']%2 == 0)
				$easter_end['day']=$easter_end['day']-30;
			else
				$easter_end['day']=$easter_end['day']-31;
			++$easter_end['month'];
		}

		return check_date($easter_start['day'].'.'.$easter_start['month'], $easter_end['day'].'.'.$easter_end['month']);
	}

	function check_easter_with_cache($easter_days, $input_file)
	{
		// run check_easter_make_cache() to make cache file

		$this_year=date('Y'); // cache
		if(!@$date_table=unserialize(file_get_contents($input_file))) return false; // read data, abort if no data available

		// calculate end date
		$easter_end_day=$date_table[$this_year][0]+$easter_days;
		$easter_end_month=$date_table[$this_year][1];

		// correct end date
		while($easter_end_day > 30)
		{
			if($easter_end_month%2 === 0)
				$easter_end_day=$easter_end_day-30;
			else
				$easter_end_day=$easter_end_day-31;

			++$easter_end_month;
		}

		return check_date($date_table[$this_year][0], $date_table[$this_year][1], $easter_end_day, $easter_end_month);
	}
	function check_easter_make_cache($output_file)
	{
		$calculate_easter=function($this_year)
		{
			// constants
			if(($this_year >= 1900) && ($this_year <= 2099))
			{
				$tabA=24; $tabB=5;
			}
			else if(($this_year >= 2100) && ($this_year <= 2199))
			{
				$tabA=24; $tabB=6;
			}
			else if(($this_year >= 2200) && ($this_year <= 2299))
			{
				$tabA=25; $tabB=0;
			}
			else if(($this_year >= 2300) && ($this_year <= 2399))
			{
				$tabA=26; $tabB=1;
			}
			else if(($this_year >= 2400) && ($this_year <= 2499))
			{
				$tabA=25; $tabB=1;
			}
			else
				return false;

			$a=$this_year % 19;
			$b=$this_year % 4;
			$c=$this_year % 7;
			$d=($a * 19 + 24) % 30;
			$e=((2 * $b)+(4 * $c)+(6 * $d)+5) % 7;
			$easter['day']=22 + $d + $e; $easter['month']=3;

			// correct start date
			while($easter['day'] > 31)
			{
				$easter['day']=$easter['day'] - 31;
				++$easter['month'];
			}

			return $easter;
		};

		$this_year=date('Y');
		for($i=1; $i<=100; ++$i)
		{
			$calculate_easter_output=$calculate_easter($this_year);
			$output_array[$this_year]=array($calculate_easter_output['day'], $calculate_easter_output['month']);
			++$this_year;
		}

		file_put_contents($output_file, serialize($output_array));
	}
?>