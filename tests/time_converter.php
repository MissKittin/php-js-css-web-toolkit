<?php
	/*
	 * time_converter.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing seconds2human';
		if(str_replace(["\n", ' '], '', var_export(seconds2human(73632827), true)) === "array('seconds'=>47,'minutes'=>33,'hours'=>5,'days'=>12,'months'=>4,'years'=>2,'weeks'=>121,)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing convert_seconds'.PHP_EOL;
		foreach([
			'minutes'=>1227213.7833333,
			'hours'=>20453.563055556,
			'days'=>1704.463587963,
			'months30'=>56.815452932099,
			'months31'=>54.982696385902,
			'years'=>2.3348816273465,
			'leap_years'=>2.3285021693483,
			'weeks'=>121.74739914021
		] as $format=>$result)
		{
			echo '  -> '.$format;
			if(abs(convert_seconds(73632827, $format)-$result) < 0.00001)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}

	if($failed)
		exit(1);
?>