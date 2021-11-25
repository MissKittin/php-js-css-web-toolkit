<?php
	/*
	 * A toy that converts inclusion to a single file blob
	 * and prints it to stdout
	 *
	 * Note:
	 *  this toy also processes include () in child files
	 *  supports include 'file'; include('file'); and include ( 'file' ) ;
	 *
	 * Warning:
	 *  include $variable; is not supported
	 *   only include 'string'; or include "string";
	 *   include $variable; will be ignored
	 *  _once is treated as a normal inclusion
	 *  you must change to the correct directory for the relative paths to be valid
	 *  -debug will also enable -no-compress
	 *  check_var.php library is required
	 *  strip_php_comments.php library is required
	 */

	include __DIR__.'/../lib/check_var.php';
	include __DIR__.'/../lib/strip_php_comments.php';

	if(check_argv('-debug'))
	{
		function open_file($matches)
		{
			if(!file_exists($matches[2]))
				die($matches[2].' not exists');
			return ' /* start include file '.$matches[2].' */ '.PHP_EOL.'?>'.include2blob(strip_php_comments(file_get_contents($matches[2]))).'<?php /* end include file '.$matches[2].' */ '.PHP_EOL;
		}
		$_SERVER['argv'][]='-no-compress';
	}
	else
	{
		function open_file($matches)
		{
			if(!file_exists($matches[2]))
				die($matches[2].' not exists');
			return '?>'.include2blob(strip_php_comments(file_get_contents($matches[2]))).'<?php ';
		}
	}
	function include2blob($file_content)
	{
		$file_content=preg_replace_callback('/include\s*\(?\s*(\'|")(.*)(\'|")\s*\)?\s*;/', 'open_file', $file_content);
		$file_content=preg_replace_callback('/include_once\s*\(?\s*(\'|")(.*)(\'|")\s*\)?\s*;/', 'open_file', $file_content);
		$file_content=preg_replace_callback('/require\s*\(?\s*(\'|")(.*)(\'|")\s*\)?\s*;/', 'open_file', $file_content);
		$file_content=preg_replace_callback('/require_once\s*\(?\s*(\'|")(.*)(\'|")\s*\)?\s*;/', 'open_file', $file_content);
		return $file_content;
	}
	class global_variable_streamer
	{
		// Source: https://www.php.net/manual/en/stream.streamwrapper.example-1.php

		private $variable_name;
		private $current_position=0;

		public function stream_open($path, $mode, $options, &$opened_path)
		{
			$this->variable_name=substr($path, 5);
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
			if($option === STREAM_META_TOUCH)
				if(isset($GLOBALS[substr($path, 5)]))
					return true;
			return false;
		}
		public function stream_stat() {}
	}

	if(!isset($argv[1]))
	{
		echo 'No file name given'.PHP_EOL;
		echo 'Usage: include2blob.php path/to/file.php [-debug] [-no-compress]'.PHP_EOL;
		exit(1);
	}
	if(!file_exists($argv[1]))
		die($argv[1].' not exists');

	$file_content=include2blob(strip_php_comments(file_get_contents($argv[1])));

	if(!check_argv('-no-compress'))
	{
		stream_wrapper_register('vs', 'global_variable_streamer');
		$file_content=php_strip_whitespace('vs://file_content');
		$file_content=preg_replace('/ \?><\?php\s*/', ' ', $file_content);
		$file_content=preg_replace('/<\?php\s+/', '<?php ', $file_content);
		$file_content=preg_replace('/<\?php\s*\?>/', '', $file_content);
	}

	echo $file_content;
?>