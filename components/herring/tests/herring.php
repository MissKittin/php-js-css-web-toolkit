<?php
	/*
	 * Warning:
	 *  the html_sum will change if the sortTable.js library is updated
	 */

	namespace Test
	{
		$test_options=[
			'short'=>[
				'clients'=>32, // 256 records
				'csv_sum'=>'5be6b7824f183f86c39d9e64a44e036e',
				'html_sum'=>'6346b52e60aba3ec2fe37fa54a248901'
			],
			'long'=>[
				'clients'=>255, // 2040 records
				'csv_sum'=>'e85f1f8d3de5b5e8be6523c98360a05e',
				'html_sum'=>'80d20ee289797c164c10a681e72b7b9e'
			],
			'longlong'=>[
				'clients'=>125000, // 1000000 records
				'csv_sum'=>'58f6e1c28c92fa400e42f0f8ad16a70e',
				'html_sum'=>'fe9c35113f165730e966a2d9e78e5a9c'
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

		foreach(['PDO', 'pdo_sqlite'] as $extension)
			if(!extension_loaded($extension))
			{
				echo $extension.' extension is not loaded'.PHP_EOL;
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

		echo ' -> Including herring.php';
			if(_include_tested_library(
				__NAMESPACE__,
				__DIR__.'/../herring.php'
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
						if($library !== 'sortTable.js')
							return true;

						if(file_exists(__DIR__.'/../lib/'.$library))
							include __DIR__.'/../lib/'.$library;
						else if(file_exists(__DIR__.'/../../../lib/'.$library))
							include __DIR__.'/../../../lib/'.$library;
						else
							throw new Exception($library.' library not found');
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
			if($GLOBALS['current_timestamp_hits'] > 100)
			{
				$GLOBALS['current_timestamp']+=86400;
				$GLOBALS['current_timestamp_hits']=0;
			}

			++$GLOBALS['current_timestamp_hits'];

			return ++$GLOBALS['current_timestamp'];
		}

		if(isset($argv[2]))
		{
			switch($argv[2])
			{
				case 'pgsql':
					$pdo_handler=new PDO('pgsql:'
						.'host=127.0.0.1;'
						.'port=5432;'
						.'dbname=herring_test;'
						.'user=postgres;'
						.'password=postgres'
					);
				break;
				case 'mysql':
					$pdo_handler=new PDO('mysql:'
						.'host=[::1];'
						.'port=3306;'
						.'dbname=herring-test',
						'root',
						''
					);
			}

			$pdo_handler->exec('DROP TABLE herring_test_visitors');
			$pdo_handler->exec('DROP TABLE herring_test_archive');
		}
		if(!isset($pdo_handler))
			$pdo_handler=new PDO('sqlite:'.__DIR__.'/tmp/herring.sqlite3');

		$herring_mock=new herring_mock([
			'pdo_handler'=>$pdo_handler,
			'table_name_prefix'=>'herring_test_',
			'ip'=>'0.0.0.0',
			'uri'=>'/',
			'cookie_name'=>'notused',
			'setcookie_callback'=>function() {}
		]);
		$herring_maintenance=new herring_mock([
			'pdo_handler'=>$pdo_handler,
			'table_name_prefix'=>'herring_test_',
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
			} catch(Exception $error) {
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
				if(empty($pdo_handler->query('SELECT * FROM herring_test_visitors')->fetchAll()))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Exception $error) {
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
				//echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring.csv')).')';
				if(md5(file_get_contents(__DIR__.'/tmp/herring.csv')) === $test_options[$test_option]['csv_sum'])
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Exception $error) {
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
				//echo ' ('.md5(file_get_contents(__DIR__.'/tmp/herring.html')).')';
				if(md5(file_get_contents(__DIR__.'/tmp/herring.html')) === $test_options[$test_option]['html_sum'])
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Exception $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['generate_report', $error->getMessage()];
				$pdo_errors['generate_report']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['generate_report']=$benchmark->get_exec_time();

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
				if(empty($pdo_handler->query('SELECT * FROM herring_test_archive')->fetchAll()))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Exception $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$exceptions[]=['flush_archive', $error->getMessage()];
				$pdo_errors['flush_archive']=$pdo_handler->errorInfo()[2];
			}
			$benchmarks['flush_archive']=$benchmark->get_exec_time();

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