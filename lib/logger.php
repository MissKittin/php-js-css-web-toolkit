<?php
	/*
	 * The Logger
	 * Easily write logs
	 *
	 * Warning:
	 *  all variables shouldn't contain space character (except $message)
	 *  all classes and log_to_file abstract depends on log_to_generic
	 *  app_name can be empty string but must be defined
	 *
	 * Classes:
	 *  log_to_curl
	 *   input array keys: string_app_name string_url [callable_on_curl_error] [array_curl_opts]
	 *    on_curl_error is a callback function with one arg and is executed on curl error
	 *     eg: function($error){ error_log(__FILE__.' logger.php: '.$error); }
	 *    warning: supports HTTP and HTTPS only
	 *    array on the server side:
	 *     $_POST['app_name']
	 *     $_POST['priority']
	 *     $_POST['message']
	 *    returns response content
	 *  log_to_exec
	 *   input array keys: string_app_name string_command
	 *   exec() is required
	 *   command parameters:
	 *    $1 -> app_name
	 *    $2 -> priority
	 *    $3 -> message
	 *   returns exec() output
	 *  log_to_mail
	 *   input array keys: string_app_name string_recipient
	 *   hint: add mail.add_x_header=0 to php configuration
	 *   returns mail() output
	 *  log_to_php
	 *   input array keys: string_app_name
	 *   uses configuration from php.ini
	 *   returns error_log() output
	 *  log_to_syslog
	 *   input array keys: string_app_name [string_logger]
	 *    where logger is path to logger binary (default: logger)
	 *   exec() is required
	 *   *nix only
	 *   returns exec() output
	 *  log_to_csv
	 *   input array keys: string_app_name string_file [string_lock_file]
	 *   throws an Exception on error
	 *  log_to_json
	 *   input array keys: string_app_name string_file [string_lock_file]
	 *   throws an Exception on error
	 *  log_to_pdo
	 *   input array keys: string_app_name pdo_handler [string_table_name] [callable_on_pdo_error]
	 *    on_pdo_error is callback function with one arg and is executed on pdo's execute() error
	 *     eg: function($error){ error_log(__FILE__.' logger.php: '.$error[0].' '.$error[1].' '.$error[2]); }
	 *  log_to_txt
	 *   input array keys: string_app_name string_file [string_lock_file]
	 *   throws an Exception on error
	 *  log_to_xml
	 *   input array keys: string_app_name string_file [string_lock_file]
	 *   throws an Exception on error
	 *
	 * Usage:
	 *  $output=$log->debug('The condition is true');
	 *  $output=$log->info('Nice message');
	 *  $output=$log->warn('This feature is deprecated');
	 *  $output=$log->error('Something went wrong');
	 *
	 * Examples:
		$log=new log_to_csv([
			'app_name'=>'test_app',
			'file'=>'./log/journal.csv', // (required)
			'lock_file'=>'./log/journal.csv.lock' // (suggested)
			//,'delimiter'=>',' // (optional, default: comma char)
		])
		$log=new log_to_curl([
			'app_name'=>'test_app',
			'url'=>'http://127.0.0.1' // (required)
			//,'on_curl_error'=>function($error){ error_log(__FILE__.' log_to_curl: '.$error); }
			//,'curl_opts'=>[CURLOPT_VERBOSE=>true]
		])
		$log=new log_to_exec([
			'app_name'=>'test_app',
			'command'=>'./program' // (required)
		])
		$log=new log_to_json([
			'app_name'=>'test_app',
			'file'=>'./log/journal.json', // (required)
			'lock_file'=>'./log/journal.json.lock' // (suggested)
		])
		$log=new log_to_mail([
			'app_name'=>'test_app',
			'recipient'=>'example@example.com' // (required)
		])
		$log=new log_to_pdo([
			'app_name'=>'test_app',
			'pdo_handler'=>new PDO('sqlite:./database.sqlite3') // (required)
			//,'table_name'=>'log' // (optional, default: log)
			//,'on_pdo_error'=>function($error){ error_log(__FILE__.' log_to_pdo: '.$error[0].' '.$error[1].' '.$error[2]); }
		])
		$log=new log_to_php(['app_name'=>'test_app'])
		$log=new log_to_syslog([
			'app_name'=>'test_app'
			//,'logger'=>'/bin/logger' // (optional, default: logger)
		])
		$log=new log_to_txt([
			'app_name'=>'test_app',
			'file'=>'./log/journal.txt', // (required)
			'lock_file'=>'./log/journal.txt.lock' // (suggested)
		])
		$log=new log_to_xml([
			'app_name'=>'test_app',
			'file'=>'./log/journal.xml', // (required)
			'lock_file'=>'./log/journal.xml.lock' // (suggested)
		])
	 *
	 * Combo example:
		$log=new log_to_something([
			'app_name'=>'test_app',

			// files
			'file'=>'./log/journal',
			'lock_file'=>'./log/journal.lock',

			// exec
			'command'=>'./shellscript.sh',

			// mail
			'recipient'=>'example@example.com',

			// pdo
			'pdo_handler'=>new PDO('sqlite:./database.sqlite3'),
			'table_name'=>'log',
			//'on_pdo_error'=>function($error){ error_log(__FILE__.' log_to_pdo: '.$error[0].' '.$error[1].' '.$error[2]); },

			// curl
			'url'=>'http://127.0.0.1'
			//,'on_curl_error'=>function($error){ error_log(__FILE__.' log_to_curl: '.$error); }
			//,'curl_opts'=>[CURLOPT_VERBOSE=>true]
		]);
		$log->debug('The condition is true');
		$log->info('Nice message');
		$log->warn('This feature is deprecated');
		$log->error('Something went wrong');
	 */

	abstract class log_to_generic
	{
		protected $constructor_params=['app_name'];
		protected $required_constructor_params=['app_name'];

		protected $app_name;

		public function __construct(array $params)
		{
			foreach($this->required_constructor_params as $param)
				if(!isset($params[$param]))
					throw new Exception('The '.$param.' parameter was not specified for the constructor');

			foreach($this->constructor_params as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];
		}

		public function debug(string $message)
		{
			return $this->log('DEBUG', $message);
		}
		public function info(string $message)
		{
			return $this->log('INFO', $message);
		}
		public function warn(string $message)
		{
			return $this->log('WARN', $message);
		}
		public function error(string $message)
		{
			return $this->log('ERROR', $message);
		}
	}
	abstract class log_to_file extends log_to_generic
	{
		protected $constructor_params=['app_name', 'file', 'lock_file'];
		protected $required_constructor_params=['app_name', 'file'];

		protected $file;
		protected $lock_file=null;

		public function __construct(array $params)
		{
			parent::__construct($params);

			if(!file_exists(dirname($this->file)))
				if(!mkdir(dirname($this->file), 0777, true))
					throw new Exception('Unable to create '.dirname($this->file));

			if($this->lock_file === null)
				if(!file_exists(dirname($this->lock_file)))
					if(!mkdir(dirname($this->lock_file), 0777, true))
						throw new Exception('Unable to create '.dirname($this->lock_file));
		}

		protected function lock_unlock_file($lock)
		{
			if($this->lock_file !== null)
			{
				if($lock)
				{
					while(file_exists($this->lock_file))
						sleep(0.01);

					if(file_put_contents($this->lock_file, '') === false)
						throw new Exception('Unable to create lock file');
				}
				else
					return unlink($this->lock_file);
			}

			return true;
		}

		public function log(string $priority, string $message)
		{
			if($this->lock_unlock_file(true))
			{
				$return=$this->do_log($priority, $message);
				$this->lock_unlock_file(false);

				return $return;
			}

			return false;
		}
	}

	class log_to_curl extends log_to_generic
	{
		protected $constructor_params=['app_name', 'url', 'curl_opts'];
		protected $required_constructor_params=['app_name', 'url'];

		protected $url;
		protected $curl_opts=array();
		protected $on_error;

		public function __construct(array $params)
		{
			if(!extension_loaded('curl'))
				throw new Exception('curl extension is not loaded');

			parent::__construct($params);

			$this->on_error['callback']=function(){};
			if(isset($params['on_error']))
				$this->on_error['callback']=$params['on_curl_error'];

			if(!isset($this->curl_opts[CURLOPT_TIMEOUT]))
				$this->curl_opts[CURLOPT_TIMEOUT]=10;
			if(!isset($this->curl_opts[CURLOPT_SSL_VERIFYPEER]))
				$this->curl_opts[CURLOPT_SSL_VERIFYPEER]=true;
			if(!isset($this->curl_opts[CURLOPT_SSLVERSION]))
				$this->curl_opts[CURLOPT_SSLVERSION]=CURL_SSLVERSION_TLSv1_2;
			if(!isset($this->curl_opts[CURLOPT_FAILONERROR]))
				$this->curl_opts[CURLOPT_FAILONERROR]=true;
			if(!isset($this->curl_opts[CURLOPT_TCP_FASTOPEN]))
				$this->curl_opts[CURLOPT_TCP_FASTOPEN]=true;
			if(!isset($this->curl_opts[CURLOPT_RETURNTRANSFER]))
				$this->curl_opts[CURLOPT_RETURNTRANSFER]=true;

			$this->curl_opts[CURLOPT_URL]=$this->url;
			$this->curl_opts[CURLOPT_POST]=true;
		}

		public function log(string $priority, string $message)
		{
			$this->curl_opts[CURLOPT_POSTFIELDS]=http_build_query([
				'priority'=>$priority,
				'app_name'=>$this->app_name,
				'message'=>$message
			]);

			$handler=curl_init();
			foreach($this->curl_opts as $option=>$value)
				curl_setopt($handler, $option, $value);
			$output=curl_exec($handler);

			if(curl_errno($handler))
				$this->on_error['callback'](curl_error($handler));

			curl_close($handler);

			return $output;
		}
	}
	class log_to_exec extends log_to_generic
	{
		protected $constructor_params=['app_name', 'command'];

		protected $command;
		
		public function log(string $priority, string $message)
		{
			return exec($this->command.' '.$this->app_name.' '.$priority.' "'.$message.'"');
		}
	}
	class log_to_mail extends log_to_generic
	{
		protected $constructor_params=['app_name', 'recipient'];

		protected $recipient;

		public function log(string $priority, string $message)
		{
			return mail($this->recipient, '[LOG] '.$this->app_name.' '.$priority ,$message);
		}
	}
	class log_to_pdo extends log_to_generic
	{
		protected $constructor_params=['app_name', 'pdo_handler', 'table_name'];
		protected $required_constructor_params=['app_name', 'pdo_handler'];

		protected $pdo_handler;
		protected $table_name='log';
		protected $on_error;

		public function __construct(array $params)
		{
			parent::__construct($params);

			$this->on_error['callback']=function(){};
			if(isset($params['on_error']))
				$this->on_error['callback']=$params['on_pdo_error'];

			$this->pdo_handler->exec('
				CREATE TABLE IF NOT EXISTS '.$this->table_name.'
				(
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					date VARCHAR(25),
					app_name VARCHAR(30),
					priority VARCHAR(10),
					message VARCHAR(255)
				)
			');
		}

		public function log(string $priority, string $message)
		{
			$query=$this->pdo_handler->prepare('
				INSERT INTO '.$this->table_name.'(date, app_name, priority, message)
				VALUES(:date, :app_name, :priority, :message)
			');

			if(!$query->execute([
				':date'=>gmdate('Y-m-d H:i:s'),
				':app_name'=>$this->app_name,
				':priority'=>$priority,
				':message'=>$message
			]))
				$this->on_error['callback']($this->pdo_handler->errorInfo());
		}
	}
	class log_to_php extends log_to_generic
	{
		public function log(string $priority, string $message)
		{
			return error_log($this->app_name.': ['.$priority.'] '.$message);
		}
	}
	class log_to_syslog extends log_to_generic
	{
		protected $constructor_params=['app_name', 'logger'];

		protected $logger='logger';

		public function log(string $priority, string $message)
		{
			switch($priority)
			{
				case 'WARN':
					$priority='warning';
				break;
				case 'DEBUG':
				case 'INFO':
				case 'ERROR':
					$priority=strtolower($priority);
				break;
				default:
					return false;
			}

			return exec($this->logger
				.' --priority user.'.$priority
				.' --tag '.$this->app_name
				.' '.$message
			);
		}
	}

	class log_to_csv extends log_to_file
	{
		protected $constructor_params=['app_name', 'file', 'lock_file', 'delimiter'];

		protected $delimiter=',';

		protected function do_log($priority, $message)
		{
			if(file_put_contents(
				$this->file,
				gmdate('Y-m-d H:i:s')
					.$this->delimiter
					.$this->app_name
					.$this->delimiter
					.$priority
					.$this->delimiter
					.$message
					.PHP_EOL,
				FILE_APPEND
			) === false)
				throw new Exception('Unable to create log file');
		}
	}
	class log_to_json extends log_to_file
	{
		protected function do_log($priority, $message)
		{
			if(!file_exists($this->file))
			{
				if(file_put_contents(
					$this->file,
					'[["'
						.gmdate('Y-m-d H:i:s').'","'
						.$this->app_name.'","'
						.$priority.'","'
						.$message
					.'"]]'
				) === false)
					throw new Exception('Unable to create log file');
			}
			else
			{
				if(file_put_contents(
					$this->file,
					substr(file_get_contents($this->file), 0, -1)
					.',["'
						.gmdate('Y-m-d H:i:s').'","'
						.$this->app_name.'","'
						.$priority.'","'
						.$message
					.'"]]'
				) === false)
					throw new Exception('Unable to create log file');
			}
		}
	}
	class log_to_txt extends log_to_file
	{
		protected function do_log($priority, $message)
		{
			if(file_put_contents(
				$this->file,
				gmdate('Y-m-d H:i:s')
					.' '.$this->app_name
					.' ['.$priority.'] '
					.$message
					.PHP_EOL,
				FILE_APPEND
			) === false)
				throw new Exception('Unable to create log file');
		}
	}
	class log_to_xml extends log_to_file
	{
		protected function do_log($priority, $message)
		{
			if(!file_exists($this->file))
			{
				if(file_put_contents(
					$this->file,
					'<?xml version="1.0" encoding="UTF-8" ?>'
					.'<journal><entry>'
						.'<date>'.gmdate('Y-m-d H:i:s').'</date>'
						.'<appname>'.$this->app_name.'</appname>'
						.'<priority>'.$priority.'</priority>'
						.'<message>'.$message.'</message>'
					.'</entry></journal>'
				) === false)
					throw new Exception('Unable to create log file');
			}
			else
			{
				if(file_put_contents(
					$this->file,
					substr(file_get_contents($this->file), 0, -10)
					.'<entry>'
						.'<date>'.gmdate('Y-m-d H:i:s').'</date>'
						.'<appname>'.$this->app_name.'</appname>'
						.'<priority>'.$priority.'</priority>'
						.'<message>'.$message.'</message>'
					.'</entry></journal>'
				) === false)
					throw new Exception('Unable to create log file');
			}
		}
	}
?>