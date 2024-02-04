<?php
	/*
	 * Logging functions
	 *
	 * See:
	 *  controllers/samples/login-component-test.php
	 */

	if(!class_exists('log_to_txt'))
		require __DIR__.'/../../../lib/logger.php';

	function log_fails()
	{
		static $logger=null;

		if($logger === null)
			$logger=new log_to_txt([
				'app_name'=>LOGGER_APP_NAME,
				'file'=>__DIR__.'/../../../var/log/fails.log',
				'lock_file'=>__DIR__.'/../../../var/log/fails.log.lock'
			]);

		return $logger;
	}
	function log_infos()
	{
		static $logger=null;

		if($logger === null)
			$logger=new log_to_txt([
				'app_name'=>LOGGER_APP_NAME,
				'file'=>__DIR__.'/../../../var/log/infos.log',
				'lock_file'=>__DIR__.'/../../../var/log/infos.log.lock'
			]);

		return $logger;
	}
?>