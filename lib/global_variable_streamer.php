<?php
	class global_variable_streamer
	{
		/*
		 * A helper for functions that only support writing output to a file
		 * Use a global variable as a file stream
		 * Registers the gvs:// wrapper by default
		 *
		 * Warning:
		 *  if you perform an operation, e.g.
		 *   file_put_contents($cache_file, php_strip_whitespace($cache_file))
		 *  and $cache_file will contain string './path/to/file.php'
		 *  then this operation will be successful
		 *  but if the $cache_file contains a string e.g. 'gvs://my_global_variable'
		 *  (gvs is the protocol for global_variable_streamer),
		 *  the data will be saved incorrectly
		 *
		 * Usage:
			global_variable_streamer::register_wrapper('gvs');

			$GLOBALS['my_var']='';
			$file=fopen('gvs://my_var', 'r+');
			for($i=0; $i<=10; ++$i)
				fwrite($file, 'line '.$i.PHP_EOL);

			rewind($file);
			while(!feof($file))
				echo 'read: '.fgets($file);

			fclose($file);
		 *
		 * Source:
		 *  https://www.php.net/manual/en/stream.streamwrapper.example-1.php
		 */

		protected static $protocol_length=null;

		protected $variable_name;
		protected $current_position=0;

		public function __construct()
		{
			if(static::$protocol_length === null)
				throw new Exception('Use '.static::class.'::register_wrapper() instead');
		}

		public static function register_wrapper(string $protocol)
		{
			if(!stream_wrapper_register($protocol, static::class))
				throw new Exception('Cannot register '.$protocol.' wrapper');

			static::$protocol_length=strlen($protocol);
		}

		public function stream_open($path, $mode, $options, &$opened_path)
		{
			$this->variable_name=substr($path, static::$protocol_length+3);
			return true;
		}
		public function stream_read($count)
		{
			$content=substr($GLOBALS[$this->variable_name], $this->current_position, $count);
			$this->current_position+=strlen($content);

			return $content;
		}
		public function stream_write($data)
		{
			$data_size=strlen($data);

			$GLOBALS[$this->variable_name]=
				substr($GLOBALS[$this->variable_name], 0, $this->current_position)
				.$data.
				substr($GLOBALS[$this->variable_name], $this->current_position+$data_size)
			;

			$this->current_position+=$data_size;

			return $data_size;
		}
		public function stream_tell()
		{
			return $this->current_position;
		}
		public function stream_eof()
		{
			if($this->current_position >= strlen($GLOBALS[$this->variable_name]))
				return true;

			return false;
		}
		public function stream_seek($offset, $whence)
		{
			switch($whence)
			{
				case SEEK_SET:
					if(($offset < strlen($GLOBALS[$this->variable_name])) && ($offset >= 0))
					{
						$this->current_position=$offset;
						return true;
					}
				break;
				case SEEK_CUR:
					if($offset >= 0)
					{
						$this->current_position+=$offset;
						return true;
					}
				break;
				case SEEK_END:
					$new_position=strlen($GLOBALS[$this->variable_name])+$offset;

					if($new_position >= 0)
					{
						$this->current_position=$new_position;
						return true;
					}
				break;
			}

			return false;
		}
		public function stream_metadata($path, $option, $variable)
		{
			if(($option === STREAM_META_TOUCH) && isset($GLOBALS[substr($path, 5)]))
				return true;

			return false;
		}
		public function stream_stat()
		{
			return false;
		}
	}
?>