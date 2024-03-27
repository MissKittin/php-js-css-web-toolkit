<?php
	/*
	 * logger.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite) (default: sqlite)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_SOCKET (has priority over the HOST)
	 *    eg. /var/run/postgresql
	 *    note: path to the directory, not socket
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_SOCKET (has priority over the HOST)
	 *    eg. /var/run/mysqld/mysqld.sock
	 *   TEST_MYSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
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

		if(!extension_loaded('PDO'))
		{
			echo 'PDO extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Mocking functions and classes';
			class PDO extends \PDO {}
			class Exception extends \Exception {}
			function gmdate($param)
			{
				return '0000-00-00 00:00:00';
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Removing temporary files';
			@mkdir(__DIR__.'/tmp');
			@mkdir(__DIR__.'/tmp/logger');
			foreach(['csv', 'json', 'txt', 'xml', 'sqlite3', 'sh'] as $log)
				if(file_exists(__DIR__.'/tmp/logger/log.'.$log))
					unlink(__DIR__.'/tmp/logger/log.'.$log);
		echo ' [ OK ]'.PHP_EOL;

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;

		if(getenv('TEST_DB_TYPE') !== false)
		{
			echo ' -> Configuring PDO'.PHP_EOL;

			$_pdo=[
				'type'=>getenv('TEST_DB_TYPE'),
				'credentials'=>[
					'pgsql'=>[
						'host'=>'127.0.0.1',
						'port'=>'5432',
						'dbname'=>'php_toolkit_tests',
						'user'=>'postgres',
						'password'=>'postgres'
					],
					'mysql'=>[
						'host'=>'[::1]',
						'port'=>'3306',
						'dbname'=>'php_toolkit_tests',
						'user'=>'root',
						'password'=>''
					]
				]
			];

			foreach(['pgsql', 'mysql'] as $_pdo['_database'])
				foreach(['host', 'port', 'socket', 'dbname', 'user', 'password'] as $_pdo['_parameter'])
				{
					$_pdo['_variable']='TEST_'.strtoupper($_pdo['_database'].'_'.$_pdo['_parameter']);
					$_pdo['_value']=getenv($_pdo['_variable']);

					if($_pdo['_value'] !== false)
					{
						echo '  -> Using '.$_pdo['_variable'].'="'.$_pdo['_value'].'" as '.$_pdo['_database'].' '.$_pdo['_parameter'].PHP_EOL;
						$_pdo['credentials'][$_pdo['_database']][$_pdo['_parameter']]=$_pdo['_value'];
					}
				}

			try /* some monsters */ {
				switch($_pdo['type'])
				{
					case 'pgsql':
						echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

						if(!extension_loaded('pdo_pgsql'))
							throw new Exception('pdo_pgsql extension is not loaded');

						if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
							$pdo_handler=new PDO('pgsql:'
								.'host='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
								.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
								.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
								.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
							);
						else
							$pdo_handler=new PDO('pgsql:'
								.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
								.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
								.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'].';'
								.'user='.$_pdo['credentials'][$_pdo['type']]['user'].';'
								.'password='.$_pdo['credentials'][$_pdo['type']]['password'].''
							);
					break;
					case 'mysql':
						echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;

						if(!extension_loaded('pdo_mysql'))
							throw new Exception('pdo_mysql extension is not loaded');

						if(isset($_pdo['credentials'][$_pdo['type']]['socket']))
							$pdo_handler=new PDO('mysql:'
								.'unix_socket='.$_pdo['credentials'][$_pdo['type']]['socket'].';'
								.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
								$_pdo['credentials'][$_pdo['type']]['user'],
								$_pdo['credentials'][$_pdo['type']]['password']
							);
						else
							$pdo_handler=new PDO('mysql:'
								.'host='.$_pdo['credentials'][$_pdo['type']]['host'].';'
								.'port='.$_pdo['credentials'][$_pdo['type']]['port'].';'
								.'dbname='.$_pdo['credentials'][$_pdo['type']]['dbname'],
								$_pdo['credentials'][$_pdo['type']]['user'],
								$_pdo['credentials'][$_pdo['type']]['password']
							);
					break;
					case 'sqlite':
						echo '  -> Using '.$_pdo['type'].' driver'.PHP_EOL;
					break;
					default:
						echo '  -> '.$_pdo['type'].' driver is not supported [FAIL]'.PHP_EOL;
				}
			} catch(Throwable $error) {
				echo ' Error: '.$error->getMessage().PHP_EOL;
				exit(1);
			}

			if(isset($pdo_handler))
				$pdo_handler->exec('DROP TABLE logger');
		}
		if(!isset($pdo_handler))
		{
			if(!extension_loaded('pdo_sqlite'))
			{
				echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
				exit(1);
			}

			$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/logger/log.sqlite3');
		}

		$failed=false;

		$GLOBALS['_mail_callback_output']=[];
		$log_params=[
			'app_name'=>'test_app',

			// files
			'file'=>__DIR__.'/tmp/logger/log',
			'lock_file'=>__DIR__.'/tmp/logger/log.lock',

			// exec
			'command'=>__DIR__.'/tmp/logger/log.sh',

			// mail
			'recipient'=>'example@example.com',
			'mail_callback'=>function($recipient, $app_name, $priority, $message)
			{
				$GLOBALS['_mail_callback_output'][$priority]=$recipient.'-'.$app_name.'-'.$message;
				return true;
			},

			// pdo
			'pdo_handler'=>$pdo_handler,
			'table_name'=>'logger',
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
			'Test\log_to_mail',
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
				case 'Test\log_to_mail':
					foreach(['DEBUG', 'INFO', 'WARN', 'ERROR'] as $mail_callback_output_test)
						if(!isset($GLOBALS['_mail_callback_output'][$mail_callback_output_test]))
							$test_failed=true;
						else if($GLOBALS['_mail_callback_output'][$mail_callback_output_test] !== 'example@example.com-test_app-'.strtolower($mail_callback_output_test).' test')
							$test_failed=true;
				break;
				case 'Test\log_to_pdo':
					$pdo_fetch=$pdo_handler->query('SELECT * FROM logger')->fetchAll();

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