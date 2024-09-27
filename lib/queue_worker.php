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
				'name'=>'John',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			])
		->	write([
				'name'=>'Michael',
				'file'=>'./tmp/michael',
				'mail'=>'michael@example.com'
			]);
	 *
	 * Example usage: add jobs to the queue
		$queue_worker
		->	add_to_queue([
				'name'=>'John',
				'file'=>'./tmp/john',
				'mail'=>'john@example.com'
			])
		->	add_to_queue([
				'name'=>'Michael',
				'file'=>'./tmp/michael',
				'mail'=>'michael@example.com'
			]);
		$queue_worker->write_queue(); // optional, will be executed automatically on unset() or shutdown
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

				// queue_worker_pdo has $worker_meta['pdo_handler']
				// and $worker_meta['table_name']

				// only queue_worker_redis
				if(isset($worker_meta['redis_handler']))
					echo 'Worker: Redis version: '.$worker_meta['redis_handler']->info()['redis_version'].PHP_EOL;

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
	 */

	class queue_worker_exception extends Exception {}

	abstract class queue_worker_abstract
	{
		// Generic class - go ahead

		protected static $children_pids=[];

		protected $queue=[];

		public function __destruct()
		{
			$this->write_queue();
		}

		public function add_to_queue($worker_input)
		{
			$this->queue[]=$worker_input;
			return $this;
		}
		public function write_queue()
		{
			if(!empty($this->queue))
			{
				foreach($this->queue as $job)
					$this->write($job);

				$this->queue=[];
			}

			return $this;
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
		 *  $queue_worker=new queue_worker_fifo('./tmp/queue_worker.fifo');
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
			if(php_sapi_name() !== 'cli')
				throw new queue_worker_exception('This method is only for CLI');

			if($worker_fork && (!function_exists('pcntl_fork')))
			{
				if($debug)
					echo '[D] PCNTL extension not available - forking disabled'.PHP_EOL;

				$worker_fork=false;
				$children_limit=0;
			}

			if($children_limit < 0)
				throw new queue_worker_exception('Child process limit cannot be negative');

			if($worker_functions !== null)
			{
				if(!file_exists($worker_functions))
					throw new queue_worker_exception($worker_functions.' not exist');

				if((include $worker_functions) === false)
					throw new queue_worker_exception($worker_functions.' inclusion error');
			}

			if(!function_exists('queue_worker_main'))
				throw new queue_worker_exception('queue_worker_main function not defined in '.$worker_functions);

			if(
				$recreate_fifo &&
				file_exists($worker_fifo) &&
				(!unlink($worker_fifo))
			)
				throw new queue_worker_exception('Unable to remove stale file');

			if(file_exists($worker_fifo))
			{
				if(is_dir($worker_fifo))
					throw new queue_worker_exception($worker_fifo.' is a directory');
			}
			else
			{
				if(!function_exists('posix_mkfifo'))
					throw new queue_worker_exception('posix extension not loaded - unable to create fifo');

				if(!posix_mkfifo($worker_fifo, 0666))
					throw new queue_worker_exception('Unable to create '.$worker_fifo);
			}

			$worker_input=fopen($worker_fifo, 'r+');

			if(!$worker_input)
				throw new queue_worker_exception('Fifo opening error');

			if($debug)
			{
				echo '[D] Fifo path: '.$worker_fifo.PHP_EOL;

				if($worker_functions === null)
					echo '[D] Functions file disabled'.PHP_EOL;
				else
					echo '[D] Functions path: '.$worker_functions.PHP_EOL;

				if($worker_fork)
				{
					echo '[D] Forking enabled'.PHP_EOL;

					if($children_limit === 0)
						echo '[D] Child process limit disabled'.PHP_EOL;
					else
						echo '[D] Child process limit enabled: '.$children_limit.PHP_EOL;
				}
				else
					echo '[D] Forking disabled'.PHP_EOL;

				if($recreate_fifo)
					echo '[D] Fifo recreated'.PHP_EOL;
			}

			stream_set_blocking($worker_input, false);

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

			while(true)
			{
				$queue=[];

				while($input_data=trim(fgets($worker_input)))
				{
					stream_set_blocking($worker_input, false);
					$queue[]=$input_data;
				}

				if(empty($queue))
				{
					if($debug)
						echo '[D] No jobs to do - waiting'.PHP_EOL;

					if(empty(static::$children_pids))
						stream_set_blocking($worker_input, true);
					else
					{
						if($debug)
							echo '[D]  Background processes are running - not waiting'.PHP_EOL;

						usleep(500000); // 0.5s
					}
				}
				else
					foreach($queue as $job_id=>$job_content)
					{
						if($worker_fork)
						{
							$child_pid=pcntl_fork();

							if($child_pid === -1)
							{
								if($debug)
								{
									echo '[D][E] Fork error, job content: '.$job_content.PHP_EOL;
									echo '[D] Executing this job sequentially'.PHP_EOL;
								}

								queue_worker_main(
									unserialize($job_content),
									[
										'worker_fifo'=>$worker_fifo,
										'worker_fork'=>false,
										'children_limit'=>$children_limit,
										'debug'=>$debug
									]
								);
							}
							else if($child_pid === 0)
							{
								if($debug)
									echo '[D] Processing job '.$job_id.': '.$job_content.PHP_EOL;

								queue_worker_main(
									unserialize($job_content),
									[
										'worker_fifo'=>$worker_fifo,
										'worker_fork'=>$worker_fork,
										'children_limit'=>$children_limit,
										'debug'=>$debug
									]
								);

								sleep(1);
								exit();
							}
							else
								static::$children_pids[$child_pid]=$child_pid;

							if(
								($child_pid !== -1) &&
								($children_limit !== 0) &&
								(count(static::$children_pids) === $children_limit)
							){
								if($debug)
									echo '[D] Child process limit ('.$children_limit.') reached - waiting'.PHP_EOL;

								while(pcntl_waitpid(0, $fork_status) !== -1);
							}
						}
						else
						{
							if($debug)
								echo '[D] Processing job '.$job_id.': '.$job_content.PHP_EOL;

							queue_worker_main(
								unserialize($job_content),
								[
									'worker_fifo'=>$worker_fifo,
									'worker_fork'=>$worker_fork,
									'children_limit'=>$children_limit,
									'debug'=>$debug
								]
							);
						}

						unset($queue[$job_id]);
					}
			}
		}

		public function __construct(string $worker_fifo)
		{
			if(!file_exists($worker_fifo))
				throw new queue_worker_exception($worker_fifo.' not exist');

			if(is_dir($this->worker_fifo))
				throw new queue_worker_exception($worker_fifo.' is a directory');

			$this->worker_fifo=realpath($worker_fifo);
		}

		public function write($worker_input)
		{
			if(file_put_contents($this->worker_fifo, serialize($worker_input).PHP_EOL) === false)
				throw new queue_worker_exception('Unable to send data to the queue server');

			return $this;
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
				$pdo_handler, // object
				'string_path/to/functions.php',
				'string_table-name', // default: queue_worker
				false, // bool_fork, enable parallel execution via PCNTL
				0, // int_children_limit, limit background processes
				false // bool_debug
			);
		 *
		 * Initialization:
			$queue_worker=new queue_worker_pdo(
				$pdo_handler, // required
				'table_name' // default: queue_worker
			);
		 */

		protected $pdo_handler;
		protected $table_name;

		public static function start_worker(
			$pdo_handler,
			string $worker_functions,
			string $table_name='queue_worker',
			bool $worker_fork=false,
			int $children_limit=0,
			bool $debug=false
		){
			if(php_sapi_name() !== 'cli')
				throw new queue_worker_exception('This method is only for CLI');

			if(!is_object($pdo_handler))
				throw new queue_worker_exception('The pdo_handler parameter is not an object');

			if(!in_array(
				$pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new queue_worker_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if($worker_fork && (!function_exists('pcntl_fork')))
			{
				if($debug)
					echo '[D] PCNTL extension not available - forking disabled'.PHP_EOL;

				$worker_fork=false;
				$children_limit=0;
			}

			if($children_limit < 0)
				throw new queue_worker_exception('Child process limit cannot be negative');

			if($worker_functions !== null)
			{
				if(!file_exists($worker_functions))
					throw new queue_worker_exception($worker_functions.' not exist');

				if((include $worker_functions) === false)
					throw new queue_worker_exception($worker_functions.' inclusion error');
			}

			if(!function_exists('queue_worker_main'))
				throw new queue_worker_exception('queue_worker_main function not defined in '.$worker_functions);

			if($debug)
			{
				if($worker_functions === null)
					echo '[D] Functions file disabled'.PHP_EOL;
				else
					echo '[D] Functions path: '.$worker_functions.PHP_EOL;

				if($worker_fork)
				{
					echo '[D] Forking enabled'.PHP_EOL;

					if($children_limit === 0)
						echo '[D] Child process limit disabled'.PHP_EOL;
					else
						echo '[D] Child process limit enabled: '.$children_limit.PHP_EOL;
				}
				else
					echo '[D] Forking disabled'.PHP_EOL;
			}

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

			switch($pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$table_name
					.	'('
					.		'id SERIAL PRIMARY KEY,'
					.		'payload TEXT'
					.	')'
					) === false)
						throw new queue_worker_exception('Cannot create '.$table_name.' table');
				break;
				case 'mysql':
					if($pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$table_name
					.	'('
					.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
					.		'payload TEXT'
					.	')'
					) === false)
						throw new queue_worker_exception('Cannot create '.$table_name.' table');
				break;
				case 'sqlite':
					if($pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$table_name
					.	'('
					.		'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,'
					.		'payload TEXT'
					.	')'
					) === false)
						throw new queue_worker_exception('Cannot create '.$table_name.' table');
			}

			while(true)
			{
				$queue=[];
				$iterator=null;

				try {
					$items=$pdo_handler->query('SELECT id,payload FROM '.$table_name);
				} catch(PDOException $error) {
					$items=false;
				}

				if($items === false)
				{
					if($debug)
						echo '[D] PDO SELECT query error'.PHP_EOL;

					usleep(500000); // 0.5s

					continue;
				}

				try {
					while($item=$items->fetch(PDO::FETCH_ASSOC))
					{
						$queue[]=$item['payload'];

						try {
							if(
								($pdo_handler->exec('DELETE FROM '.$table_name.' WHERE id='.$item['id']) === false) &&
								$debug
							)
								echo '[D] PDO DELETE id='.$item['id'].' query error'.PHP_EOL;
						} catch(PDOException $error) {
							if($debug)
								echo '[D] PDO DELETE id='.$item['id'].' query error'.PHP_EOL;
						}
					}
				} catch(PDOException $error) {
					if($debug)
						echo '[D] PDO fetch error'.PHP_EOL;

					usleep(500000); // 0.5s

					continue;
				}

				if(empty($queue))
				{
					if($debug)
						echo '[D] No jobs to do - waiting'.PHP_EOL;

					if(empty(static::$children_pids))
						sleep(5);
					else
					{
						if($debug)
							echo '[D]  Background processes are running - not waiting'.PHP_EOL;

						usleep(500000); // 0.5s
					}
				}
				else
					foreach($queue as $job_id=>$job_content)
					{
						if($worker_fork)
						{
							$child_pid=pcntl_fork();

							if($child_pid === -1)
							{
								if($debug)
								{
									echo '[D][E] Fork error, job content: '.$job_content.PHP_EOL;
									echo '[D] Executing this job sequentially'.PHP_EOL;
								}

								queue_worker_main(
									unserialize($job_content),
									[
										'pdo_handler'=>$pdo_handler,
										'table_name'=>$table_name,
										'worker_fork'=>false,
										'children_limit'=>$children_limit,
										'debug'=>$debug
									]
								);
							}
							else if($child_pid === 0)
							{
								if($debug)
									echo '[D] Processing job '.$job_id.': '.$job_content.PHP_EOL;

								queue_worker_main(
									unserialize($job_content),
									[
										'pdo_handler'=>$pdo_handler,
										'table_name'=>$table_name,
										'worker_fork'=>$worker_fork,
										'children_limit'=>$children_limit,
										'debug'=>$debug
									]
								);

								sleep(1);
								exit();
							}
							else
								static::$children_pids[$child_pid]=$child_pid;

							if(
								($child_pid !== -1) &&
								($children_limit !== 0) &&
								(count(static::$children_pids) === $children_limit)
							){
								if($debug)
									echo '[D] Child process limit ('.$children_limit.') reached - waiting'.PHP_EOL;

								while(pcntl_waitpid(0, $fork_status) !== -1);
							}
						}
						else
						{
							if($debug)
								echo '[D] Processing job '.$job_id.': '.$job_content.PHP_EOL;

							queue_worker_main(
								unserialize($job_content),
								[
									'pdo_handler'=>$pdo_handler,
									'table_name'=>$table_name,
									'worker_fork'=>$worker_fork,
									'children_limit'=>$children_limit,
									'debug'=>$debug
								]
							);
						}

						unset($queue[$job_id]);
					}
			}
		}

		public function __construct(
			$pdo_handler,
			string $table_name='queue_worker'
		){
			if(!is_object($pdo_handler))
				throw new queue_worker_exception('The pdo_handler parameter is not an object');

			$this->pdo_handler=$pdo_handler;
			$this->table_name=$table_name;
		}

		public function write($worker_input)
		{
			$query=$this->pdo_handler->prepare(''
			.	'INSERT INTO '.$this->table_name.'(payload) '
			.	'VALUES(:payload)'
			);

			if($query === false)
				throw new queue_worker_exception('PDO prepare error');

			if(!$query->execute([':payload'=>serialize($worker_input)]))
				throw new queue_worker_exception('PDO execute error - unable to send data to the queue server');

			return $this;
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
				$redis_handler, // object
				'string_path/to/functions.php',
				'string_key-prefix', // default: queue_worker__
				false, // bool_fork, enable parallel execution via PCNTL
				0, // int_children_limit, limit background processes
				false // bool_debug
			);
		 *
		 * Initialization:
			$queue_worker=new queue_worker_redis(
				$redis_handler, // required
				'key_prefix__' // default: queue_worker__
			);
		 */

		protected $redis_handler;
		protected $prefix;

		public static function start_worker(
			$redis_handler,
			string $worker_functions,
			string $prefix='queue_worker__',
			bool $worker_fork=false,
			int $children_limit=0,
			bool $debug=false
		){
			if(php_sapi_name() !== 'cli')
				throw new queue_worker_exception('This method is only for CLI');

			if(!is_object($redis_handler))
				throw new queue_worker_exception('The redis_handler parameter is not an object');

			if($worker_fork && (!function_exists('pcntl_fork')))
			{
				if($debug)
					echo '[D] PCNTL extension not available - forking disabled'.PHP_EOL;

				$worker_fork=false;
				$children_limit=0;
			}

			if($children_limit < 0)
				throw new queue_worker_exception('Child process limit cannot be negative');

			if($worker_functions !== null)
			{
				if(!file_exists($worker_functions))
					throw new queue_worker_exception($worker_functions.' not exist');

				if((include $worker_functions) === false)
					throw new queue_worker_exception($worker_functions.' inclusion error');
			}

			if(!function_exists('queue_worker_main'))
				throw new queue_worker_exception('queue_worker_main function not defined in '.$worker_functions);

			if($debug)
			{
				if($worker_functions === null)
					echo '[D] Functions file disabled'.PHP_EOL;
				else
					echo '[D] Functions path: '.$worker_functions.PHP_EOL;

				if($worker_fork)
				{
					echo '[D] Forking enabled'.PHP_EOL;

					if($children_limit === 0)
						echo '[D] Child process limit disabled'.PHP_EOL;
					else
						echo '[D] Child process limit enabled: '.$children_limit.PHP_EOL;
				}
				else
					echo '[D] Forking disabled'.PHP_EOL;
			}

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

			while(true)
			{
				$queue=[];
				$iterator=null;

				do
				{
					try {
						$keys=$redis_handler->scan($iterator, $prefix.'*');
					} catch(Throwable $error) {
						if($debug)
							echo '[D] Caught Redis error: '.$error->getMessage().PHP_EOL;

						$keys=false;
					}

					if($keys === false)
						break;

					foreach($keys as $key)
					{
						$queue[]=$redis_handler->get($key);
						$redis_handler->del($key);
					}
				}
				while($iterator > 0);

				if(empty($queue))
				{
					if($debug)
						echo '[D] No jobs to do - waiting'.PHP_EOL;

					if(empty(static::$children_pids))
						sleep(5);
					else
					{
						if($debug)
							echo '[D]  Background processes are running - not waiting'.PHP_EOL;

						usleep(500000); // 0.5s
					}
				}
				else
					foreach($queue as $job_id=>$job_content)
					{
						if($worker_fork)
						{
							$child_pid=pcntl_fork();

							if($child_pid === -1)
							{
								if($debug)
								{
									echo '[D][E] Fork error, job content: '.$job_content.PHP_EOL;
									echo '[D] Executing this job sequentially'.PHP_EOL;
								}

								queue_worker_main(
									unserialize($job_content),
									[
										'redis_handler'=>$redis_handler,
										'worker_fork'=>false,
										'children_limit'=>$children_limit,
										'debug'=>$debug
									]
								);
							}
							else if($child_pid === 0)
							{
								if($debug)
									echo '[D] Processing job '.$job_id.': '.$job_content.PHP_EOL;

								queue_worker_main(
									unserialize($job_content),
									[
										'redis_handler'=>$redis_handler,
										'worker_fork'=>$worker_fork,
										'children_limit'=>$children_limit,
										'debug'=>$debug
									]
								);

								sleep(1);
								exit();
							}
							else
								static::$children_pids[$child_pid]=$child_pid;

							if(
								($child_pid !== -1) &&
								($children_limit !== 0) &&
								(count(static::$children_pids) === $children_limit)
							){
								if($debug)
									echo '[D] Child process limit ('.$children_limit.') reached - waiting'.PHP_EOL;

								while(pcntl_waitpid(0, $fork_status) !== -1);
							}
						}
						else
						{
							if($debug)
								echo '[D] Processing job '.$job_id.': '.$job_content.PHP_EOL;

							queue_worker_main(
								unserialize($job_content),
								[
									'redis_handler'=>$redis_handler,
									'worker_fork'=>$worker_fork,
									'children_limit'=>$children_limit,
									'debug'=>$debug
								]
							);
						}

						unset($queue[$job_id]);
					}
			}
		}

		public function __construct(
			$redis_handler,
			string $prefix='queue_worker__'
		){
			if(!is_object($redis_handler))
				throw new queue_worker_exception('The redis_handler parameter is not an object');

			$this->redis_handler=$redis_handler;
			$this->prefix=$prefix;
		}

		public function write($worker_input)
		{
			if($this->redis_handler->set(
				$this->prefix.strtr(microtime(false), ' ', '_'),
				serialize($worker_input)
			) === false)
				throw new queue_worker_exception('Unable to send data to the queue server');

			return $this;
		}
	}
?>