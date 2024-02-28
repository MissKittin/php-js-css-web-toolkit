<?php
	/*
	 * Simpleblog key-value database that can be edited in notepad
	 * original concept from the Bash SSG project
	 *
	 * Warning:
	 *  the larger the zip file for the simpleblog_db_zip, the longer it takes to load
	 *  simpleblog_db_zip requires the Zip extension
	 *  this is alpha version - use at your own risk
	 *
	 * Classes:
	 *  simpleblog_db
	 *   basic version
	 *  simpleblog_db_cache
	 *   simpleblog_db extension for cache (saves cpu)
	 *  simpleblog_db_zip
	 *   simpleblog_db packed in a zip archive (saves storage)
	 *
	 * Database layout:
	 *  each record is in the root directory, the key is the filename
	 *   and the value - the contents of the file
	 *  each record can have subrecords presented as a nested array
	 *  subrecords can also be nested
	 *
	 * Note:
	 *  db_path will always be converted to the real path
	 *  array values in the array from the read() method will always be strings
	 *  you can switch from simpleblog_db_cache to simpleblog_db
	 *   and - after cleaning the cache storage - from simpleblog_db to simpleblog_db_cache
	 *  simpleblog_db_zip opens the zip file on first operation
	 *  throws an simpleblog_db_exception on error
	 *
	 * Usage/Examples:
	 *  creating a database handler (basic version):
			$db=new simpleblog_db([
				'db_path'=>'./simpleblog_db'
			])
	 *  creating a database handler (cache-enabled version):
			$db=new simpleblog_db_cache([
				'db_path'=>'./simpleblog_db'
				'cache_path'=>'./simpleblog_db_cache'
			])
	 *  creating a database handler (zip version):
			$db=new simpleblog_db_zip([
				'db_path'=>'./simpleblog_db.zip',
				'db_compression'=>true // optional
			])
	 *  adding a record:
			$db->add('sample-record', [
				'name'=>'value',
				'subrecord'=>[
					'subrecord_name'=>'subrecord_value'
				]
			])
	 *  adding multiple records (optimizes simpleblog_db_zip performance):
			$db->add_bulk('sample-record', [
				'name'=>'value',
				'subrecord'=>[
					'subrecord_name'=>'subrecord_value'
				]
			]);
			$db->reopen_db();
	 *  record name change [returns bool]:
			$db->rename('sample-record', 'renamed-record')
	 *  edit a record (you can use also add_bulk):
			$db->add('sample-record', [
				'name'=>'new value'
			])
	 *  record list [returns array]:
			$db->list()
	 *  reading record [returns array]:
			$db->read('sample-record')
	 *  search for keys/subrecords [returns array]:
			$db->find('subrecord/subrecord_name')
	 *  delete a record:
			$db->delete('sample-record')
	 *
	 * Sources:
	 *  simpleblog_db_zip::array_flat() https://stackoverflow.com/a/9546302
	 *  simpleblog_db_zip::unflatten() https://stackoverflow.com/a/33855103
	 */

	class simpleblog_db_exception extends Exception {}
	class simpleblog_db
	{
		protected $constructor_params=['db_path'];
		protected $db_path;

		public function __construct(array $params)
		{
			foreach($this->constructor_params as $param)
				if(isset($params[$param]))
				{
					if(!is_string($params[$param]))
						throw new simpleblog_db_exception('The input array parameter '.$param.' is not a string');

					$this->$param=$params[$param];
				}
				else
					throw new simpleblog_db_exception('The '.$param.' parameter was not specified for the constructor');

			if(!is_dir($this->db_path))
				throw new simpleblog_db_exception('Wrong db_path or pointing to file');

			$this->db_path=realpath($this->db_path);
		}

		public function list()
		{
			$result=[];

			foreach(new DirectoryIterator($this->db_path) as $file)
				if(!$file->isDot())
					$result[]=$file->getFilename();

			return $result;
		}
		public function add(string $record_name, array $content)
		{
			$record_name=str_replace(['/..', '../'], '', $record_name);

			if($record_name === '')
				throw new simpleblog_db_exception('Record name cannot be empty');

			if(
				(!file_exists($this->db_path.'/'.$record_name)) &&
				(!mkdir($this->db_path.'/'.$record_name))
			)
				throw new simpleblog_db_exception('The database could not be saved');

			foreach($content as $key=>$value)
				if($value === null)
					$this->delete($record_name.'/'.$key);
				else if(is_array($value))
					(__METHOD__)($record_name.'/'.$key, $value);
				else
					if(file_put_contents($this->db_path.'/'.$record_name.'/'.$key, $value) === false)
						throw new simpleblog_db_exception('The database could not be saved');

			return null;
		}
		public function rename(string $old_name, string $new_name)
		{
			$old_name=str_replace(['/..', '../'], '', $old_name);
			$new_name=str_replace(['/..', '../'], '', $new_name);

			if(($old_name === '') || ($new_name === ''))
				throw new simpleblog_db_exception('Record name cannot be empty');

			$old_name=$this->db_path.'/'.$old_name;
			$new_name=$this->db_path.'/'.$new_name;

			if((!file_exists($old_name)) || file_exists($new_name))
				return false;

			if(!rename($old_name, $new_name))
				throw new simpleblog_db_exception('The database could not be saved');

			return true;
		}
		public function read(string $record_name)
		{
			$record_name=str_replace(['/..', '../'], '', $record_name);
			$record_content=[];

			foreach(array_slice(scandir($this->db_path.'/'.$record_name), 2) as $key)
				if(is_dir($this->db_path.'/'.$record_name.'/'.$key))
					$record_content[$key]=(__METHOD__)($record_name.'/'.$key);
				else
				{
					$record_content[$key]=file_get_contents($this->db_path.'/'.$record_name.'/'.$key);

					if($record_content[$key] === false)
						throw new simpleblog_db_exception('Database could not be read');
				}

			return $record_content;
		}
		public function delete(string $record_name)
		{
			$record_name=str_replace(['/..', '../'], '', $record_name);

			if($record_name === '')
				throw new simpleblog_db_exception('Record name cannot be empty');

			if(is_file($this->db_path.'/'.$record_name))
			{
				if(!unlink($this->db_path.'/'.$record_name))
					throw new simpleblog_db_exception('The database could not be saved');
			}
			else
			{
				foreach(new DirectoryIterator($this->db_path.'/'.$record_name) as $file)
					if(!$file->isDot())
					{
						if($file->isFile())
						{
							if(!unlink($file->getPathname()))
								throw new simpleblog_db_exception('The database could not be saved');
						}
						else
							(__METHOD__)(strtr(
								substr(
									$file->getPathname(),
									strlen($this->db_path)+1
								),
								'\\', '/'
							));
					}

				if(!rmdir($this->db_path.'/'.$record_name))
					throw new simpleblog_db_exception('The database could not be saved');
			}
		}
		public function find(string $path)
		{
			$found=[];

			foreach(
				new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$this->db_path,
						RecursiveDirectoryIterator::SKIP_DOTS+
						RecursiveDirectoryIterator::UNIX_PATHS
					)
				)
			as $file){
				$path_b=substr($file->getPathname(), strlen($this->db_path)+1);

				if(strpos($path_b, $path) !== false)
					$found[]=$path_b;
			}

			return $found;
		}

		public function add_bulk(string $record_name, array $content)
		{
			return $this->add($record_name, $content);
		}
		public function reopen_db() {}
	}
	class simpleblog_db_cache extends simpleblog_db
	{
		protected $constructor_params=['db_path', 'cache_path'];
		protected $cache_path;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			if(
				(!file_exists($this->cache_path)) &&
				(!mkdir($this->cache_path))
			)
				throw new simpleblog_db_exception('Unable to create cache store');

			$this->cache_path=realpath($this->cache_path);
		}

		protected function read_from_cache($file, $method, $method_params)
		{
			$file=str_replace(['/..', '../'], '', $file);

			if(file_exists($file))
			{
				$content=unserialize(file_get_contents($file));

				if($content === false)
				{
					if(!unlink($file))
						throw new simpleblog_db_exception('Fatal error: unable to repair the cache');

					return call_user_func_array([$this, $method], $method_params);
				}

				return $content;
			}
		}
		protected function read_from_db($file, $method, $method_params)
		{
			$file=str_replace(['/..', '../'], '', $file);
			$content=call_user_func_array(['parent', $method], $method_params);

			if(($content !== null) && (!empty($content)))
				file_put_contents($file, serialize($content));

			return $content;
		}
		protected function remove_list_find_cache()
		{
			if(
				file_exists($this->cache_path.'/__list_records__') &&
				(!unlink($this->cache_path.'/__list_records__'))
			)
				throw new simpleblog_db_exception('Fatal error: unable to write cache');

			foreach(glob($this->cache_path.'/__find_cache_*__') as $file)
				if(!unlink($file))
					throw new simpleblog_db_exception('Fatal error: unable to write cache');
		}

		public function list()
		{
			$content=$this->read_from_cache($this->cache_path.'/__list_records__', __FUNCTION__, []);

			if($content !== null)
				return $content;

			return $this->read_from_db($this->cache_path.'/__list_records__', __FUNCTION__, []);
		}
		public function add(string $record_name, array $content)
		{
			if(
				file_exists($this->cache_path.'/'.$record_name) &&
				(!unlink($this->cache_path.'/'.$record_name))
			)
				throw new simpleblog_db_exception('Fatal error: unable to write cache');

			$this->read_from_db($this->cache_path.'/'.$record_name, __FUNCTION__, [$record_name, $content]);
			$this->remove_list_find_cache();
		}
		public function rename(string $old_name, string $new_name)
		{
			$old_name=str_replace(['/..', '../'], '', $old_name);

			if(
				file_exists($this->cache_path.'/'.$old_name) &&
				(!unlink($this->cache_path.'/'.$old_name))
			)
				throw new simpleblog_db_exception('Fatal error: unable to write cache');

			$this->remove_list_find_cache();

			return parent::{__FUNCTION__}($old_name, $new_name);
		}
		public function read(string $record_name)
		{
			$content=$this->read_from_cache($this->cache_path.'/'.$record_name, __FUNCTION__, [$record_name]);

			if($content !== null)
				return $content;

			return $this->read_from_db($this->cache_path.'/'.$record_name, __FUNCTION__, [$record_name]);
		}
		public function delete(string $record_name)
		{
			$record_name=str_replace(['/..', '../'], '', $record_name);

			parent::{__FUNCTION__}($record_name);

			if(
				file_exists($this->cache_path.'/'.$record_name) &&
				(!unlink($this->cache_path.'/'.$record_name))
			)
				throw new simpleblog_db_exception('Fatal error: unable to delete record from cache');

			$this->remove_list_find_cache();
		}
		public function find(string $path)
		{
			$cache_file=strtr($path, '/', '__-__');
			$content=$this->read_from_cache($this->cache_path.'/__find_cache_'.$cache_file.'__', __FUNCTION__, [$path]);

			if($content !== null)
				return $content;

			return $this->read_from_db($this->cache_path.'/__find_cache_'.$cache_file.'__', __FUNCTION__, [$path]);
		}
	}
	class simpleblog_db_zip
	{
		protected $db_path;
		protected $db_compression=false;
		protected $db_handler;
		protected $db_opened=false;

		public function __construct(array $params)
		{
			if(!extension_loaded('Zip'))
				throw new simpleblog_db_exception('Zip extension is not loaded');

			if(!isset($params['db_path']))
				throw new simpleblog_db_exception('The db_path parameter was not specified for the constructor');

			foreach([
				'db_path'=>'string',
				'db_compression'=>'boolean'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new Exception('The input array parameter '.$param.' is not a '.$param_type);

					$this->$param=$params[$param];
				}

			$this->db_path=realpath($this->db_path);

			if($this->db_path === false)
			{
				if(file_put_contents($params['db_path'], '') === false)
					throw new simpleblog_db_exception('Unable to create database');

				$this->db_path=realpath($params['db_path']);

				if($this->db_path === false)
					throw new simpleblog_db_exception('realpath(db_path) failed');

				unlink($this->db_path);
			}

			$this->db_handler=new ZipArchive();
		}
		public function __destruct()
		{
			$this->close_db();
		}

		protected function open_db()
		{
			if(!$this->db_opened)
			{
				if(!$this->db_handler->open($this->db_path, ZipArchive::CREATE))
					throw new simpleblog_db_exception('Unable to open/create database');

				$this->db_opened=true;
			}
		}
		protected function close_db()
		{
			if($this->db_opened)
				$this->db_handler->close();

			$this->db_opened=false;
		}
		public function reopen_db()
		{
			$this->close_db();
			$this->open_db();
		}
		protected function array_flat($array, $prefix=null)
		{
			$result=[];

			foreach($array as $key=>$value)
			{
				$new_key=$prefix;

				if($prefix !== null)
					$new_key.='/';

				$new_key.=$key;

				if(is_array($value))
					$result=array_merge($result, (__METHOD__)($value, $new_key));
				else
					$result[$new_key]=$value;
			}

			return $result;
		}
		protected function unflatten($array)
		{
			$result=[];

			foreach($array as $key=>$value)
			{
				$keys=explode('/', $key);
				$last_key=array_pop($keys);

				$node=&$result;
				foreach($keys as $k)
				{
					if(!array_key_exists($k, $node))
						$node[$k]=[];

					$node=&$node[$k];
				}

				$node[$last_key]=$value;
			}

			return $result;
		}

		public function add_bulk(string $record_name, array $content)
		{
			$record_name=str_replace(['/..', '../'], '', $record_name);

			if($record_name === '')
				throw new simpleblog_db_exception('Record name cannot be empty');

			$this->open_db();

			foreach($this->array_flat($content) as $key=>$value)
				if($value === null)
					$this->delete_bulk($record_name.'/'.$key);
				else
				{
					if(!$this->db_handler->addFromString($record_name.'/'.$key, $value))
						throw new simpleblog_db_exception('The database could not be saved');

					if($this->db_compression)
						$this->db_handler->setCompressionName($record_name.'/'.$key, ZipArchive::CM_DEFLATE);
				}
		}

		public function list()
		{
			$this->open_db();
			$result=[];

			for($i=0; $i<$this->db_handler->numFiles; ++$i)
			{
				$i_stats=$this->db_handler->statIndex($i);

				if($i_stats !== false)
				{
					$i_stats=explode('/', $i_stats['name'])[0];
					$result[$i_stats]=$i_stats;
				}
			}

			$i=0;
			foreach($result as $record)
			{
				unset($result[$record]);
				$result[$i]=$record;
				++$i;
			}

			return $result;
		}
		public function add(string $record_name, array $content)
		{
			$this->add_bulk($record_name, $content);
			$this->reopen_db();
		}
		public function rename(string $old_name, string $new_name)
		{
			foreach($this->find($old_name) as $file)
			{
				$new_file=substr_replace($file, $new_name, strpos($file, $old_name), strlen($old_name));

				if(!$this->db_handler->renameName($file, $new_file))
					throw new simpleblog_db_exception('The database could not be saved');
			}

			return true;
		}
		public function read(string $record_name)
		{
			$record_name=str_replace(['/..', '../'], '', $record_name);

			if($record_name === '')
				throw new simpleblog_db_exception('Record name cannot be empty');

			$record_name_length=strlen($record_name);
			$result=[];

			$this->open_db();

			for($i=0; $i<$this->db_handler->numFiles; ++$i)
			{
				$i_stats=$this->db_handler->statIndex($i);

				if(
					($i_stats !== false) &&
					(substr($i_stats['name'], 0, $record_name_length) === $record_name)
				)
					$result[substr($i_stats['name'], $record_name_length+1)]=$this->db_handler->getFromName($i_stats['name']);
			}

			return $this->unflatten($result);
		}
		public function delete(string $path)
		{
			$path=str_replace(['/..', '../'], '', $path);

			if($path === '')
				throw new simpleblog_db_exception('Record name cannot be empty');

			$this->open_db();

			for($i=0; $i<$this->db_handler->numFiles; ++$i)
			{
				$i_stats=$this->db_handler->statIndex($i);

				if(
					($i_stats !== false) &&
					(stripos($i_stats['name'], $path) === 0) &&
					(!$this->db_handler->deleteName($i_stats['name']))
				)
					throw new simpleblog_db_exception('The database could not be saved');
			}
		}
		public function find(string $path)
		{
			$this->open_db();

			$found=[];
			$strlen=strlen($path);

			for($i=0; $i<$this->db_handler->numFiles; ++$i)
			{
				$i_stats=$this->db_handler->statIndex($i);

				if($i_stats !== false)
				{
					$strpos=strpos($i_stats['name'], $path);

					if(
						($strpos !== false) &&
						(
							(substr($i_stats['name'], $strpos-1, $strlen+1) === '/'.$path) ||
							(substr($i_stats['name'], $strpos, $strlen+1) === $path.'/')
						)
					)
						$found[]=$i_stats['name'];
				}
			}

			return $found;
		}
	}
?>