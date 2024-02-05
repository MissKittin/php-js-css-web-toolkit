<?php
	function pdo_instance($on_error=null)
	{
		/*
		 * Add some features on top of pdo_connect
		 *
		 * Environment variables:
		 *  DB_IGNORE_ENV=true - ignore all variables (default: false)
		 *  DB_TYPE - select database from app/databases/samples
		 *
		 * See:
		 *  controllers/samples/login-component-test.php
		 *  models/samples/database_test_abstract.php.php
		 */

		static $pdo_handler=null;

		if($pdo_handler === null)
		{
			if(!function_exists('pdo_connect'))
				require __DIR__.'/../../../lib/pdo_connect.php';

			if(getenv('DB_IGNORE_ENV') === 'true')
				$pdo_connect_db='sqlite';
			else
				$pdo_connect_db=getenv('DB_TYPE');

			if($pdo_connect_db === false)
				$pdo_connect_db='sqlite';

			if(!is_dir(__DIR__.'/../../databases/samples/'.$pdo_connect_db))
				throw new Exception(__DIR__.'/../../databases/samples/'.$pdo_connect_db.' not exists');

			$pdo_handler=pdo_connect(
				__DIR__.'/../../databases/samples/'.$pdo_connect_db,
				$on_error
			);
		}

		return $pdo_handler;
	}
?>