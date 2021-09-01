<?php
	/*
	 * The Logger
	 * Easily write logs
	 *
	 * Warning:
	 *  All variables shouldn't contain space character (except $message).
	 *  All classes and log_to_file abstract depends on log_to_generic.
	 *  app_name can be empty but must be defined.
	 *
	 * Priorities: DEBUG INFO WARN ERROR
	 * OOP usage: $output=$log->log('INFO', 'Nice message');
	 *
	 * Functions with examples:
	 *  log_to_curl('INFO', 'app_name', 'http://127.0.0.1', 'Nice message', function($error){ error_log(__FILE__.' logger.php: '.$error); })
	 *   where function() is executed on curl error (optional)
	 *   last parameter is curl opts array() (empty by default, not defined in this example)
	 *   Warning: supports HTTP and HTTPS only
	 *   array on the server side:
	 *    $_POST['app_name']
	 *    $_POST['priority']
	 *    $_POST['message']
	 *  log_to_exec('INFO', 'app_name', './program', 'Nice message')
	 *   exec() required
	 *   command parameters:
	 *    $1 -> app_name
	 *    $2 -> priority
	 *    $3 -> message
	 *  log_to_mail('INFO', 'app_name', 'example@example.com', 'Nice message')
	 *   hint: add mail.add_x_header=0 to php configuration
	 *  log_to_php('INFO', 'app_name', 'Nice message')
	 *   uses configuration from php.ini
	 *  log_to_syslog('INFO', 'app_name', 'Nice message')
	 *   exec() and gnu logger binary required (default: logger, can be changed by last parameter)
	 *   *nix only
	 *
	 * Function to class wrappers (class requires function with the same name):
	 *  log_to_curl
	 *   input array keys: app_name url [on_error] [curl_opts]
	 *    on_error is callback function with one parameter
	 *  log_to_exec
	 *   input array keys: app_name command
	 *  log_to_mail
	 *   input array keys: app_name recipient
	 *  log_to_php
	 *   input array keys: app_name
	 *  log_to_syslog
	 *   input array keys: app_name [logger]
	 *    where logger is path to logger binary
	 *
	 * Classes:
	 *  log_to_csv
	 *   input array keys: app_name file [lock_file]
	 *  log_to_json
	 *   input array keys: app_name file [lock_file]
	 *  log_to_pdo
	 *   input array keys: app_name pdo_handler [table_name]
	 *  log_to_txt
	 *   input array keys: app_name file [lock_file]
	 *  log_to_xml
	 *   input array keys: app_name file [lock_file]
	 *
	 * OOP examples:
		$log=new log_to_csv(array(
			'app_name'=>'test_app',
			'file'=>'./log/journal.csv', // (required)
			'lock_file'=>'./log/journal.csv.lock' // (suggested)
			//,'delimiter'=>',' // (optional, default: comma char)
		))
		$log=new log_to_curl(array(
			'app_name'=>'test_app',
			'url'=>'http://127.0.0.1' // (required)
			//,'on_error'=>function($error){ error_log(__FILE__.' log_to_curl: '.$error); }
			//,'curl_opts'=>[CURLOPT_VERBOSE=>true]
		))
		$log=new log_to_exec(array(
			'app_name'=>'test_app',
			'command'=>'./program' // (required)
		))
		$log=new log_to_json(array(
			'app_name'=>'test_app',
			'file'=>'./log/journal.json', // (required)
			'lock_file'=>'./log/journal.json.lock' // (suggested)
		))
		$log=new log_to_mail(array(
			'app_name'=>'test_app',
			'recipient'=>'example@example.com' // (required)
		))
		$log=new log_to_pdo(array(
			'app_name'=>'test_app',
			'pdo_handler'=>new PDO('sqlite:./database.sqlite3') // (required)
			//,'table_name'=>'log' // (optional, default: log)
		))
		$log=new log_to_php(['app_name'=>'test_app'])
		$log=new log_to_syslog(array(
			'app_name'=>'test_app'
			//,'logger'=>'/bin/logger' (optional, default: logger)
		))
		$log=new log_to_txt(array(
			'app_name'=>'test_app',
			'file'=>'./log/journal.txt', // (required)
			'lock_file'=>'./log/journal.txt.lock' // (suggested)
		))
		$log=new log_to_xml(array(
			'app_name'=>'test_app',
			'file'=>'./log/journal.xml', // (required)
			'lock_file'=>'./log/journal.xml.lock' // (suggested)
		))
	 *
	 * Combo example:
		$log=new log_to_something(array(
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
			'table_name'=>'log'

			// curl
			'url'=>'http://127.0.0.1'
			//,'on_error'=>function($error){ error_log(__FILE__.' log_to_curl: '.$error); }
			//,'curl_opts'=>[CURLOPT_VERBOSE=>true]
		));
		$log->log('INFO', 'Nice message');
	 */

	function log_to_curl($priority, $app_name, $url, $message, $on_error=null, $curl_opts=array())
	{
		if(!isset($curl_opts[CURLOPT_TIMEOUT])) $curl_opts[CURLOPT_TIMEOUT]=10;
		if(!isset($curl_opts[CURLOPT_SSL_VERIFYPEER])) $curl_opts[CURLOPT_SSL_VERIFYPEER]=true;
		if(!isset($curl_opts[CURLOPT_SSLVERSION])) $curl_opts[CURLOPT_SSLVERSION]=CURL_SSLVERSION_TLSv1_2;
		if(!isset($curl_opts[CURLOPT_FAILONERROR])) $curl_opts[CURLOPT_FAILONERROR]=true;
		if(!isset($curl_opts[CURLOPT_TCP_FASTOPEN])) $curl_opts[CURLOPT_TCP_FASTOPEN]=true;
		if(!isset($curl_opts[CURLOPT_RETURNTRANSFER])) $curl_opts[CURLOPT_RETURNTRANSFER]=true;

		$curl_opts[CURLOPT_URL]=$url;
		$curl_opts[CURLOPT_POST]=true;
		$curl_opts[CURLOPT_POSTFIELDS]=http_build_query(array(
			'priority'=>$priority,
			'app_name'=>$app_name,
			'message'=>$message
		));

		$handler=curl_init();
		curl_setopt_array($handler, $curl_opts);

		$output=curl_exec($handler);

		if($on_error !== null)
			if(curl_errno($handler))
				$on_error(curl_error($handler));

		curl_close($handler);

		return $output;
	}
	function log_to_exec($priority, $app_name, $command, $message)
	{
		return exec($command.' '.$app_name.' '.$priority.' "'.$message.'"');
	}
	function log_to_mail($priority, $app_name, $recipient, $message)
	{
		return mail($recipient, 'LOG '.$app_name ,$message);
	}
	function log_to_php($priority, $app_name, $message)
	{
		return error_log($app_name.': ['.$priority.'] '.$message);
	}
	function log_to_syslog($priority, $app_name, $message, $logger='logger')
	{
		switch($priority)
		{
			case 'DEBUG': $priority='debug'; break;
			case 'INFO': $priority='info'; break;
			case 'WARN': $priority='warning'; break;
			case 'ERROR': $priority='error'; break;
			default: return false;
		}
		return exec($logger.' --priority user.'.$priority.' --tag '.$app_name.' '.$message);
	}

	abstract class log_to_generic
	{
		// constructor requires protected $constructor_params array
		protected $app_name;

		public function __construct($params)
		{
			foreach($this->constructor_params as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];
		}
	}
	abstract class log_to_file extends log_to_generic
	{
		protected $file;
		protected $lock_file=null;

		protected function lock_unlock_file($lock)
		{
			if($this->lock_file !== null)
			{
				if($lock)
				{
					while(file_exists($this->lock_file))
						sleep(0.01);
					if(file_put_contents($this->lock_file, '') === false)
						return false;
				}
				else
					return unlink($this->lock_file);
			}
			return true;
		}

		public function log($priority, $message)
		{
			if(!file_exists(dirname($this->file)))
				mkdir(dirname($this->file));
			if($this->lock_unlock_file(true))
			{
				$return=$this->do_log($priority, $message); // protected function from child
				$this->lock_unlock_file(false);
				return $return;
			}
			return false;
		}
	}

	class log_to_curl extends log_to_generic
	{
		protected $constructor_params=['app_name', 'url', 'curl_opts', 'on_error'];
		protected $url;
		protected $curl_opts=array();
		protected $on_error=null;

		public function log($priority, $message)
		{
			return log_to_curl($priority, $this->app_name, $this->url, $message, $this->on_error, $this->curl_opts);
		}
	}
	class log_to_exec extends log_to_generic
	{
		protected $constructor_params=['app_name', 'command'];
		protected $command;
		
		public function log($priority, $message)
		{
			return log_to_exec($priority, $this->app_name, $this->command, $message);
		}
	}
	class log_to_mail extends log_to_generic
	{
		protected $constructor_params=['app_name', 'recipient'];
		protected $recipient;

		public function log($priority, $message)
		{
			return log_to_mail($priority, $this->app_name, $this->recipient, $message);
		}
	}
	class log_to_pdo extends log_to_generic
	{
		protected $constructor_params=['app_name', 'pdo_handler', 'table_name'];
		protected $pdo_handler;
		protected $table_name='log';

		public function __destruct()
		{
			$this->pdo_handler=null;
		}

		public function log($priority, $message)
		{
			return $this->pdo_handler->exec('
				CREATE TABLE IF NOT EXISTS '.$this->table_name.'
				(
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					date VARCHAR(25),
					app_name VARCHAR(30),
					priority VARCHAR(10),
					message VARCHAR(255)
				);
				INSERT INTO '.$this->table_name.'(date, app_name, priority, message)
				VALUES
				(
					"'.gmdate('Y-m-d H:i:s').'",
					"'.$this->app_name.'",
					"'.$priority.'",
					"'.$message.'"
				)
			');
		}
	}
	class log_to_php extends log_to_generic
	{
		protected $constructor_params=['app_name'];

		public function log($priority, $message)
		{
			return log_to_php($priority, $this->app_name, $message);
		}
	}
	class log_to_syslog extends log_to_generic
	{
		protected $constructor_params=['app_name', 'logger'];
		protected $logger='logger';

		public function log($priority, $message)
		{
			return log_to_syslog($priority, $this->app_name, $message, $this->logger);
		}
	}

	class log_to_csv extends log_to_file
	{
		protected $constructor_params=['app_name', 'file', 'lock_file', 'delimiter'];
		protected $delimiter=',';

		protected function do_log($priority, $message)
		{
			//if(!file_exists($this->file))
			//	file_put_contents($this->file, 'date'.$this->delimiter.'app_name'.$this->delimiter.'priority'.$this->delimiter.'message'.PHP_EOL, FILE_APPEND);
			return file_put_contents($this->file, gmdate('Y-m-d H:i:s').$this->delimiter.$this->app_name.$this->delimiter.$priority.$this->delimiter.$message.PHP_EOL, FILE_APPEND);
		}
	}
	class log_to_json extends log_to_file
	{
		protected $constructor_params=['app_name', 'file', 'lock_file'];

		protected function do_log($priority, $message)
		{
			if(!file_exists($this->file))
				return file_put_contents($this->file, '[["'.gmdate('Y-m-d H:i:s').'","'.$this->app_name.'","'.$priority.'","'.$message.'"]]');
			else
				return file_put_contents($this->file, substr(file_get_contents($this->file), 0, -1).',["'.gmdate('Y-m-d H:i:s').'","'.$this->app_name.'","'.$priority.'","'.$message.'"]]');
		}
	}
	class log_to_txt extends log_to_file
	{
		protected $constructor_params=['app_name', 'file', 'lock_file'];

		protected function do_log($priority, $message)
		{
			return file_put_contents($this->file, gmdate('Y-m-d H:i:s').' '.$this->app_name.' ['.$priority.'] '.$message.PHP_EOL, FILE_APPEND);
		}
	}
	class log_to_xml extends log_to_file
	{
		protected $constructor_params=['app_name', 'file', 'lock_file'];

		protected function do_log($priority, $message)
		{
			if(!file_exists($this->file))
				return file_put_contents($this->file, '<journal><entry><date>'.gmdate('Y-m-d H:i:s').'</date><appname>'.$this->app_name.'</appname><priority>'.$priority.'</priority><message>'.$message.'</message></entry></journal>');
			else
				return file_put_contents($this->file, substr(file_get_contents($this->file), 0, -10).'<entry><date>'.gmdate('Y-m-d H:i:s').'</date><appname>'.$this->app_name.'</appname><priority>'.$priority.'</priority><message>'.$message.'</message></entry></journal>');
		}
	}
?>