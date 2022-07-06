<?php
	/*
	 * logger.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_sqlite extension is required
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		foreach(['PDO', 'pdo_sqlite'] as $extension)
			if(!extension_loaded($extension))
			{
				echo $extension.' extension is not loaded'.PHP_EOL;
				exit(1);
			}

		echo ' -> Mocking functions and classes';
			class PDO extends \PDO {}
			function gmdate($param)
			{
				return '0000-00-00 00:00:00';
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Removing temporary files';
			@mkdir(__DIR__.'/tmp');
			foreach(['csv', 'json', 'txt', 'xml'] as $log)
				if(file_exists(__DIR__.'/tmp/logger/log.'.$log))
					unlink(__DIR__.'/tmp/logger/log.'.$log);
		echo ' [ OK ]'.PHP_EOL;

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(_include_tested_library(
				__NAMESPACE__,
				__DIR__.'/../lib/'.basename(__FILE__)
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

		if(isset($argv[1]))
		{
			switch($argv[1])
			{
				case 'pgsql':
					$pdo_handler=new PDO('pgsql:'
						.'host=127.0.0.1;'
						.'port=5432;'
						.'dbname=logger_test;'
						.'user=postgres;'
						.'password=postgres'
					);
				break;
				case 'mysql':
					$pdo_handler=new PDO('mysql:'
						.'host=[::1];'
						.'port=3306;'
						.'dbname=logger-test',
						'root',
						''
					);
			}

			$pdo_handler->exec('DROP TABLE log');
		}
		if(!isset($pdo_handler))
			$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/logger.sqlite3');

		$failed=false;

		$log_params=[
			'app_name'=>'test_app',

			// files
			'file'=>__DIR__.'/tmp/logger/log',
			'lock_file'=>__DIR__.'/tmp/logger/log.lock',

			// exec
			'command'=>__DIR__.'/tmp/logger.sh',

			// mail
			'recipient'=>'example@example.com',

			// pdo
			'pdo_handler'=>$pdo_handler,
			'table_name'=>'log',
			//'on_pdo_error'=>function($error){ error_log(__FILE__.' log_to_pdo: '.$error[0].' '.$error[1].' '.$error[2]); },

			// curl
			'url'=>'http://127.0.0.1'
			//,'on_curl_error'=>function($error){ error_log(__FILE__.' log_to_curl: '.$error); }
			//,'curl_opts'=>[CURLOPT_VERBOSE=>true]
		];

		foreach([
			'Test\log_to_csv',
			//'Test\log_to_curl',
			//'Test\log_to_exec',
			'Test\log_to_json',
			//'Test\log_to_mail',
			'Test\log_to_pdo',
			//'Test\log_to_php',
			//'Test\log_to_syslog',
			'Test\log_to_txt',
			'Test\log_to_xml'
		] as $class){
			echo ' -> Testing '.$class;

			switch($class)
			{
				case 'Test\log_to_csv':
					$log_params['file']=__DIR__.'/tmp/logger/log.csv';
				break;
				case 'Test\log_to_json':
					$log_params['file']=__DIR__.'/tmp/logger/log.json';
				break;
				case 'Test\log_to_txt':
					$log_params['file']=__DIR__.'/tmp/logger/log.txt';
				break;
				case 'Test\log_to_xml':
					$log_params['file']=__DIR__.'/tmp/logger/log.xml';
			}
			$log_handler=new $class($log_params);

			foreach(['debug', 'info', 'warn', 'error'] as $method)
				$log_handler->$method($method.' test');

			$test_failed=false;
			switch($class)
			{
				case 'Test\log_to_csv':
					if(str_replace(PHP_EOL, '', file_get_contents(__DIR__.'/tmp/logger/log.csv')) !== '0000-00-00 00:00:00,test_app,DEBUG,debug test0000-00-00 00:00:00,test_app,INFO,info test0000-00-00 00:00:00,test_app,WARN,warn test0000-00-00 00:00:00,test_app,ERROR,error test')
						$test_failed=true;
				break;
				case 'Test\log_to_json':
					if(file_get_contents(__DIR__.'/tmp/logger/log.json') !== '[["0000-00-00 00:00:00","test_app","DEBUG","debug test"],["0000-00-00 00:00:00","test_app","INFO","info test"],["0000-00-00 00:00:00","test_app","WARN","warn test"],["0000-00-00 00:00:00","test_app","ERROR","error test"]]')
						$test_failed=true;
				break;
				case 'Test\log_to_pdo':
					$pdo_fetch=$pdo_handler->query('SELECT * FROM log')->fetchAll();

					if($pdo_fetch[0]['id'].$pdo_fetch[0]['date'].$pdo_fetch[0]['app_name'].$pdo_fetch[0]['priority'].$pdo_fetch[0]['message'] !== '10000-00-00 00:00:00test_appDEBUGdebug test')
						$test_failed=true;
					if($pdo_fetch[1]['id'].$pdo_fetch[1]['date'].$pdo_fetch[1]['app_name'].$pdo_fetch[1]['priority'].$pdo_fetch[1]['message'] !== '20000-00-00 00:00:00test_appINFOinfo test')
						$test_failed=true;
					if($pdo_fetch[2]['id'].$pdo_fetch[2]['date'].$pdo_fetch[2]['app_name'].$pdo_fetch[2]['priority'].$pdo_fetch[2]['message'] !== '30000-00-00 00:00:00test_appWARNwarn test')
						$test_failed=true;
					if($pdo_fetch[3]['id'].$pdo_fetch[3]['date'].$pdo_fetch[3]['app_name'].$pdo_fetch[3]['priority'].$pdo_fetch[3]['message'] !== '40000-00-00 00:00:00test_appERRORerror test')
						$test_failed=true;
				break;
				case 'Test\log_to_txt':
					if(str_replace(PHP_EOL, '', file_get_contents(__DIR__.'/tmp/logger/log.txt')) !== '0000-00-00 00:00:00 test_app [DEBUG] debug test0000-00-00 00:00:00 test_app [INFO] info test0000-00-00 00:00:00 test_app [WARN] warn test0000-00-00 00:00:00 test_app [ERROR] error test')
						$test_failed=true;
				break;
				case 'Test\log_to_xml':
					if(file_get_contents(__DIR__.'/tmp/logger/log.xml') !== '<?xml version="1.0" encoding="UTF-8" ?><journal><entry><date>0000-00-00 00:00:00</date><appname>test_app</appname><priority>DEBUG</priority><message>debug test</message></entry><entry><date>0000-00-00 00:00:00</date><appname>test_app</appname><priority>INFO</priority><message>info test</message></entry><entry><date>0000-00-00 00:00:00</date><appname>test_app</appname><priority>WARN</priority><message>warn test</message></entry><entry><date>0000-00-00 00:00:00</date><appname>test_app</appname><priority>ERROR</priority><message>error test</message></entry></journal>')
						$test_failed=true;
			}
			if($test_failed)
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		}

		if($failed)
			exit(1);
	}
?>