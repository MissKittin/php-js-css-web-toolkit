<?php
	function convert_bytes($input)
	{
		// Automatically convert input number to human-readable form

		$depth=0;
		$value=$input;
		while($value >= 1024)
		{
			$value=$value/1024;
			++$depth;
		}
		switch($depth)
		{
			case 0: $unit='B'; break;
			case 1: $unit='kB'; break;
			case 2: $unit='MB'; break;
			case 3: $unit='GB'; break;
			case 4: $unit='TB'; break;
			case 5: $unit='PB'; break;
			default: $unit='?B';break;
		}
		return round($value, 1) . $unit;
	}
?>