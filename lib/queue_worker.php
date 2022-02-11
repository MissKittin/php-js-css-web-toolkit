<?php
	class queue_worker
	{
		/*
		 * The Worker
		 * She is not afraid of any job
		 *
		 * Warning:
		 *  only for *nix systems
		 *  posix extension is recommended (for queue server)
		 *  pcntl extension is optional (for queue server)
		 *   if it is not available the fork option will be turned off automatically
		 *
		 * Note:
		 *  the server can execute jobs in parallel: using several instances
		 *   listening to one fifo or/and using the fork option flag
		 *  if the fork fails, the server will execute the job sequentially
		 *
		 * Queue server start:
			queue_worker::start_worker(
				string_path_to_fifo,
				string_path_to_functions_php,
				bool_fork=false, // enable parallel execution via PCNTL
				int_children_limit=0, // limit background processes
				bool_recreate_fifo=true, // use this if you want to run multiple instances
				bool_debug=false
			)
		 *
		 * Initialization:
		 *  $queue_worker=new queue_worker('./tmp/queue_worker.fifo')
		 *
		 * Example usage: send jobs to the server immediately
			$queue_worker
				->write([
					'name'=>'John',
					'file'=>'./tmp/john',
					'mail'=>'john@example.com'
				])
				->write([
					'name'=>'Michael',
					'file'=>'./tmp/michael',
					'mail'=>'michael@example.com'
				])
		 *
		 * Example usage: add jobs to the queue
			$queue_worker
				->add_to_queue([
					'name'=>'John',
					'file'=>'./tmp/john',
					'mail'=>'john@example.com'
				])
				->add_to_queue([
					'name'=>'Michael',
					'file'=>'./tmp/michael',
					'mail'=>'michael@example.com'
				]);
			$queue_worker->write_queue(); // optional, will be executed automatically on unset() or shutdown
		 *
		 * Example functions.php:
			// Here you can include libraries

			function queue_worker_main($input_data, $worker_meta)
			{
				// This function must be defined

				echo 'Worker: Queue worker fifo: '.$worker_meta['worker_fifo'].PHP_EOL;

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
		 *
		 * Source:
		 *  https://web.archive.org/web/20120416013625/http://squirrelshaterobots.com/programming/php/building-a-queue-server-in-php-part-3-accepting-input-from-named-pipes/
		 */

		protected static $children_pids=array();

		protected $worker_fifo;
		protected $queue=array();

		public static function start_worker(
			string $worker_fifo,
			string $worker_functions,
			bool $worker_fork=false,
			int $children_limit=0,
			bool $recreate_fifo=true,
			bool $debug=false
		){
			if(php_sapi_name() !== 'cli')
				throw new Exception('This method is only for CLI');

			if($worker_fork && (!function_exists('pcntl_fork')))
			{
				if($debug)
					echo '[D] PCNTL extension not available - forking disabled'.PHP_EOL;

				$worker_fork=false;
				$children_limit=0;
			}

			if($children_limit < 0)
				throw new Exception('Child process limit cannot be negative');

			if($worker_functions !== null)
			{
				if(!file_exists($worker_functions))
					throw new Exception($worker_functions.' not exist');

				if((include $worker_functions) === false)
					throw new Exception($worker_functions.' inclusion error');
			}

			if(!function_exists('queue_worker_main'))
				throw new Exception('queue_worker_main function not defined in '.$worker_functions);

			if($recreate_fifo && file_exists($worker_fifo))
				if(!unlink($worker_fifo))
					throw new Exception('Unable to remove stale file');

			if(file_exists($worker_fifo))
			{
				if(is_dir($worker_fifo))
					throw new Exception($worker_fifo.' is a directory');
			}
			else
			{
				if(!function_exists('posix_mkfifo'))
					throw new Exception('posix extension not loaded - unable to create fifo');

				if(!posix_mkfifo($worker_fifo, 0666))
					throw new Exception('Unable to create '.$worker_fifo);
			}

			$worker_input=fopen($worker_fifo, 'r+');
			if(!$worker_input)
				throw new Exception('Fifo opening error');

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
				$queue=array();

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
				throw new Exception($worker_fifo.' not exist');
			if(is_dir($this->worker_fifo))
				throw new Exception($worker_fifo.' is a directory');

			$this->worker_fifo=realpath($worker_fifo);
		}
		public function __destruct()
		{
			$this->write_queue();
		}

		public function write($worker_input)
		{
			if(file_put_contents($this->worker_fifo, serialize($worker_input).PHP_EOL) === false)
				throw new Exception('Unable to send data to the queue server');

			return $this;
		}
		public function add_to_queue($worker_input)
		{
			$this->queue[]=$worker_input;
			return $this;
		}
		public function write_queue()
		{
			if(!empty($this->queue))
				foreach($this->queue as $job)
					$this->write($job);

			return $this;
		}
	}
?>