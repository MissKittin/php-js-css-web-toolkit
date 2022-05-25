<?php
	/*
	 * Time period checking library
	 * from Simpleblog project
	 *
	 * Warning:
	 *  check_easter() requires check_date() and check_easter__calculate()
	 *  check_easter_cache() requires check_date()
	 *  check_easter_make_cache() requires check_easter__calculate()
	 *
	 * Functions:
	 *  check_date [returns bool]
	 *   basic function - check time if is between DD.MM - DD.MM (or D.M - D.M)
	 *  check_easter [returns bool]
	 *   easter extension for check_date
	 *  check_easter_cache [returns bool]
	 *   easter extension for check_date that uses pre-calculated tables
	 *
	 * Helpers:
	 *  check_easter__make_cache [returns string]
	 *   tables pre-calculation for check_easter_with_cache
	 *  check_easter__calculate
	 *   Gauss Easter Algorithm calculator (for internal use)
	 *
	 * Usage:
	 *  check_date(20,4, 27,8)
	 *  check_date(24,6, 14,2)
	 *  check_easter(49)
	 *  check_easter_cache(49, file_get_contents('./tmp/check_easter_cache'))
	 *  file_put_contents('./tmp/check_easter_cache', check_easter__make_cache())
	 */

	function check_date(int $start_day, int $start_month, int $end_day, int $end_month)
	{
		$current_date=strtotime(date('Y-m-d'));
		$current_year=date('Y');

		$calculate_between_years=function(
			$start_month,
			$current_date,
			$current_year,
			$end_month,
			$end_day,
			$start_day
		){
			$current_month=date('m');
			if($current_month == $start_month)
			{
				if(date('d') >= $start_day)
					return true;
			}
			else if($current_month < $start_month)
			{
				if($current_date <= strtotime($current_year.'-'.$end_month.'-'.$end_day))
					return true;
			}
			else
				if($current_date >= strtotime($current_year.'-'.$start_month.'-'.$start_day))
					return true;

			return false;
		};

		if($start_month <= $end_month)
		{
			if(($start_month === $end_month) && ($start_day > $end_day))
				return $calculate_between_years(
					$start_month,
					$current_date,
					$current_year,
					$end_month,
					$end_day,
					$start_day
				);

			if
			(
				($current_date >= strtotime($current_year.'-'.$start_month.'-'.$start_day))
				&&
				($current_date <= strtotime($current_year.'-'.$end_month.'-'.$end_day))
			)
				return true;
		}
		else
			return $calculate_between_years(
				$start_month,
				$current_date,
				$current_year,
				$end_month,
				$end_day,
				$start_day
			);

		return false;
	}
	function check_easter(int $easter_days)
	{
		$easter_start=check_easter__calculate(date('Y'));
		if($easter_start === false)
			return false;

		$easter_end['day']=$easter_start['day']+$easter_days;
		$easter_end['month']=$easter_start['month'];

		while($easter_end['day'] > 30)
		{
			if($easter_end['month']%2 === 0)
				$easter_end['day']-=30;
			else
				$easter_end['day']-=31;

			++$easter_end['month'];
		}

		return check_date(
			$easter_start['day'],
			$easter_start['month'],
			$easter_end['day'],
			$easter_end['month']
		);
	}
	function check_easter_cache(int $easter_days, string $input_data)
	{
		if(!@$date_table=unserialize($input_data))
			return false;

		$this_year=date('Y');
		$easter_end_day=$date_table[$this_year][0]+$easter_days;
		$easter_end_month=$date_table[$this_year][1];

		while($easter_end_day > 30)
		{
			if($easter_end_month%2 === 0)
				$easter_end_day-=30;
			else
				$easter_end_day-=31;

			++$easter_end_month;
		}

		return check_date(
			$date_table[$this_year][0],
			$date_table[$this_year][1],
			$easter_end_day,
			$easter_end_month
		);
	}

	function check_easter__calculate(int $this_year)
	{
		if(($this_year >= 1900) && ($this_year <= 2099))
			$tab=[24, 5];
		else if(($this_year >= 2100) && ($this_year <= 2199))
			$tab=[24, 6];
		else if(($this_year >= 2200) && ($this_year <= 2299))
			$tab=[25, 0];
		else if(($this_year >= 2300) && ($this_year <= 2399))
			$tab=[26, 1];
		else if(($this_year >= 2400) && ($this_year <= 2499))
			$tab=[25, 1];
		else
			return false;

		$a=$this_year%19;
		$b=$this_year%4;
		$c=$this_year%7;
		$d=($a*19+$tab[0])%30;
		$e=((2*$b)+(4*$c)+(6*$d)+$tab[1])%7;
		$easter['day']=22+$d+$e;
		$easter['month']=3;

		while($easter['day'] > 31)
		{
			$easter['day']-=31;
			++$easter['month'];
		}

		return $easter;
	}
	function check_easter__make_cache()
	{
		$this_year=date('Y');
		for($i=1; $i<=100; ++$i)
		{
			$calculate_easter_output=check_easter__calculate($this_year);
			$output_array[$this_year]=[
				$calculate_easter_output['day'],
				$calculate_easter_output['month']
			];
			++$this_year;
		}

		return serialize($output_array);
	}
?>