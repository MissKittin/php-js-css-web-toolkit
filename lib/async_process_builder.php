<?php
	class async_process_builder_exception extends Exception {}
	class async_process_builder
	{
		/*
		 * Async Process Builder
		 * proc_* wrapper and standard input-output-error interface
		 *
		 * Warning:
		 *  proc_* and stream_* functions are required
		 *  reading methods wait for stream
		 *  and may hang if nothing is returned
		 *  you have been warned
		 *
		 * Methods:
		 *  Constructor parameters:
		 *   string_command
		 *   bool_inherit_env
		 *    if false, the process will start without environment variables
		 *    default: true
		 *  Setting up:
		 *   chdir(string_directory) [returns this]
		 *    set working directory
		 *    note: to use, the process cannot be started
		 *    note: if not used, the start method will use getcwd
		 *   setenv(string_name, string_value) [returns this]
		 *    set environment variable for process
		 *   setenv(string_name) [returns bool]
		 *    unset environment variable for process
		 *    returns true if the variable existed
		 *   set_pty() [returns this]
		 *    use pseudo terminal instead of pipes
		 *   unset_pty() [returns this]
		 *    use pipes instead of pseudo terminal (default behavior)
		 *  Starting:
		 *   start()
		 *    throws an async_process_builder_exception if the process cannot be started
		 *   restart()
		 *    alias for stop() and start()
		 *  Writing:
		 *   write(string_input) [returns int|false]
		 *    write to stdin
		 *    note: to use, the process must be started
		 *    returns the number of bytes written, or false on failure
		 *    throws an async_process_builder_exception if stdin is closed
		 *   send_signal(int_signal) [returns bool]
		 *    note: this method is only for *nix
		 *  Reading:
		 *   close_stdin() [returns bool]
		 *    use this before calling read_all_out/read_all_err
		 *    throws an async_process_builder_exception if stdin is already closed
		 *   read_char_out() [returns string]
		 *   read_char_err() [returns string]
		 *    uses fgetc to read one character from stdout or stderr
		 *    throws an async_process_builder_exception if process is not running
		 *   read_until_char_out(char_char) [returns string]
		 *   read_until_char_err(char_char) [returns string]
		 *    uses fgetc in a loop until it encounters a specific character
		 *    note: char_char must be length === 1
		 *    throws an async_process_builder_exception if process is not running
		 *   read_line_out() [returns string]
		 *   read_line_err() [returns string]
		 *    uses fgets with trim to read one line from stdout or stderr
		 *    note: end char(s) must be PHP_EOL
		 *     (eg. "\n" for *nix or "\r\n" for windows)
		 *    throws an async_process_builder_exception if process is not running
		 *   read_bytes_out(int_bytes) [returns string]
		 *   read_bytes_err(int_bytes) [returns string]
		 *    uses fread to get n bytes from stdout or stderr
		 *    throws an async_process_builder_exception if process is not running
		 *   read_all_out() [returns string]
		 *   read_all_err() [returns string]
		 *    uses stream_get_contents to get full output from stdout or stderr
		 *    note: waits for EOF - will return the result
		 *     only after the program ends
		 *    throws an async_process_builder_exception if stdin is not closed
		 *  Status checking:
		 *   getenv(string_name) [returns string]
		 *    get defined environment variable for process
		 *   get_pid() [returns int]
		 *    get process pid
		 *   process_started() [returns bool]
		 *    check if process is running
		 *   stdin_closed() [returns bool]
		 *    check if stdin has been closed
		 *   has_pty() [returns bool]
		 *    check if the process has a pseudo terminal
		 *  Stopping:
		 *   stop() [returns int|false]
		 *    uses proc_close
		 *    throws an async_process_builder_exception if stdin/stdout/stderr cannot be closed
		 *  Misc:
		 *   [static] get_exit_code(int_code)  [returns string]
		 *    translate exit code to description
		 *
		 * Example usage - talking:
			$process=new async_process_builder('./my-program');
			$process->setenv('VARIABLE', 'VALUE');
			$process->start();
			if($process->process_started())
			{
				$process->write('input data');
				sleep(1);
				$output_data_a=$process->$process->read_line_out();
				$process->write('another input data');
				sleep(1);
				$output_data_b=$process->read_line_out();
				if($process->process_started())
					$process->stop();
			}
		 * Example usage - input-output:
			$process=new async_process_builder('./my-program');
			$process->setenv('VARIABLE', 'VALUE');
			$process->start();
			if($process->process_started())
			{
				$process->write('input data');
				sleep(1);
				$process->close_stdin();
				$output_data=$process->read_all_out();
				if($process->process_started())
					$process->stop();
			}
		 *
		 * Source - exit codes:
		 *  https://github.com/schmittjoh/Process/blob/master/Process.php
		 */

		protected static $exit_codes=[
			0=>'OK',
			1=>'General error',
			2=>'Misuse of shell builtins',

			126=>'Invoked command cannot execute',
			127=>'Command not found',
			128=>'Invalid exit argument',

			129=>'Hangup',
			130=>'Interrupt',
			131=>'Quit and dump core',
			132=>'Illegal instruction',
			133=>'Trace/breakpoint trap',
			134=>'Process aborted',
			135=>'Bus error: "access to undefined portion of memory object"',
			136=>'Floating point exception: "erroneous arithmetic operation"',
			137=>'Kill (terminate immediately)',
			138=>'User-defined 1',
			139=>'Segmentation violation',
			140=>'User-defined 2',
			141=>'Write to pipe with no one reading',
			142=>'Signal raised by alarm',
			143=>'Termination (request to terminate)',
			145=>'Child process terminated, stopped (or continued*)',
			146=>'Continue if stopped',
			147=>'Stop executing temporarily',
			148=>'Terminal stop signal',
			149=>'Background process attempting to read from tty ("in")',
			150=>'Background process attempting to write to tty ("out")',
			151=>'Urgent data available on socket',
			152=>'CPU time limit exceeded',
			153=>'File size limit exceeded',
			154=>'Signal raised by timer counting virtual time: "virtual timer expired"',
			155=>'Profiling timer expired',
			157=>'Pollable event',
			159=>'Bad syscall'
		];

		protected $command;
		protected $env=[];
		protected $cwd=null;
		protected $process_descriptors_pipe=[
			0=>['pipe', 'r'],
			1=>['pipe', 'w'],
			2=>['pipe', 'w']
		];
		protected $process_descriptors_pty=[
			0=>['pty'],
			1=>['pty'],
			2=>['pty']
		];
		protected $process_has_pty=false;
		protected $process_handler=null;
		protected $process_pipes=null;
		protected $_read_until_char_debug=false; // change by inheritance

		public static function get_exit_code(int $code)
		{
			if(!isset(self::$exit_codes[$code]))
				return 'Undefined';

			return self::$exit_codes[$code];
		}

		public function __construct(string $command, bool $inherit_env=true)
		{
			if(!function_exists('proc_open'))
				throw new async_process_builder_exception('proc_open function is not available');

			$this->command=escapeshellcmd($command);

			if($inherit_env)
				$this->env=getenv();
		}
		public function __destruct()
		{
			if($this->process_started())
				$this->stop();
		}
		public function __clone()
		{
			$this->process_handler=null;
			$this->process_pipes=null;
		}
		public function __wakeup()
		{
			throw new async_process_builder_exception(static::class.': unserialization is not allowed');
		}

		protected function read_char(int $descriptor)
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			return fgetc($this->process_pipes[$descriptor]);
		}
		protected function read_until_char(int $descriptor, string $end_char)
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			if((!isset($end_char[0])) || isset($end_char[1])) // (strlen($end_char) !== 1)
				throw new async_process_builder_exception('The passed string contains more than one character');

			$output='';

			do
			{
				$input=fgetc($this->process_pipes[$descriptor]);
				$output.=$input;

				if($this->_read_until_char_debug)
				{
					if($input === $end_char)
						echo '(['.$input.'])';
					else
						echo '('.$input.')';
				}
			}
			while($input !== $end_char);

			return substr($output, 0, -1);
		}
		protected function read_line(int $descriptor)
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			return trim(fgets($this->process_pipes[$descriptor]));
		}
		protected function read_bytes(int $descriptor, int $bytes)
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			return fread($this->process_pipes[$descriptor], $bytes);
		}
		protected function read_all(int $descriptor)
		{
			// if(!$this->process_started()) cannot be used:
			// if one descriptor is read, the process will terminate and the other cannot be read

			if(!isset($this->process_pipes[$descriptor]))
				throw new async_process_builder_exception('Descriptor '.$descriptor.' does not exists (process not started?)');

			if(
				is_resource($this->process_pipes[$descriptor]) &&
				(stream_get_meta_data($this->process_pipes[$descriptor]) === false)
			)
				throw new async_process_builder_exception('Descriptor '.$descriptor.' is not a stream');

			if(!$this->stdin_closed())
				throw new async_process_builder_exception('Standard input is not closed');

			return stream_get_contents($this->process_pipes[$descriptor]);
		}

		public function read_char_out()
		{
			return $this->read_char(1);
		}
		public function read_char_err()
		{
			return $this->read_char(2);
		}
		public function read_until_char_out(string $char)
		{
			return $this->read_until_char(1, $char);
		}
		public function read_until_char_err(string $char)
		{
			return $this->read_until_char(2, $char);
		}
		public function read_line_out()
		{
			return $this->read_line(1);
		}
		public function read_line_err()
		{
			return $this->read_line(2);
		}
		public function read_bytes_out(int $bytes)
		{
			return $this->read_bytes(1, $bytes);
		}
		public function read_bytes_err(int $bytes)
		{
			return $this->read_bytes(2, $bytes);
		}
		public function read_all_out()
		{
			return $this->read_all(1);
		}
		public function read_all_err()
		{
			return $this->read_all(2);
		}
		public function write(string $input)
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			if($this->stdin_closed())
				throw new async_process_builder_exception('Standard input is closed');

			return fwrite($this->process_pipes[0], $input.PHP_EOL);
		}

		public function chdir(string $directory)
		{
			if($this->process_started())
				throw new async_process_builder_exception('The process is already running');

			if(!is_dir($directory))
				throw new async_process_builder_exception($directory.' is not a directory');

			$this->cwd=$directory;

			return $this;
		}
		public function getenv(string $name)
		{
			if(isset($this->env[$name]))
				return $this->env[$name];

			return null;
		}
		public function setenv(string $name, string $value=null)
		{
			if($this->process_started())
				throw new async_process_builder_exception('The process is already running');

			if($value === null)
			{
				if(isset($this->env[$name]))
				{
					unset($this->env[$name]);
					return true;
				}

				return false;
			}

			$this->env[$name]=$value;

			return $this;
		}
		public function set_pty()
		{
			if($this->process_started())
				throw new async_process_builder_exception('The process is already running');

			$this->process_has_pty=true;

			return $this;
		}

		public function get_pid()
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			return proc_get_status($this->process_handler)['pid'];
		}
		public function process_started()
		{
			if(!is_resource($this->process_handler))
				return false;

			$process_status=proc_get_status($this->process_handler);

			if(!isset($process_status['running']))
				return false;

			if(!$process_status['running'])
				return false;

			return true;
		}
		public function stdin_closed()
		{
			if(!isset($this->process_pipes[0]))
				throw new async_process_builder_exception('Standard input descriptor does not exists (process not started?)');

			if(!is_resource($this->process_pipes[0]))
				return true;

			return (stream_get_meta_data($this->process_pipes[0]) === false);
		}
		public function has_pty()
		{
			return $this->process_has_pty;
		}

		public function start()
		{
			if($this->process_started())
				throw new async_process_builder_exception('The process is already running');

			if($this->cwd === null)
				$this->cwd=getcwd();

			$process_descriptors=$this->process_descriptors_pipe;
			if($this->process_has_pty)
				$process_descriptors=$this->process_descriptors_pty;

			$this->process_handler=proc_open(
				$this->command,
				$process_descriptors,
				$this->process_pipes,
				$this->cwd,
				$this->env
			);

			if(!is_resource($this->process_handler))
				throw new async_process_builder_exception('Process cannot be started');

			foreach($this->process_pipes as $pipe)
				stream_set_blocking($pipe, false);
		}
		public function restart()
		{
			if($this->process_started())
				$this->stop();

			$this->start();
		}
		public function send_signal(int $signal)
		{
			if(!$this->process_started())
				throw new async_process_builder_exception('The process is not running');

			return proc_terminate($this->process_handler, $signal);
		}
		public function close_stdin()
		{
			if($this->stdin_closed())
				throw new async_process_builder_exception('Standard input is already closed');

			return fclose($this->process_pipes[0]);
		}
		public function stop()
		{
			if(!$this->stdin_closed())
				$this->close_stdin();

			if(!fclose($this->process_pipes[1]))
				throw new async_process_builder_exception('Cannot close standard output');

			if(!fclose($this->process_pipes[2]))
				throw new async_process_builder_exception('Cannot close standard error output');

			if($this->process_started())
				return proc_close($this->process_handler);

			return false;
		}
	}
?>