<?php
	/*
	 * Execution time measurement methods
	 * Usage:
	 *  class
	 *   $exec_time=new measure_exec_time_from_here(); error_log('Exec time: '.$exec_time->get_exec_time().' seconds');
	 *  function
	 *   error_log('Exec time: '.measure_exec_time_from_request().' seconds')
	 *  shutdown function:
			register_shutdown_function(function(){
				error_log('Exec time: '.measure_exec_time_from_request().' seconds');
			});
	 */

	class measure_exec_time_from_here
	{
		/*
		 * Measure execution time from point
		 *
		 * Usage:
		 *  add before operation: $exec_time=new measure_exec_time_from_here();
		 *  add after operation: error_log('Exec time: '.$exec_time->get_exec_time().' seconds');
		 */

		private $start_time;

		public function __construct()
		{
			$this->start_time=microtime(true);
		}

		public function get_start_time()
		{
			return $this->start_time;
		}
		public function get_exec_time()
		{
			return microtime(true)-$this->start_time;
		}
	}
	function measure_exec_time_from_request()
	{
		/*
		 * Measure execution time from request start
		 *
		 * Usage:
		 *  add after operation: error_log('Exec time: '.measure_exec_time_from_request().' seconds');
		 * or you can register shutdown function:
			register_shutdown_function(function(){
				error_log('Exec time: '.measure_exec_time_from_request().' seconds');
			});
		 */

		return microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'];
	}
?>