<?php
	class ci_zip_exception extends Exception {}
	class ci_zip
	{
		/*
		 * Create zip file in memory
		 *
		 * Warning:
		 *  zlib extension is required
		 *
		 * Note:
		 *  throws an ci_zip_exception on error
		 *
		 * Quick usage: send http headers, create and send the zip
			echo ci_zip::set_headers('string_my-archive-filename.zip', 'String Example description') // second arg is optional
			->	set_compression(9) // 0-9, optional, 0 -> not compressed
			->	add_data('string_file_name', 'string_file_content') // add files to the zip
			->	add_data('string_dir_name/file_name', 'string_file_content') // add to subdirectory
			->	add_data([ // multiple files
					'string_file_name'=>'string_file_content',
					'string_dir_name/file_name'=>'string_file_content'
				])
			->	add_dir('string_empty-directory-name')
			->	add_dir('string_directory-name/empty-subdirectory-name')
			->	get_zip(); // send the zip content to the client
		 *
		 * Methods:
		 *  set_compression [returns self]
		 *   set file compression, from 0 to 9
		 *   0 - no compression, 9 - the strongest compression
				$zip=new ci_zip();
				$zip->set_compression(4);
		 *  add_data [returns self]
		 *   adds data to the Zip archive
				$zip=new ci_zip();
				$zip->add_data('string_file_name', 'string_file_content');
				$zip->add_data([
					'string_file_name_a'=>'string_file_content_a',
					'string_file_name_b'=>'string_file_content_b',
					'string_file_name_n'=>'string_file_content_n'
				]);
		 *  add_dir [returns self]
		 *   permits you to add an empty directory
				$zip=new ci_zip();
				$zip->add_dir('string_empty-directory-name');
		 *  read_file [returns bool]
		 *   permits you to compress a file that already exists somewhere on your server
				$zip=new ci_zip();
				$zip->read_file('file_a.txt');
				$zip->read_file('file_b.txt', 'another_name.txt');
		 *  read_dir [returns bool]
		 *   permits you to compress a directory and its contents that already exists somewhere on your server
				$zip=new ci_zip();
				$zip->read_dir('dirname');
				$zip->read_dir('/path/to/directory', false, '/path/to/');
				$zip->read_dir('Z:\\directory', false, 'Z:/');
		 *  get_zip [returns string]
		 *   returns the Zip-compressed file data
				echo $zip->get_zip();
		 *  archive [returns bool]
		 *   writes the Zip-encoded file to a directory on your server
				$zip->archive('path/to/generated.zip')
		 *
		 * Source: https://gitlab.softwarelibre.gob.bo/cristiamherrera/CodeigniterPMGM/-/blob/ce2db11db903d914752b0a236f93ed52539669ff/system/libraries/Zip.php
		 * License: MIT
		 */

		/*
		 * This class is based on a library I found at Zend:
		 * http://www.zend.com/codex.php?id=696&single=1
		 *
		 * The original library is a little rough around the edges so I
		 * refactored it and added several additional methods -- Rick Ellis
		 */

		/*
		 * CodeIgniter
		 *
		 * An open source application development framework for PHP
		 *
		 * This content is released under the MIT License (MIT)
		 *
		 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
		 *
		 * Permission is hereby granted, free of charge, to any person obtaining a copy
		 * of this software and associated documentation files (the "Software"), to deal
		 * in the Software without restriction, including without limitation the rights
		 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		 * copies of the Software, and to permit persons to whom the Software is
		 * furnished to do so, subject to the following conditions:
		 *
		 * The above copyright notice and this permission notice shall be included in
		 * all copies or substantial portions of the Software.
		 *
		 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
		 * THE SOFTWARE.
		 */

		protected $zipdata='';
		protected $directory='';
		protected $entries=0;
		protected $file_num=0;
		protected $offset=0;
		protected $now;
		protected $compression_level=2;

		public static function set_headers(
			string $file_name,
			?string $description=null
		){
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$file_name);

			if($description !== null)
				header('Content-Description: '.$description);

			return new static();
		}

		public function __construct()
		{
			if(!function_exists('gzcompress'))
				throw new ci_zip_exception(
					'zlib extension is not loaded'
				);

			$this->now=time();
		}

		protected function _get_mod_time($dir)
		{
			$date=$this->now;

			if(file_exists($dir))
				$date=filemtime($dir);

			$date=getdate($date);

			return [
				'file_mtime'=>(
					($date['hours'] << 11) +
					($date['minutes'] << 5) +
					$date['seconds'] /
					2
				),
				'file_mdate'=>(
					(($date['year']-1980) << 9) +
					($date['mon'] << 5) +
					$date['mday']
				)
			];
		}
		protected function _add_data(
			$filepath,
			$data,
			$file_mtime,
			$file_mdate
		){
			$filepath=strtr($filepath, '\\', '/');
			$uncompressed_size=strlen($data);
			$crc32=crc32($data);
			$gzdata=substr(
				gzcompress(
					$data,
					$this->compression_level
				),
				2, -4
			);
			$compressed_size=strlen($gzdata);

			$this->zipdata.=''
			.	"\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00"
			.	pack('v', $file_mtime)
			.	pack('v', $file_mdate)
			.	pack('V', $crc32)
			.	pack('V', $compressed_size)
			.	pack('V', $uncompressed_size)
			.	pack('v', strlen($filepath))
			.	pack('v', 0)
			.	$filepath
			.	$gzdata;

			$this->directory.=''
			.	"\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00"
			.	pack('v', $file_mtime)
			.	pack('v', $file_mdate)
			.	pack('V', $crc32)
			.	pack('V', $compressed_size)
			.	pack('V', $uncompressed_size)
			.	pack('v', strlen($filepath))
			.	pack('v', 0)
			.	pack('v', 0)
			.	pack('v', 0)
			.	pack('v', 0)
			.	pack('V', 32)
			.	pack('V', $this->offset)
			.	$filepath;

			$this->offset=strlen($this->zipdata);
			++$this->entries;
			++$this->file_num;
		}

		public function set_compression(int $compression_level)
		{
			if(
				($compression_level < 0) ||
				($compression_level > 9)
			)
				throw new ci_zip_exception(
					'Compression level must be between 0 and 9'
				);

			$this->compression_level=$compression_level;

			return $this;
		}
		public function add_dir($directory)
		{
			if(is_string($directory))
				$directory=[$directory];

			if(!is_iterable($directory))
				throw new ci_zip_exception(
					'The directory parameter must be a string or iterable'
				);

			foreach($directory as $dir)
			{
				if(!preg_match('|.+/$|', $dir))
					$dir.='/';

				$dir_time=$this->_get_mod_time($dir);
				$dir=strtr($dir, '\\', '/');

				$this->zipdata.=''
				.	"\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00"
				.	pack('v', $dir_time['file_mtime'])
				.	pack('v', $dir_time['file_mdate'])
				.	pack('V', 0)
				.	pack('V', 0)
				.	pack('V', 0)
				.	pack('v', strlen($dir))
				.	pack('v', 0)
				.	$dir
				.	pack('V', 0)
				.	pack('V', 0)
				.	pack('V', 0);

				$this->directory.=''
				.	"\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00"
				.	pack('v', $dir_time['file_mtime'])
				.	pack('v', $dir_time['file_mdate'])
				.	pack('V',0)
				.	pack('V',0)
				.	pack('V',0)
				.	pack('v', strlen($dir))
				.	pack('v', 0)
				.	pack('v', 0)
				.	pack('v', 0)
				.	pack('v', 0)
				.	pack('V', 16)
				.	pack('V', $this->offset)
				.	$dir;

				$this->offset=strlen($this->zipdata);
				++$this->entries;
			}

			return $this;
		}
		public function add_data($filepath, $data=null)
		{
			if(is_array($filepath))
			{
				foreach($filepath as $path=>$data)
				{
					$file_data=$this->_get_mod_time($path);
					$this->_add_data(
						$path,
						$data,
						$file_data['file_mtime'],
						$file_data['file_mdate']
					);
				}

				return $this;
			}

			if(!is_string($filepath))
				throw new ci_zip_exception(
					'The filepath parameter must be an array or a string'
				);

			$file_data=$this->_get_mod_time($filepath);
			$this->_add_data(
				$filepath,
				$data,
				$file_data['file_mtime'],
				$file_data['file_mdate']
			);

			return $this;
		}
		public function read_file(
			string $path,
			$archive_filepath=false
		){
			if(
				(!is_string($archive_filepath)) &&
				(!is_bool($archive_filepath))
			)
				throw new ci_zip_exception(
					'The archive_filepath parameter must be a string or boolean'
				);

			if(!file_exists($path))
				return false;

			$data=file_get_contents($path);

			if($data === false)
				return false;

			if($archive_filepath === false)
			{
				$name=strtr($path, '\\', '/');

				if($archive_filepath === false)
					$name=preg_replace('|.*/(.+)|', '\\1', $name);
			}
			else
				$name=strtr($archive_filepath, '\\', '/');

			$this->add_data($name, $data);

			return true;
		}
		public function read_dir(
			string $path,
			bool $preserve_filepath=true,
			?string $root_path=null
		){
			$path=rtrim($path, '/\\').'/';
			$fp=opendir($path);

			if($fp === false)
				return false;

			if($root_path === null)
				$root_path=strtr(dirname($path), '\\', '/').'/';

			while(($file=readdir($fp)) !== false)
			{
				if($file[0] === '.')
					continue;

				if(is_dir($path.$file))
				{
					$this->{__FUNCTION__}(
						$path.$file.'/',
						$preserve_filepath,
						$root_path
					);

					continue;
				}

				$data=file_get_contents($path.$file);

				if($data !== false)
				{
					$name=strtr($path, '\\', '/');

					if($preserve_filepath === false)
						$name=str_replace($root_path, '', $name);

					$this->add_data($name.$file, $data);
				}
			}

			closedir($fp);

			return true;
		}
		public function get_zip()
		{
			if($this->entries === 0)
				return false;

			return ''
			.	$this->zipdata
			.	$this->directory
			.	"\x50\x4b\x05\x06\x00\x00\x00\x00"
			.	pack('v', $this->entries)
			.	pack('v', $this->entries)
			.	pack('V', strlen($this->directory))
			.	pack('V', strlen($this->zipdata))
			.	"\x00\x00";
		}
		public function archive(string $filepath)
		{
			$fp=fopen($filepath, 'w+b');

			if($fp === false)
				return false;

			flock($fp, LOCK_EX);

			for(
				$result=0, $written=0, $data=$this->get_zip(), $length=strlen($data);
				$written<$length;
				$written+=$result
			){
				$result=fwrite($fp, substr($data, $written));

				if($result === false)
					break;
			}

			flock($fp, LOCK_UN);
			fclose($fp);

			return is_int($result);
		}
	}
?>