<?php
	/*
	 * The Worker
	 * She is not afraid of any job
	 *
	 * Warning:
	 *  pcntl extension is optional (for queue server)
	 *  if it is not available the fork option will be turned off automatically
	 *
	 * Classes:
	 *  queue_worker_fifo - use named pipe as information transport
	 *   warning: only for *nix systems
	 *  queue_worker_pdo - use relational database as information transport
	 *   supported databases: PostgreSQL, MySQL, SQLite3
	 *   note: may throw PDOException depending on PDO::ATTR_ERRMODE
	 *  queue_worker_redis - use Redis as information transport
	 *  queue_worker_file - use regular files as information transport
	 *
	 * Note:
	 *  the server can execute jobs in parallel: using several instances
	 *   listening to one fifo or/and using the fork option flag
	 *  if the fork fails, the server will execute the job sequentially
	 *  throws an queue_worker_exception on error
	 *
	 * Queue server start and initialization:
	 *  see below in class
	 *
	 * Example usage: send jobs to the server immediately
		$queue_worker
		->	write([
				'name'=>'John Sackadorian',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			])
		->	write([
				'name'=>'Michael Myers',
				'file'=>'./tmp/michael',
				'mail'=>'michael@example.com'
			]);
	 *
	 * Example usage: add jobs to the queue
		$queue_worker
		->	add_to_queue([
				'name'=>'John Sackadorian',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			])
		->	add_to_queue([
				'name'=>'Michael Myers',
				'file'=>'./tmp/michael',
				'mail'=>'michael@example.com'
			]);
		$queue_worker->write_queue(); // optional, will be executed automatically on unset() or shutdown
	 *
	 * Shut down the worker server:
		$queue_worker->shutdown();
	 *
	 * Example functions.php:
		<?php
			// Here you can include libraries

			function queue_worker_main($input_data, $worker_meta)
			{
				// This function must be defined

				// only queue_worker_fifo
				if(isset($worker_meta['worker_fifo']))
					echo 'Worker: Queue worker fifo: '.$worker_meta['worker_fifo'].PHP_EOL;

				// queue_worker_pdo has $worker_meta['pdo_handle']
				// and $worker_meta['table_name']

				// only queue_worker_redis
				if(isset($worker_meta['redis_handle']))
					echo 'Worker: Redis version: '.$worker_meta['redis_handle']->info()['redis_version'].PHP_EOL;

				// only queue_worker_file
				if(isset($worker_meta['worker_dir']))
					echo 'Worker: Queue worker directory: '.$worker_meta['worker_dir'].PHP_EOL;

				if($worker_meta['worker_fork'])
				{
					echo 'Worker: Forking enabled'.PHP_EOL;

					if($worker_meta['children_limit'] === 0)
						echo 'Worker: Child process limit disabled'.PHP_EOL;
					else
						echo 'Worker: Child process limit enabled: '.$worker_meta['children_limit'].PHP_EOL;
				}
				else
					echo 'Worker: Forking disabled'.PHP_EOL;

				if($worker_meta['debug'])
					echo 'Worker: Debug mode enabled'.PHP_EOL;
				else
					echo 'Worker: Debug mode disabled'.PHP_EOL;

				if(process_file($input_data['name'], $input_data['file']))
					send_mail($input_data['name'], $input_data['file'], $input_data['mail']);
			}
			function queue_worker_shutdown()
			{
				echo 'Worker: Exiting...'.PHP_EOL;
				exit();
			}

			function process_file($name, $file)
			{
				echo 'Worker: Processing '.$file.' for '.$name.PHP_EOL;
				sleep(10);
				echo 'Worker: Processing '.$file.' for '.$name.' is completed'.PHP_EOL;

				return true;
			}
			function send_mail($name, $file, $mail)
			{
				echo 'Worker: Sending mail with '.$file.' for '.$name.' to '.$mail.PHP_EOL;
			}
		?>
	 *
	 * You can also bypass the entire system
	 * and run the main worker function directly
	 * from the app (which is not recommended):
		<?php
			require 'path/to/functions.php';

			queue_worker_main([
				'name'=>'John Sackadorian',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			], [
				'worker_fork'=>false,
				'debug'=>false
			]);

			queue_worker_main([
				'name'=>'Michael Myers',
				'file'=>'./tmp/michael',
				'mail'=>'michael@example.com'
			], [
				'worker_fork'=>false,
				'debug'=>false
			]);
		?>
	 */

	class queue_worker_exception extends Exception {}

	abstract class queue_worker_abstract
	{
		// Generic class - go ahead

		protected static $children_pids=[];

		protected $queue=[];

		protected static function worker_print_debug(
			$message,
			$debug,
			$error=false
		){
			if(!$debug)
				return static::class;

			$E='';

			if($error)
				$E='[E]';

			echo '[D]'.$E.' '.$message.PHP_EOL;

			return static::class;
		}
		protected static function worker_check_env(
			$worker_fork,
			&$children_limit,
			$worker_functions
		){
			if(php_sapi_name() !== 'cli')
				throw new queue_worker_exception(
					'This method is only for CLI'
				);

			if(
				$worker_fork &&
				(!function_exists('pcntl_fork'))
			){
				static::worker_print_debug('PCNTL extension not available - forking disabled', $debug);

				$worker_fork=false;
				$children_limit=0;
			}

			if($children_limit < 0)
				throw new queue_worker_exception(
					'Child process limit cannot be negative'
				);

			if($worker_functions !== null)
			{
				if(!file_exists($worker_functions))
					throw new queue_worker_exception(
						$worker_functions.' not exist'
					);

				if((include $worker_functions) === false)
					throw new queue_worker_exception(
						$worker_functions.' inclusion error'
					);
			}

			if(!function_exists('queue_worker_main'))
				throw new queue_worker_exception(
					'queue_worker_main function not defined in '.$worker_functions
				);
		}
		protected static function worker_print_debug_summary(
			$debug,
			$worker_functions,
			$worker_fork,
			$children_limit
		){
			if(!$debug)
				return static::class;

			if($worker_functions === null)
				static::worker_print_debug('Functions file disabled', $debug);
			else
				static::worker_print_debug('Functions path: '.$worker_functions, $debug);

			if($worker_fork)
			{
				static::worker_print_debug('Forking enabled', $debug);

				if($children_limit === 0)
					static::worker_print_debug('Child process limit disabled', $debug);
				else
					static::worker_print_debug('Child process limit enabled: '.$children_limit, $debug);
			}
			else
				static::worker_print_debug('Forking disabled', $debug);

			return static::class;
		}
		protected static function worker_setup_pcntl($worker_fork)
		{
			if($worker_fork)
			{
				declare(ticks=1);
				pcntl_signal(SIGCHLD, function($signal){
					if($signal === SIGCHLD)
						foreach(static::$children_pids as $pid)
							if(pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED) !== 0)
								unset(static::$children_pids[$pid]);
				});
			}
		}
		protected static function worker_on_empty_queue(&$worker_shutdown, $debug)
		{
			if(empty(static::$children_pids))
			{
				if($worker_shutdown)
				{
					if(function_exists('queue_worker_shutdown'))
					{
						static::worker_print_debug('Shutting down', $debug);
						queue_worker_shutdown();
					}

					static::worker_print_debug('queue_worker_shutdown function is not defined or exit() was not called in it - I keep working', $debug);

					$worker_shutdown=false;
				}

				return true;
			}

			return false;
		}
		protected static function worker_process_queue(
			&$queue,
			$worker_fork,
			$debug,
			$children_limit,
			$worker_main_params
		){
			foreach($queue as $job_id=>$job_content)
			{
				if($worker_fork)
				{
					$child_pid=pcntl_fork();

					switch($child_pid)
					{
						case -1:
							static::worker_print_debug('Fork error, job content: '.$job_content, $debug, true);
							static::worker_print_debug('Executing this job sequentially', $debug);

							queue_worker_main(
								unserialize($job_content),
								array_merge($worker_main_params, [
									'worker_fork'=>false,
									'children_limit'=>$children_limit,
									'debug'=>$debug
								])
							);
						break;
						case 0:
							static::worker_print_debug('Processing job '.$job_id.': '.$job_content, $debug);

							queue_worker_main(
								unserialize($job_content),
								array_merge($worker_main_params, [
									'worker_fork'=>$worker_fork,
									'children_limit'=>$children_limit,
									'debug'=>$debug
								])
							);

							sleep(1);
							exit();
						break;
						default:
							static::$children_pids[$child_pid]=$child_pid;
					}

					if(
						($child_pid !== -1) &&
						($children_limit !== 0) &&
						(count(static::$children_pids) === $children_limit)
					){
						static::worker_print_debug('Child process limit ('.$children_limit.') reached - waiting', $debug);
						while(pcntl_waitpid(0, $fork_status) !== -1);
					}

					unset($queue[$job_id]);

					continue;
				}

				static::worker_print_debug('Processing job '.$job_id.': '.$job_content, $debug);

				queue_worker_main(
					unserialize($job_content),
					array_merge($worker_main_params, [
						'worker_fork'=>$worker_fork,
						'children_limit'=>$children_limit,
						'debug'=>$debug
					])
				);

				unset($queue[$job_id]);
			}
		}

		public function __destruct()
		{
			$this->write_queue();
		}

		public function add_to_queue($worker_input)
		{
			$this->queue[]=$worker_input;
			return $this;
		}
		public function write($worker_input)
		{
			$this->write_raw(serialize($worker_input));
			return $this;
		}
		public function write_queue()
		{
			foreach($this->queue as $job)
				$this->write($job);

			$this->queue=[];

			return $this;
		}
		public function shutdown()
		{
			$this->write_raw('__QUEUE_WORKER_SHUTDOWN__');
		}
	}

	class queue_worker_fifo extends queue_worker_abstract
	{
		/*
		 * The Worker
		 * I N  T H E  F I F O
		 *
		 * Warning:
		 *  only for *nix systems
		 *  posix extension is recommended (for queue server)
		 *
		 * Queue server start:
			queue_worker_fifo::start_worker(
				'string_path/to/fifo',
				'string_path/to/functions.php',
				false, // bool_fork, enable parallel execution via PCNTL
				0, // int_children_limit, limit background processes
				true, // bool_recreate_fifo, use this if you want to run multiple instances
				false // bool_debug
			);
		 *
		 * Initialization:
			$queue_worker=new queue_worker_fifo('./tmp/queue_worker.fifo');
		 *
		 * Source: https://web.archive.org/web/20120416013625/http://squirrelshaterobots.com/programming/php/building-a-queue-server-in-php-part-3-accepting-input-from-named-pipes/
		 */

		protected $worker_fifo;

		public static function start_worker(
			string $worker_fifo,
			string $worker_functions,
			bool $worker_fork=false,
			int $children_limit=0,
			bool $recreate_fifo=true,
			bool $debug=false
		){
			$worker_shutdown=false;

			static::worker_check_env(
				$worker_fork,
				$children_limit,
				$worker_functions
			);

			if(
				$recreate_fifo &&
				file_exists($worker_fifo) &&
				(!unlink($worker_fifo))
			)
				throw new queue_worker_exception(
					'Unable to remove stale file'
				);

			if(file_exists($worker_fifo))
			{
				if(is_dir($worker_fifo))
					throw new queue_worker_exception(
						$worker_fifo.' is a directory'
					);
			}
			else
			{
				if(!function_exists('posix_mkfifo'))
					throw new queue_worker_exception(
						'posix extension not loaded - unable to create fifo'
					);

				if(!posix_mkfifo($worker_fifo, 0666))
					throw new queue_worker_exception(
						'Unable to create '.$worker_fifo
					);
			}

			$worker_input=fopen($worker_fifo, 'r+');

			if(!$worker_input)
				throw new queue_worker_exception(
					'Fifo opening error'
				);

			static
			::	worker_print_debug('Fifo path: '.$worker_fifo, $debug)
			::	worker_print_debug_summary(
					$debug,
					$worker_functions,
					$worker_fork,
					$children_limit
				);

			if($recreate_fifo)
				static::worker_print_debug('Fifo recreated', $debug);

			stream_set_blocking($worker_input, false);

			static::worker_setup_pcntl($worker_fork);

			while(true)
			{
				$queue=[];

				if(!$worker_shutdown)
					while(
						$input_data=trim(fgets($worker_input))
					){
						stream_set_blocking($worker_input, false);

						if($input_data === '__QUEUE_WORKER_SHUTDOWN__')
						{
							$worker_shutdown=true;
							continue;
						}

						$queue[]=$input_data;
					}

				if(empty($queue))
				{
					static::worker_print_debug('No jobs to do - waiting', $debug);

					if(static::worker_on_empty_queue($worker_shutdown, $debug))
					{
						stream_set_blocking($worker_input, true);
						continue;
					}

					static::worker_print_debug('Background processes are running - not waiting', $debug);
					usleep(500000); // 0.5s

					continue;
				}

				static::worker_process_queue(
					$queue,
					$worker_fork,
					$debug,
					$children_limit,
					['worker_fifo'=>$worker_fifo]
				);
			}
		}

		public function __construct(string $worker_fifo)
		{
			if(!file_exists($worker_fifo))
				throw new queue_worker_exception(
					$worker_fifo.' not exist'
				);

			if(is_dir($this->worker_fifo))
				throw new queue_worker_exception(
					$worker_fifo.' is a directory'
				);

			$this->worker_fifo=realpath($worker_fifo);
		}

		protected function write_raw($data)
		{
			if(file_put_contents(
				$this->worker_fifo,
				$data.PHP_EOL
			) === false)
				throw new queue_worker_exception(
					'Unable to send data to the queue server'
				);
		}
	}
	class queue_worker_pdo extends queue_worker_abstract
	{
		/*
		 * The Worker
		 * I N  T H E  S E Q U E L
		 *
		 * Note:
		 *  may throw PDOException depending on PDO::ATTR_ERRMODE
		 *
		 * Supported databases:
		 *  PostgreSQL
		 *  MySQL
		 *  SQLite3
		 *
		 * Table layout:
		 *  PostgreSQL:
		 *   `id` SERIAL PRIMARY KEY
		 *   `payload` TEXT
		 *  MySQL:
		 *   `id` INTEGER NOT NULL AUTO_INCREMENT [PRIMARY KEY]
		 *   `payload` TEXT
		 *  SQLite3:
		 *   `id` INTEGER PRIMARY KEY AUTOINCREMENT
		 *   `payload` TEXT
		 *
		 * Queue server start:
			queue_worker_pdo::start_worker(
				$pdo_handle, // object
				'string_path/to/functions.php',
				'string_table-name', // default: queue_worker
				false, // bool_fork, enable parallel execution via PCNTL
				0, // int_children_limit, limit background processes
				false // bool_debug
			);
		 *
		 * Initialization:
			$queue_worker=new queue_worker_pdo(
				$pdo_handle, // required
				'table_name' // default: queue_worker
			);
		 */

		protected $pdo_handle;
		protected $table_name;

		public static function start_worker(
			$pdo_handle,
			string $worker_functions,
			string $table_name='queue_worker',
			bool $worker_fork=false,
			int $children_limit=0,
			bool $debug=false
		){
			$worker_shutdown=false;

			static::worker_check_env(
				$worker_fork,
				$children_limit,
				$worker_functions
			);

			if(!is_object($pdo_handle))
				throw new queue_worker_exception(
					'The pdo_handle parameter is not an object'
				);

			if(!in_array(
				$pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new queue_worker_exception(
					$this->pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported'
				);

			static
			::	worker_print_debug_summary(
					$debug,
					$worker_functions,
					$worker_fork,
					$children_limit
				)
			::	worker_setup_pcntl(
					$worker_fork
				);

			switch($pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($pdo_handle->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$table_name
					.	'('
					.		'id SERIAL PRIMARY KEY,'
					.		'payload TEXT'
					.	')'
					) === false)
						throw new queue_worker_exception(
							'Cannot create '.$table_name.' table'
						);
				break;
				case 'mysql':
					if($pdo_handle->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$table_name
					.	'('
					.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
					.		'payload TEXT'
					.	')'
					) === false)
						throw new queue_worker_exception(
							'Cannot create '.$table_name.' table'
						);
				break;
				case 'sqlite':
					if($pdo_handle->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$table_name
					.	'('
					.		'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,'
					.		'payload TEXT'
					.	')'
					) === false)
						throw new queue_worker_exception(
							'Cannot create '.$table_name.' table'
						);
			}

			while(true)
			{
				$queue=[];
				$iterator=null;

				if(!$worker_shutdown)
				{
					try {
						$items=$pdo_handle->query(''
						.	'SELECT id,payload '
						.	'FROM '.$table_name
						);
					} catch(PDOException $error) {
						$items=false;
					}

					if($items === false)
					{
						static::worker_print_debug('PDO SELECT query error', $debug, true);
						usleep(500000); // 0.5s

						continue;
					}

					try {
						while($item=$items->fetch(PDO::FETCH_ASSOC))
						{
							if($item['payload'] === '__QUEUE_WORKER_SHUTDOWN__')
								$worker_shutdown=true;
							else
								$queue[]=$item['payload'];

							try {
								if($pdo_handle->exec(''
								.	'DELETE FROM '.$table_name.' '
								.	'WHERE id='.$item['id']
								) === false)
									static::worker_print_debug('PDO DELETE id='.$item['id'].' query error', $debug, true);
							} catch(PDOException $error) {
								static::worker_print_debug('PDO DELETE id='.$item['id'].' query error', $debug, true);
							}
						}
					} catch(PDOException $error) {
						static::worker_print_debug('PDO fetch error', $debug, true);
						usleep(500000); // 0.5s

						continue;
					}
				}

				if(empty($queue))
				{
					static::worker_print_debug('No jobs to do - waiting', $debug);

					if(static::worker_on_empty_queue($worker_shutdown, $debug))
					{
						sleep(5);
						continue;
					}

					static::worker_print_debug('Background processes are running - not waiting', $debug);
					usleep(500000); // 0.5s

					continue;
				}

				static::worker_process_queue(
					$queue,
					$worker_fork,
					$debug,
					$children_limit,
					[
						'pdo_handle'=>$pdo_handle,
						'table_name'=>$table_name
					]
				);
			}
		}

		public function __construct(
			$pdo_handle,
			string $table_name='queue_worker'
		){
			if(!is_object($pdo_handle))
				throw new queue_worker_exception(
					'The pdo_handle parameter is not an object'
				);

			$this->pdo_handle=$pdo_handle;
			$this->table_name=$table_name;
		}

		protected function write_raw($data)
		{
			$query=$this->pdo_handle->prepare(''
			.	'INSERT INTO '.$this->table_name
			.	'(payload) '
			.	'VALUES(:payload)'
			);

			if($query === false)
				throw new queue_worker_exception(
					'PDO prepare error'
				);

			if(!$query->execute([
				':payload'=>$data
			]))
				throw new queue_worker_exception(
					'PDO execute error - unable to send data to the queue server'
				);
		}
	}
	class queue_worker_redis extends queue_worker_abstract
	{
		/*
		 * The Worker
		 * I N  T H E  R E D I S
		 *
		 * Queue server start:
			queue_worker_redis::start_worker(
				$redis_handle, // object
				'string_path/to/functions.php',
				'string_key-prefix', // default: queue_worker__
				false, // bool_fork, enable parallel execution via PCNTL
				0, // int_children_limit, limit background processes
				false // bool_debug
			);
		 *
		 * Initialization:
			$queue_worker=new queue_worker_redis(
				$redis_handle, // required
				'key_prefix__' // default: queue_worker__
			);
		 */

		protected $redis_handle;
		protected $prefix;

		public static function start_worker(
			$redis_handle,
			string $worker_functions,
			string $prefix='queue_worker__',
			bool $worker_fork=false,
			int $children_limit=0,
			bool $debug=false
		){
			$worker_shutdown=false;

			static::worker_check_env(
				$worker_fork,
				$children_limit,
				$worker_functions
			);

			if(!is_object($redis_handle))
				throw new queue_worker_exception(
					'The redis_handle parameter is not an object'
				);

			static
			::	worker_print_debug_summary(
					$debug,
					$worker_functions,
					$worker_fork,
					$children_limit
				)
			::	worker_setup_pcntl(
					$worker_fork
				);

			while(true)
			{
				$queue=[];
				$iterator=null;

				if(!$worker_shutdown)
					do
					{
						try {
							$keys=$redis_handle->scan(
								$iterator,
								$prefix.'*'
							);
						} catch(Throwable $error) {
							static::worker_print_debug('Caught Redis error: '.$error->getMessage(), $debug);
							$keys=false;
						}

						if($keys === false)
							break;

						foreach($keys as $key)
						{
							$input_data=$redis_handle->get($key);

							if($input_data === '__QUEUE_WORKER_SHUTDOWN__')
								$worker_shutdown=true;
							else
								$queue[]=$input_data;

							$redis_handle->del($key);
						}
					}
					while($iterator > 0);

				if(empty($queue))
				{
					static::worker_print_debug('No jobs to do - waiting', $debug);

					if(static::worker_on_empty_queue($worker_shutdown, $debug))
					{
						sleep(5);
						continue;
					}

					static::worker_print_debug('Background processes are running - not waiting', $debug);
					usleep(500000); // 0.5s

					continue;
				}

				static::worker_process_queue(
					$queue,
					$worker_fork,
					$debug,
					$children_limit,
					['redis_handle'=>$redis_handle]
				);
			}
		}

		public function __construct(
			$redis_handle,
			string $prefix='queue_worker__'
		){
			if(!is_object($redis_handle))
				throw new queue_worker_exception(
					'The redis_handle parameter is not an object'
				);

			$this->redis_handle=$redis_handle;
			$this->prefix=$prefix;
		}

		protected function write_raw($data)
		{
			if($this->redis_handle->set(''
			.	$this->prefix
			.	strtr(
					microtime(false),
					' ',
					'_'
				)
			,	$data
			) === false)
				throw new queue_worker_exception(
					'Unable to send data to the queue server'
				);
		}
	}
	class queue_worker_file extends queue_worker_abstract
	{
		/*
		 * The Worker
		 * I N  T H E  F I L E
		 *
		 * Queue server start:
			queue_worker_file::start_worker(
				'string_path/to/workdir',
				'string_path/to/functions.php',
				false, // bool_fork, enable parallel execution via PCNTL
				0, // int_children_limit, limit background processes
				false // bool_debug
			);
		 *
		 * Initialization:
			$queue_worker=new queue_worker_file('./tmp/queue_worker_workdir');
		 */

		protected $worker_dir;

		public static function start_worker(
			string $worker_dir,
			string $worker_functions,
			bool $worker_fork=false,
			int $children_limit=0,
			bool $debug=false
		){
			$worker_shutdown=false;

			static::worker_check_env(
				$worker_fork,
				$children_limit,
				$worker_functions
			);

			if(!is_dir($worker_dir))
				throw new queue_worker_exception(
					$worker_dir.' is not a directory'
				);

			static
			::	worker_print_debug('Workdir: '.$worker_dir, $debug)
			::	worker_print_debug_summary(
					$debug,
					$worker_functions,
					$worker_fork,
					$children_limit
				)
			::	worker_setup_pcntl(
					$worker_fork
				);

			while(true)
			{
				$queue=[];

				if(!$worker_shutdown)
					foreach(new DirectoryIterator($worker_dir) as $input_file)
					{
						if($input_file->isDot())
							continue;

						$input_file=$input_file->getPathname();
						$input_data=file_get_contents($input_file);

						if($input_data === '__QUEUE_WORKER_SHUTDOWN__')
							$worker_shutdown=true;
						else
							$queue[]=$input_data;

						unlink($input_file);
					}

				if(empty($queue))
				{
					static::worker_print_debug('No jobs to do - waiting', $debug);

					if(static::worker_on_empty_queue($worker_shutdown, $debug))
					{
						sleep(5);
						continue;
					}

					static::worker_print_debug('Background processes are running - not waiting', $debug);
					usleep(500000); // 0.5s

					continue;
				}

				static::worker_process_queue(
					$queue,
					$worker_fork,
					$debug,
					$children_limit,
					['worker_dir'=>$worker_dir]
				);
			}
		}

		public function __construct(string $worker_dir)
		{
			if(!is_dir($worker_dir))
				throw new queue_worker_exception(
					$worker_fifo.' not exist'
				);

			$this->worker_dir=realpath($worker_dir);
		}

		protected function write_raw($data)
		{
			if(file_put_contents(
				$this->worker_dir.'/'.microtime(true).'.'.bin2hex(random_bytes(10)),
				$data
			) === false)
				throw new queue_worker_exception(
					'Unable to send data to the queue server'
				);
		}
	}
?>