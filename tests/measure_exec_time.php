<?php
	/*
	 * measure_exec_time.php library test
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

	$exec_time=new measure_exec_time_from_here();
	sleep(1);
	echo ' -> Exec time from request: '.measure_exec_time_from_request().' seconds'.PHP_EOL;
	echo ' -> Exec time from point: '.$exec_time->get_exec_time().' seconds'.PHP_EOL;
?>