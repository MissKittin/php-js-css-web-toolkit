<?php
	/*
	 * The Logger
	 * Easily write logs
	 *
	 * Warning:
	 *  methods log_to_csv, log_to_json, log_to_txt, and log_to_xml
	 *   can be problematic if the output file is too large
	 *   consider using other methods or apply log rotation
	 *  all variables shouldn't contain space character (except $message)
	 *  all classes and log_to_file abstract depends on log_to_generic
	 *  app_name can be empty string but must be defined
	 *
	 * Classes:
	 *  log_to_curl
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'url' => 'http://string.myserv.er/log' // required
	 *    'on_curl_error' => function($error){ error_log(__FILE__.' logger.php: '.$error); } // optional, executed on curl error
	 *    'curl_opts' => [CURL_OPT=>'value'] // optional
	 *   warning: supports HTTP and HTTPS only
	 *   array on the server side:
	 *    $_POST['app_name']
	 *    $_POST['priority']
	 *    $_POST['message']
	 *   returns response content
	 *  log_to_exec
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'command' => 'string/path/to/prog' // required
	 *   warning: exec() is required
	 *   command parameters:
	 *    $1 -> app_name
	 *    $2 -> priority
	 *    $3 -> message
	 *   returns exec() output
	 *  log_to_mail
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'recipient' => 'string-logs@myserv.er' // required
	 *    'mail_callback' => function($recipient, $app_name, $priority, $message){ return my_mail_function($recipient, '[LOG] '.$app_name.' '.$priority, $message); } // optional
	 *   hint: add mail.add_x_header=0 to php configuration
	 *   returns mail() or $mail_callback() output
	 *  log_to_php
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *   note: uses configuration from php.ini
	 *   returns error_log() output
	 *  log_to_syslog
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'logger' => 'string/path/to/bin/logger' // optional, default: logger
	 *   warning: exec() is required
	 *   warning: *nix only
	 *   returns exec() output
	 *  log_to_csv
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'file' => 'string/path/to/file' // required
	 *    'lock_file' => 'string/path/to/file.lock' // suggested
	 *    'delimiter' => ',' // char, optional
	 *   note: throws an logger_exception on error
	 *  log_to_json
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'file' => 'string/path/to/file' // required
	 *    'lock_file' => 'string/path/to/file.lock' // suggested
	 *   note: throws an logger_exception on error
	 *  log_to_pdo
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'pdo_handler' => new PDO() // required
	 *    'table_name' => 'string_my_table' // optional, default: log
	 *    'create_table' => true // optional, default (safe): true
	 *    'on_pdo_error' => function($error){ error_log(__FILE__.' logger.php: '.$error[0].' '.$error[1].' '.$error[2]); } // optional, executed on pdo's execute() error
	 *   supported databases: PostgreSQL, MySQL, SQLite3
	 *   table layout:
	 *    PostgreSQL:
	 *     `id` SERIAL PRIMARY KEY
	 *     `date` VARCHAR(25)
	 *     `app_name` VARCHAR(30)
	 *     `priority` VARCHAR(10)
	 *     `message` VARCHAR(255)
	 *    MySQL:
	 *     `id` INTEGER NOT NULL AUTO_INCREMENT [PRIMARY KEY]
	 *     `date` VARCHAR(25)
	 *     `app_name` VARCHAR(30)
	 *     `priority` VARCHAR(10)
	 *     `message` VARCHAR(255)
	 *    SQLite3:
	 *     `id` INTEGER PRIMARY KEY AUTOINCREMENT
	 *     `date` VARCHAR(25)
	 *     `app_name` VARCHAR(30)
	 *     `priority` VARCHAR(10)
	 *     `message` VARCHAR(255)
	 *  log_to_txt
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'file' => 'string/path/to/file' // required
	 *    'lock_file' => 'string/path/to/file.lock' // suggested
	 *   note: throws an logger_exception on error
	 *  log_to_xml
	 *   input array params:
	 *    'app_name' => 'string_my_app_name' // required
	 *    'file' => 'string/path/to/file' // required
	 *    'lock_file' => 'string/path/to/file.lock' // suggested
	 *   note: throws an logger_exception on error
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
			'file'=>'./log/journal.csv',
			'lock_file'=>'./log/journal.csv.lock' // suggested
			//,'delimiter'=>',' // optional
		])
		$log=new log_to_curl([
			'app_name'=>'test_app',
			'url'=>'http://127.0.0.1'
			//,'on_curl_error'=>function($error){ error_log(__FILE__.' log_to_curl: '.$error); }
			//,'curl_opts'=>[CURLOPT_VERBOSE=>true]
		])
		$log=new log_to_exec([
			'app_name'=>'test_app',
			'command'=>'./program'
		])
		$log=new log_to_json([
			'app_name'=>'test_app',
			'file'=>'./log/journal.json',
			'lock_file'=>'./log/journal.json.lock' // suggested
		])
		$log=new log_to_mail([
			'app_name'=>'test_app',
			'recipient'=>'example@example.com'
			//,'mail_callback'=>function($recipient, $app_name, $priority, $message)
			//{
			//	return my_mail_function(
			//		$recipient,
			//		'[LOG] '.$app_name.' '.$priority,
			//		$message
			//	);
			//}
		])
		$log=new log_to_pdo([
			'app_name'=>'test_app',
			'pdo_handler'=>new PDO('sqlite:./database.sqlite3')
			//,'table_name'=>'log'
			//,'create_table'=>true
			//,'on_pdo_error'=>function($error){ error_log(__FILE__.' log_to_pdo: '.$error[0].' '.$error[1].' '.$error[2]); }
		])
		$log=new log_to_php(['app_name'=>'test_app'])
		$log=new log_to_syslog([
			'app_name'=>'test_app'
			//,'logger'=>'/bin/logger' // default: logger
		])
		$log=new log_to_txt([
			'app_name'=>'test_app',
			'file'=>'./log/journal.txt',
			'lock_file'=>'./log/journal.txt.lock' // suggested
		])
		$log=new log_to_xml([
			'app_name'=>'test_app',
			'file'=>'./log/journal.xml',
			'lock_file'=>'./log/journal.xml.lock' // suggested
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
			//'mail_callback'=>function($recipient, $app_name, $priority, $message)
			//{
			//	return my_mail_function(
			//		$recipient,
			//		'[LOG] '.$app_name.' '.$priority,
			//		$message
			//	);
			//},

			// pdo
			'pdo_handler'=>new PDO('sqlite:./database.sqlite3'),
			'table_name'=>'log',
			//'create_table'=>false,
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

	class logger_exception extends Exception {}

	abstract class log_to_generic
	{
		protected $constructor_params=['app_name'=>'string'];
		protected $required_constructor_params=['app_name'];

		protected $app_name;

		public function __construct(array $params)
		{
			foreach($this->required_constructor_params as $param)
				if(!isset($params[$param]))
					throw new logger_exception('The '.$param.' parameter was not specified for the constructor');

			foreach($this->constructor_params as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new logger_exception('The input array parameter '.$param.' is not a '.$param_type);

					$this->$param=$params[$param];
				}
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
		protected $constructor_params=[
			'app_name'=>'string',
			'file'=>'string',
			'lock_file'=>'string'
		];
		protected $required_constructor_params=['app_name', 'file'];

		protected $file;
		protected $lock_file=null;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			if(
				(!file_exists(dirname($this->file))) &&
				(!mkdir(dirname($this->file), 0777, true))
			)
				throw new logger_exception('Unable to create '.dirname($this->file));

			if(
				($this->lock_file === null) &&
				(!file_exists(dirname($this->lock_file))) &&
				(!mkdir(dirname($this->lock_file), 0777, true))
			)
				throw new logger_exception('Unable to create '.dirname($this->lock_file));
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
						throw new logger_exception('Unable to create lock file');
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
		protected $constructor_params=[
			'app_name'=>'string',
			'url'=>'string',
			'curl_opts'=>'array'
		];
		protected $required_constructor_params=['app_name', 'url'];

		protected $url;
		protected $curl_opts=[];
		protected $on_error;

		public function __construct(array $params)
		{
			if(!extension_loaded('curl'))
				throw new logger_exception('curl extension is not loaded');

			parent::{__FUNCTION__}($params);

			$this->on_error['callback']=function(){};

			if(isset($params['on_curl_error']))
			{
				if(!is_callable($params['on_curl_error']))
					throw new logger_exception('The input array parameter on_curl_error is not callable');

				$this->on_error['callback']=$params['on_curl_error'];
			}

			foreach([
				CURLOPT_TIMEOUT=>10,
				CURLOPT_SSL_VERIFYPEER=>true,
				CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1_2,
				CURLOPT_FAILONERROR=>true,
				CURLOPT_TCP_FASTOPEN=>true,
				CURLOPT_RETURNTRANSFER=>true
			] as $curl_opt_name=>$curl_opt_value)
				if(!isset($this->curl_opts[$curl_opt_name]))
					$this->curl_opts[$curl_opt_name]=$curl_opt_value;

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

			$curl_handler=curl_init();
			foreach($this->curl_opts as $option=>$value)
				curl_setopt($curl_handler, $option, $value);
			$output=curl_exec($curl_handler);

			if(curl_errno($curl_handler))
				$this->on_error['callback'](curl_error($curl_handler));

			curl_close($curl_handler);

			return $output;
		}
	}
	class log_to_exec extends log_to_generic
	{
		protected $constructor_params=[
			'app_name'=>'string',
			'command'=>'string'
		];

		protected $command;

		public function log(string $priority, string $message)
		{
			return exec($this->command.' '.$this->app_name.' '.$priority.' "'.$message.'"');
		}
	}
	class log_to_mail extends log_to_generic
	{
		protected $constructor_params=[
			'app_name'=>'string',
			'recipient'=>'string'
		];

		protected $recipient;
		protected $mail_callback;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$this->mail_callback['callback']=function($recipient, $app_name, $priority, $message)
			{
				return mail(
					$recipient,
					'[LOG] '.$app_name.' '.$priority,
					$message
				);
			};

			if(isset($params['mail_callback']))
			{
				if(!is_callable($params['mail_callback']))
					throw new logger_exception('The input array parameter mail_callback is not callable');

				$this->mail_callback['callback']=$params['mail_callback'];
			}
		}

		public function log(string $priority, string $message)
		{
			return $this->mail_callback['callback']($this->recipient, $this->app_name, $priority, $message);
		}
	}
	class log_to_pdo extends log_to_generic
	{
		protected $constructor_params=[
			'app_name'=>'string',
			'pdo_handler'=>'object',
			'table_name'=>'string',
			'create_table'=>'boolean'
		];
		protected $required_constructor_params=['app_name', 'pdo_handler'];

		protected $pdo_handler;
		protected $table_name='log';
		protected $create_table=true;
		protected $on_error;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$this->on_error['callback']=function(){};

			if(isset($params['on_pdo_error']))
			{
				if(!is_callable($params['on_pdo_error']))
					throw new logger_exception('The input array parameter on_pdo_error is not callable');

				$this->on_error['callback']=$params['on_pdo_error'];
			}

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new logger_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if($this->create_table)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id SERIAL PRIMARY KEY,'
						.		'date VARCHAR(25),'
						.		'app_name VARCHAR(30),'
						.		'priority VARCHAR(10),'
						.		'message VARCHAR(255)'
						.	')'
						) === false)
							$this->on_error['callback']($this->pdo_handler->errorInfo());
					break;
					case 'mysql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
						.		'date VARCHAR(25),'
						.		'app_name VARCHAR(30),'
						.		'priority VARCHAR(10),'
						.		'message VARCHAR(255)'
						.	')'
						) === false)
							$this->on_error['callback']($this->pdo_handler->errorInfo());
					break;
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
						.		'date VARCHAR(25),'
						.		'app_name VARCHAR(30),'
						.		'priority VARCHAR(10),'
						.		'message VARCHAR(255)'
						.	')'
						) === false)
							$this->on_error['callback']($this->pdo_handler->errorInfo());
				}
		}

		public function log(string $priority, string $message)
		{
			$query=$this->pdo_handler->prepare(''
			.	'INSERT INTO '.$this->table_name
			.	'('
			.		'date,'
			.		'app_name,'
			.		'priority,'
			.		'message'
			.	') VALUES ('
			.		':date,'
			.		':app_name,'
			.		':priority,'
			.		':message'
			.	')'
			);

			if($query === false)
				$this->on_error['callback']($this->pdo_handler->errorInfo());

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
		protected $constructor_params=[
			'app_name'=>'string',
			'logger'=>'string'
		];

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
			.	' --priority user.'.$priority
			.	' --tag '.$this->app_name
			.	' '.$message
			);
		}
	}

	class log_to_csv extends log_to_file
	{
		protected $constructor_params=[
			'app_name'=>'string',
			'file'=>'string',
			'lock_file'=>'string',
			'delimiter'=>'string'
		];

		protected $delimiter=',';

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			if(
				isset($params['delimiter']) &&
				(isset($params['delimiter'][0]) && (!isset($params['delimiter'][1]))) // (strlen($params['delimiter']) === 1)
			)
				throw new logger_exception('The delimiter parameter must have length 1');
		}

		protected function do_log($priority, $message)
		{
			if(file_put_contents(
				$this->file,
				gmdate('Y-m-d H:i:s')
				.	$this->delimiter
				.	$this->app_name
				.	$this->delimiter
				.	$priority
				.	$this->delimiter
				.	$message
				.	PHP_EOL,
				FILE_APPEND
			) === false)
				throw new logger_exception('Unable to create log file');
		}
	}
	class log_to_json extends log_to_file
	{
		protected function do_log($priority, $message)
		{
			if(!file_exists($this->file))
			{
				if(file_put_contents(
					$this->file, ''
					.'[['
					.	'"'.gmdate('Y-m-d H:i:s').'",'
					.	'"'.$this->app_name.'",'
					.	'"'.$priority.'",'
					.	'"'.str_replace('"', '\"', $message).'"'
					.']]'
				) === false)
					throw new logger_exception('Unable to create log file');
			}
			else
			{
				$file_handler=fopen($this->file, 'r+');

				if($file_handler === false)
					throw new logger_exception('Unable to edit log file');

				$new_log_size=fstat($file_handler)['size']-1;

				$array_separator=',';

				if($new_log_size < 0)
					$array_separator='[';
				else
				{
					ftruncate($file_handler, $new_log_size);
					fseek($file_handler, $new_log_size);
				}

				fwrite(
					$file_handler,
					$array_separator
					.'['
					.	'"'.gmdate('Y-m-d H:i:s').'",'
					.	'"'.$this->app_name.'",'
					.	'"'.$priority.'",'
					.	'"'.str_replace('"', '\"', $message).'"'
					.']]'
				);

				fclose($file_handler);
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
				throw new logger_exception('Unable to create log file');
		}
	}
	class log_to_xml extends log_to_file
	{
		protected function do_log($priority, $message)
		{
			if(!file_exists($this->file))
			{
				if(file_put_contents(
					$this->file, ''
					.'<?xml version="1.0" encoding="UTF-8" ?>'
					.'<journal><entry>'
					.	'<date>'.gmdate('Y-m-d H:i:s').'</date>'
					.	'<appname>'.$this->app_name.'</appname>'
					.	'<priority>'.$priority.'</priority>'
					.	'<message>'.$message.'</message>'
					.'</entry></journal>'
				) === false)
					throw new logger_exception('Unable to create log file');
			}
			else
			{
				$file_handler=fopen($this->file, 'r+');

				if($file_handler === false)
					throw new logger_exception('Unable to edit log file');

				$new_log_size=fstat($file_handler)['size']-10;

				$xml_header='';

				if($new_log_size < 0)
					$xml_header='<?xml version="1.0" encoding="UTF-8" ?><journal>';
				else
				{
					ftruncate($file_handler, $new_log_size);
					fseek($file_handler, $new_log_size);
				}

				fwrite(
					$file_handler,
					$xml_header
					.'<entry>'
					.	'<date>'.gmdate('Y-m-d H:i:s').'</date>'
					.	'<appname>'.$this->app_name.'</appname>'
					.	'<priority>'.$priority.'</priority>'
					.	'<message>'.$message.'</message>'
					.'</entry></journal>'
				);

				fclose($file_handler);
			}
		}
	}
?>