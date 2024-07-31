<?php
	/*
	 * Hint:
	 *  you can setup database credentials by environment variables
	 *  variables:
	 *   TEST_DB_TYPE (pgsql, mysql, sqlite) (default: sqlite)
	 *   TEST_PGSQL_HOST (default: 127.0.0.1)
	 *   TEST_PGSQL_PORT (default: 5432)
	 *   TEST_PGSQL_SOCKET (has priority over the HOST)
	 *    eg. for pgsql (note: directory path): /var/run/postgresql
	 *    eg. for mysql: /var/run/mysqld/mysqld.sock
	 *   TEST_PGSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_PGSQL_USER (default: postgres)
	 *   TEST_PGSQL_PASSWORD (default: postgres)
	 *   TEST_MYSQL_HOST (default: [::1])
	 *   TEST_MYSQL_PORT (default: 3306)
	 *   TEST_MYSQL_SOCKET (has priority over the HOST
	 *   TEST_MYSQL_DBNAME (default: php_toolkit_tests)
	 *   TEST_MYSQL_USER (default: root)
	 *   TEST_MYSQL_PASSWORD
	 *
	 * Warning:
	 *  PDO extension is required
	 *  pdo_pgsql extension is recommended
	 *  pdo_mysql extension is recommended
	 *  pdo_sqlite extension is recommended
	 */

	namespace Test
	{
		date_default_timezone_set('UTC');

		$test_options=[
			'short'=>[
				'clients'=>32, // 256 records
				'csv_sum'=>'2c3839f21acbe4c8d2eb0b6a576da527',
				'html_sum'=>'432018e22f7dc68e9a80be9d2dc63aa7',
				'html_short_sum'=>'06053c90ca3b64f4ac48cf102831d321'
			],
			'long'=>[
				'clients'=>255, // 2040 records
				'csv_sum'=>'62f60e0a2c03fdc2ad4f35c78a93c526',
				'html_sum'=>'6e679c981f70d32c0eeffb000bb5b114',
				'html_short_sum'=>'4174540ddcad85f120c680e0c41fb6de'
			],
			'longlong'=>[
				'clients'=>125000, // 1000000 records
				'csv_sum'=>'0fcd721e26bb28ed7e500e00ce299cea',
				'html_sum'=>'7ccdd281f46de3590e79ed11db152d9f',
				'html_short_sum'=>'47a8057d213918ba693941b61b0ae351'
			]
		];

		$test_option='short';
		if(isset($argv[1]))
			switch($argv[1])
			{
				case 'long':
					$test_option='long';
				break;
				case 'longlong':
					$test_option='longlong';
			}
		echo ' -> Selected test option: '.$test_option.PHP_EOL;

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

		if(!class_exists('PDO'))
		{
			echo 'PDO extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Mocking functions and classes';
			class Exception extends \Exception {}
			class PDO extends \PDO {}
			class measure_exec_time_from_here
			{
				public function get_exec_time()
				{
					return 'measure_exec_time_from_here\get_exec_time__here';
				}
			}
			function function_exists()
			{
				return true;
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php',
			'measure_exec_time.php'
		] as $library){
			echo ' -> Including '.$library;
				if(file_exists(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(file_exists(__DIR__.'/../../../lib/'.$library))
				{
					if(@(include __DIR__.'/../../../lib/'.$library) === false)
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

		echo ' -> Including main.php';
			if(_include_tested_library(
				__NAMESPACE__,
				__DIR__.'/../main.php'
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

		echo ' -> Mocking herring';
			class herring_mock extends herring
			{
				protected $_views_path=__DIR__.'/..';
				protected $_no_view_date=true;

				protected function load_library($libraries)
				{
					foreach($libraries as $library=>$opts)
					{
						return true;
					}
				}

				public function _set_parameter($parameter, $value)
				{
					$this->$parameter=$value;
					return $this;
				}
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Removing temporary files';
			@mkdir(__DIR__.'/tmp');
			foreach([
				'herring.csv',
				'herring.html',
				'herring-short.html',
				'herring-csv.html',
				'herring-csv-short.html',
				'herring.sqlite3',
				'herring_pre_flush.sqlite3',
				'herring_pre_move.sqlite3'
			] as $file)
				@unlink(__DIR__.'/tmp/'.$file);
		echo ' [ OK ]'.PHP_EOL;

		$GLOBALS['current_timestamp']=0;
		$GLOBALS['current_timestamp_hits']=0;
		function get_timstamp()
		{
			if($GLOBALS['current_timestamp_hits'] > 50)
				$GLOBALS['current_timestamp']+=3600;

			if($GLOBALS['current_timestamp_hits'] > 100)
			{
				$GLOBALS['current_timestamp']+=86400;
				$GLOBALS['current_timestamp_hits']=0;
			}

			++$GLOBALS['current_timestamp_hits'];

			return ++$GLOBALS['current_timestamp'];
		}

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

						if(!in_array('pgsql', PDO::getAvailableDrivers()))
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

						if(!in_array('mysql', PDO::getAvailableDrivers()))
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
			{
				$pdo_handler->exec('DROP TABLE comp_herring_test_visitors');
				$pdo_handler->exec('DROP TABLE comp_herring_test_archive');
			}
		}
		if(!isset($pdo_handler))
		{
			if(!in_array('sqlite', PDO::getAvailableDrivers()))
			{
				echo 'pdo_sqlite extension is not loaded'.PHP_EOL;
				exit(1);
			}

			$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/herring.sqlite3');
		}

		$herring_mock=new herring_mock([
			'pdo_handler'=>$pdo_handler,
			'table_name_prefix'=>'comp_herring_test_',
			'ip'=>'0.0.0.0',
			'uri'=>'/',
			'cookie_name'=>'notused',
			'setcookie_callback'=>function() {}
		]);
		$herring_maintenance=new herring_mock([
			'pdo_handler'=>$pdo_handler,
			'table_name_prefix'=>'comp_herring_test_',
			'maintenance_mode'=>true
		]);
		$failed=false;
		$exceptions=[];
		$pdo_errors=[];

		echo ' -> Testing add';
			$benchmark=new \measure_exec_time_from_here();
			try {
				for($i=1; $i<=$test_options[$test_option]['clients']; $i++)
				{
					$client=$i.'.'.$i.'.'.$i.'.'.$i;

					$herring_mock->_set_parameter('ip', $client);
					$herring_mock->_set_parameter('user_agent', 'User agent of '.$client);
					$herring_mock->_set_parameter('cookie_value', md5('User agent of '.$client));
					$referer=null;

					foreach(['page1', 'page2', 'page3', 'page4', 'page1', 'page2', 'page3', 'page4'] as $page)
					{
						if($referer === null)
							$referer='http://myweb.site/home';
						else
							$referer=null;

						$herring_mock->_set_parameter('timestamp', get_timstamp());
						$herring_mock->_set_parameter('uri', '/'.$page);
						$herring_mock->_set_parameter('referer', $referer);

						$herring_mock->add();
					}
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['add', $error->getMessage()];
				$pdo_errors['add']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['add']=$benchmark->get_exec_time();
		if(!$failed)
			echo ' [ OK ]'.PHP_EOL;

		if(file_exists(__DIR__.'/tmp/herring.sqlite3'))
		{
			echo ' -> Backing up database';
				copy(__DIR__.'/tmp/herring.sqlite3', __DIR__.'/tmp/herring_pre_move.sqlite3');
			echo ' [ OK ]'.PHP_EOL;
		}
		else
			echo ' -> Backing up database [SKIP]'.PHP_EOL;

		echo ' -> Testing move_to_archive';
			$benchmark=new \measure_exec_time_from_here();
			try {
				$herring_maintenance->move_to_archive(0);
				if(empty($pdo_handler->query('SELECT * FROM comp_herring_test_visitors')->fetchAll()))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['move_to_archive', $error->getMessage()];
				$pdo_errors['move_to_archive']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['move_to_archive']=$benchmark->get_exec_time();

		echo ' -> Testing dump_archive_to_csv';
			$benchmark=new \measure_exec_time_from_here();
			try {
				$herring_maintenance->dump_archive_to_csv(__DIR__.'/tmp/herring.csv');
				if(isset($argv[2]) && ($argv[2] === 'sumdebug'))
					echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring.csv')).')';
				if(md5(file_get_contents(__DIR__.'/tmp/herring.csv')) === $test_options[$test_option]['csv_sum'])
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['dump_archive_to_csv', $error->getMessage()];
				$pdo_errors['dump_archive_to_csv']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['dump_archive_to_csv']=$benchmark->get_exec_time();

		echo ' -> Testing generate_report';
			$benchmark=new \measure_exec_time_from_here();
			try {
				$herring_maintenance->generate_report(__DIR__.'/tmp/herring.html');
				if(isset($argv[2]) && ($argv[2] === 'sumdebug'))
					echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring.html')).')';
				if(md5(file_get_contents(__DIR__.'/tmp/herring.html')) === $test_options[$test_option]['html_sum'])
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['generate_report', $error->getMessage()];
				$pdo_errors['generate_report']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['generate_report']=$benchmark->get_exec_time();

		echo ' -> Testing generate_report_short';
			$benchmark=new \measure_exec_time_from_here();
			try {
				$herring_maintenance->generate_report_short(__DIR__.'/tmp/herring-short.html');
				if(isset($argv[2]) && ($argv[2] === 'sumdebug'))
					echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring-short.html')).')';
				if(md5(file_get_contents(__DIR__.'/tmp/herring-short.html')) === $test_options[$test_option]['html_short_sum'])
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['generate_report_short', $error->getMessage()];
				$pdo_errors['generate_report_short']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['generate_report_short']=$benchmark->get_exec_time();

		if(file_exists(__DIR__.'/tmp/herring.sqlite3'))
		{
			echo ' -> Backing up database';
				copy(__DIR__.'/tmp/herring.sqlite3', __DIR__.'/tmp/herring_pre_flush.sqlite3');
			echo ' [ OK ]'.PHP_EOL;
		}
		else
			echo ' -> Backing up database [SKIP]'.PHP_EOL;

		echo ' -> Testing flush_archive';
			$benchmark=new \measure_exec_time_from_here();
			try {
				$herring_maintenance->flush_archive();
				if(empty($pdo_handler->query('SELECT * FROM comp_herring_test_archive')->fetchAll()))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['flush_archive', $error->getMessage()];
				$pdo_errors['flush_archive']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['flush_archive']=$benchmark->get_exec_time();

		echo ' -> Testing generate_report_from_csv';
			if(in_array('sqlite', PDO::getAvailableDrivers()))
			{
				$benchmark=new \measure_exec_time_from_here();
				try {
					herring_mock::generate_report_from_csv(__DIR__.'/tmp/herring.csv', __DIR__.'/tmp/herring-csv.html');
					if(isset($argv[2]) && ($argv[2] === 'sumdebug'))
						echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring-csv.html')).')';
					if(md5(file_get_contents(__DIR__.'/tmp/herring-csv.html')) === $test_options[$test_option]['html_sum'])
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				} catch(Throwable $error) {
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
					$exceptions[]=['generate_report_from_csv', $error->getMessage()];
					$pdo_errors['generate_report_from_csv']=$pdo_handler->errorInfo()[2];
				}
				$benchmarks['generate_report_from_csv']=$benchmark->get_exec_time();
			}
			else
				echo ' [SKIP]'.PHP_EOL;

		echo ' -> Testing generate_report_short_from_csv';
			if(in_array('sqlite', PDO::getAvailableDrivers()))
			{
				$benchmark=new \measure_exec_time_from_here();
				try {
					herring_mock::generate_report_short_from_csv(__DIR__.'/tmp/herring.csv', __DIR__.'/tmp/herring-csv-short.html');
					if(isset($argv[2]) && ($argv[2] === 'sumdebug'))
						echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring-csv-short.html')).')';
					if(md5(file_get_contents(__DIR__.'/tmp/herring-csv-short.html')) === $test_options[$test_option]['html_short_sum'])
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				} catch(Throwable $error) {
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
					$exceptions[]=['generate_report_short_from_csv', $error->getMessage()];
					$pdo_errors['generate_report_shory_from_csv']=$pdo_handler->errorInfo()[2];
				}
				$benchmarks['generate_report_short_from_csv']=$benchmark->get_exec_time();
			}
			else
				echo ' [SKIP]'.PHP_EOL;

		echo PHP_EOL;
		foreach($benchmarks as $benchmark_method=>$benchmark_time)
			echo $benchmark_method.': '.$benchmark_time.'s'.PHP_EOL;
		echo 'memory_get_peak_usage: '.memory_get_peak_usage().'B'.PHP_EOL;

		if($failed)
		{
			if(!empty($exceptions))
				echo PHP_EOL;

			foreach($exceptions as $exception)
				echo $exception[0].' caught: '.$exception[1].PHP_EOL;

			if(!empty($pdo_errors))
			{
				echo PHP_EOL;

				foreach($pdo_errors as $method=>$error)
					echo $method.': '.$error.PHP_EOL;
			}

			exit(1);
		}
	}
?>