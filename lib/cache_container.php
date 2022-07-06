<?php
	/*
	 * Cache manager/abstraction layer
	 *
	 * Note:
	 *  timeout == 0 means infinity
	 *  the timestamp of the variable will be refreshed with each modification
	 *  for cache_driver_pdo and cache_driver_redis:
	 *   if the key value is not json, the key will be deleted automatically
	 *
	 * Main classes:
	 *  cache_container - full version of the container with local cache
	 *  cache_container_lite - simplified version without local cache
	 *
	 * Container methods ([] means optional):
	 *  put(string_key, string_value, [int_timeout=0])
	 *   save to cache
	 *  put_temp(string_key, string_value, [int_timeout=0])
	 *   save to local cache only
	 *   warning:
	 *    only available in the cache_container class
	 *  get(string_key, [default_value=null]) [returns string|default_value]
	 *   read from cache
	 *   where the default_value is returned when the key is not defined
	 *  get_put(string_key, string_value, [int_timeout=0]) [returns string|default_value]
	 *   read from cache or write value to cache if key does not exist
	 *  isset(string_key) [returns bool]
	 *  increment(string_key, [int_amount=1]) [returns string]
	 *  decrement(string_key, [int_amount=1]) [returns string]
	 *  pull(string_key) [returns string]
	 *   read and unset key
	 *  unset(string_key)
	 *  flush()
	 *   all cache
	 *
	 * Drivers ([] means optional):
	 *  cache_driver_none -> dummy backend - only use local cache
	 *   warning:
	 *    this driver is rejected by cache_container_lite
	 *  cache_driver_file -> store json-encoded data in a file
	 *   constructor array parameters:
	 *    file => file path
	 *    lock_file => lock file path
	 *   note:
	 *    the database is loaded by the constructor and written by the destructor
	 *   warning:
	 *    if an uncaught exception occurs, the lockfile will not be removed,
	 *    a "lockfile still exists" exception will be thrown
	 *    and the cache will not work until you manually remove the lockfile
	 *  cache_driver_file_realtime
	 *   cache_driver_file wrapper that reads the database and writes changes to the database after each use
	 *   usage is the same as for cache_driver_file
	 *  cache_driver_pdo -> use a relational database as a cache
	 *   constructor array parameters:
	 *    pdo_handler
	 *    [table_name] (default: cache_container)
	 *   warning: mysql and pgsql are not supported
	 *   note: throws an Exception if query execution fails
	 *  cache_driver_redis -> use Redis as a cache
	 *   constructor array parameters:
	 *    redis_handler
	 *    [prefix] => adds to the name of each key (default: cache_container__)
	 *
	 * Example initialization:
		$cache=new cache_container(new cache_driver_pdo([
			'pdo_handler'=>new PDO('sqlite:./tmp/cache.sqlite3')
		]));
	 */

	class cache_container
	{
		protected $cache_driver;
		protected $local_cache=[];

		public function __construct(cache_driver $cache_driver)
		{
			$this->cache_driver=$cache_driver;
		}

		protected function validate_cache($key, &$value, &$timeout)
		{
			if(isset($this->local_cache[$key]))
			{
				$value=$this->local_cache[$key]['value'];
				$timeout=$this->local_cache[$key]['timeout'];
				$timestamp=$this->local_cache[$key]['timestamp'];
			}
			else
			{
				$value=$this->cache_driver->get($key);
				if(empty($value))
				{
					$value=null;
					return null;
				}

				$timeout=$value['timeout'];
				$timestamp=$value['timestamp'];
				$value=$value['value'];

				$this->local_cache[$key]['value']=$value;
				$this->local_cache[$key]['timeout']=$timeout;
				$this->local_cache[$key]['timestamp']=$timestamp;
			}

			if(($timeout !== 0) && ((time()-$timestamp) > $timeout))
			{
				$this->unset($key);
				$value=null;
				$timeout=0;
			}
		}

		public function put_temp(string $key, $value, int $timeout=0)
		{
			$this->local_cache[$key]['value']=$value;
			$this->local_cache[$key]['timeout']=$timeout;
			$this->local_cache[$key]['timestamp']=time();
		}

		public function get(string $key, $default_value=null)
		{
			$value=null;
			$timeout=0;
			$this->validate_cache($key, $value, $timeout);

			if($value === null)
				return $default_value;

			return $value;
		}
		public function put(string $key, $value, int $timeout=0)
		{
			$this->put_temp($key, $value, $timeout);
			$this->cache_driver->put($key, $value, $timeout);
		}

		public function get_put(string $key, $default_value, int $timeout=0)
		{
			$value=$this->get($key, null);

			if($value === null)
			{
				$this->put($key, $default_value, $timeout);
				return $default_value;
			}

			return $value;
		}
		public function increment(string $key, float $amount=1)
		{
			$value=null;
			$timeout=0;
			$this->validate_cache($key, $value, $timeout);

			if($value === null)
				throw new Exception($key.' is not set');

			$value=$value+$amount;
			$this->put($key, $value, $timeout);

			return $value;
		}
		public function decrement(string $key, float $amount=1)
		{
			$value=null;
			$timeout=0;
			$this->validate_cache($key, $value, $timeout);

			if($value === null)
				throw new Exception($key.' is not set');

			$value=$value-$amount;
			$this->put($key, $value, $timeout);

			return $value;
		}
		public function pull(string $key)
		{
			$value=null;
			$timeout=0;
			$this->validate_cache($key, $value, $timeout);

			if($value === null)
				throw new Exception($key.' is not set');

			$this->unset($key);

			return $value;
		}
		public function unset(string $key)
		{
			$this->cache_driver->unset($key);

			if(isset($this->local_cache[$key]))
				unset($this->local_cache[$key]);
		}
		public function isset(string $key)
		{
			if($this->get($key, null) === null)
				return false;

			return true;
		}
		public function flush()
		{
			$this->cache_driver->flush();
			$this->local_cache=[];
		}
	}
	class cache_container_lite
	{
		protected $cache_driver;

		public function __construct(cache_driver $cache_driver)
		{
			if($cache_driver instanceof cache_driver_none)
				throw new Exception('Dummy driver cannot be used with '.__CLASS__);

			$this->cache_driver=$cache_driver;
		}

		protected function validate_cache(string $key)
		{
			$value=$this->cache_driver->get($key);

			if(empty($value))
				return null;

			if(($value['timeout'] !== 0) && ((time()-$value['timestamp']) > $value['timeout']))
			{
				$this->unset($key);
				return null;
			}

			return $value;
		}

		public function get(string $key, $default_value=null)
		{
			$value=$this->validate_cache($key);

			if($value === null)
				return $default_value;

			return $value['value'];
		}
		public function put(string $key, $value, int $timeout=0)
		{
			$this->cache_driver->put($key, $value, $timeout);
		}

		public function put_temp()
		{
			throw new Exception('put_temp() is not available in the '.__CLASS__);
		}
		public function get_put(string $key, $default_value, int $timeout=0)
		{
			$value=$this->get($key, null);

			if($value === null)
			{
				$this->put($key, $default_value, $timeout);
				return $default_value;
			}

			return $value;
		}
		public function isset(string $key)
		{
			$value=$this->get($key, null);

			if($value === null)
				return false;

			return true;
		}
		public function increment(string $key, float $amount=1)
		{
			$value=$this->validate_cache($key);

			if($value === null)
				throw new Exception($key.' is not set');

			$value['value']=$value['value']+$amount;
			$this->put($key, $value['value'], $value['timeout']);

			return $value['value'];
		}
		public function decrement(string $key, float $amount=1)
		{
			$value=$this->validate_cache($key);

			if($value === null)
				throw new Exception($key.' is not set');

			$value['value']=$value['value']-$amount;
			$this->put($key, $value['value'], $value['timeout']);

			return $value['value'];
		}
		public function pull(string $key)
		{
			$value=$this->get($key, null);
			$this->unset($key);

			if($value === null)
				throw new Exception($key.' is not set');

			return $value;
		}
		public function unset(string $key)
		{
			$this->cache_driver->unset($key);
		}
		public function flush()
		{
			$this->cache_driver->flush();
		}
	}

	interface cache_driver
	{
		public function put($key, $value, $timeout);
		public function get($key): array;
			// returns array('value'=>string_value, 'timeout'=>int_timeout, 'timestamp'=>int_timestamp)|array()
		public function unset($key);
		public function flush();
	}

	class cache_driver_none implements cache_driver
	{
		public function put($a, $b, $c) {}
		public function get($a): array
		{
			return [];
		}
		public function unset($a) {}
		public function flush() {}
	}
	class cache_driver_file implements cache_driver
	{
		protected $file;
		protected $lock_file;
		protected $container=[];

		public function __construct(array $params)
		{
			foreach(['file', 'lock_file'] as $param)
				if(!isset($params[$param]))
					throw new Exception('The '.$param.' parameter was not specified for the constructor');

			foreach(['file', 'lock_file'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->lock_unlock_file(true);
			if(file_exists($this->file))
				$this->container=json_decode(file_get_contents($this->file), true);
		}
		public function __destruct()
		{
			$this->lock_unlock_file(false, function(){
				if(file_put_contents($this->file, json_encode($this->container, JSON_UNESCAPED_UNICODE)) === false)
				{
					unlink($this->lock_file);
					throw new Exception('Unable to save the cache file');
				}
			});
		}

		protected function lock_unlock_file($make_lock, $save_callback=null)
		{
			if($make_lock)
			{
				$max_wait=500; // 5 seconds

				while(file_exists($this->lock_file))
				{
					usleep(10000);

					if($max_wait === 0)
						throw new Exception('Lock file still exists');

					--$max_wait;
				}

				if(file_put_contents($this->lock_file, '') === false)
					throw new Exception('Unable to create the lock file');
			}
			else
			{
				if(!file_exists($this->lock_file))
					throw new Exception('Lock file not exists - cache not saved');

				$save_callback();
				unlink($this->lock_file);
			}
		}

		public function put($key, $value, $timeout)
		{
			$this->container[$key]['value']=$value;
			$this->container[$key]['timeout']=$timeout;
			$this->container[$key]['timestamp']=time();
		}
		public function get($key): array
		{
			if(isset($this->container[$key]))
			{
				if(($this->container[$key]['timeout'] !== 0) && ((time()-$this->container[$key]['timestamp']) > $this->container[$key]['timeout']))
				{
					unset($this->container[$key]);
					return [];
				}

				return $this->container[$key];
			}

			return [];
		}
		public function unset($key)
		{
			if(isset($this->container[$key]))
				unset($this->container[$key]);
		}
		public function flush()
		{
			$this->container=[];
		}
	}
	class cache_driver_file_realtime implements cache_driver
	{
		protected $file;
		protected $lock_file;

		public function __construct(array $params)
		{
			foreach(['file', 'lock_file'] as $param)
				if(!isset($params[$param]))
					throw new Exception('The '.$param.' parameter was not specified for the constructor');

			foreach(['file', 'lock_file'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];
		}

		protected function open_database()
		{
			return new cache_driver_file([
				'file'=>$this->file,
				'lock_file'=>$this->lock_file
			]);
		}

		public function put($key, $value, $timeout)
		{
			$this->open_database()->put($key, $value, $timeout);
		}
		public function get($key): array
		{
			return $this->open_database()->get($key);
		}
		public function unset($key)
		{
			$this->open_database()->unset($key);
		}
		public function flush()
		{
			$this->open_database()->flush();
		}
	}
	class cache_driver_pdo implements cache_driver
	{
		protected $pdo_handler;
		protected $table_name='cache_container';

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new Exception('No pdo_handler given');

			foreach(['pdo_handler', 'table_name'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			if($this->pdo_handler->exec(''
			.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
			.	'('
			.		'key TEXT PRIMARY KEY,'
			.		'value TEXT,'
			.		'timeout INTEGER,'
			.		'timestamp INTEGER'
			.	')'
			) === false)
				throw new Exception('Cannot create '.$this->table_name.' table');
		}

		public function put($key, $value, $timeout)
		{
			$query=$this->pdo_handler->prepare(''
			.	'REPLACE INTO '.$this->table_name
			.	'('
			.		'key,'
			.		'value,'
			.		'timeout,'
			.		'timestamp'
			.	') VALUES ('
			.		':key,'
			.		':value,'
			.		':timeout,'
			.		':timestamp'
			.	')'
			);

			if($query === false)
				throw new Exception('PDO prepare error');

			if($query->execute([
				':key'=>$key,
				':value'=>json_encode($value, JSON_UNESCAPED_UNICODE),
				':timeout'=>$timeout,
				':timestamp'=>time()
			]) === false)
				throw new Exception('PDO execute error');
		}
		public function get($key): array
		{
			$result=$this->pdo_handler->prepare(''
			.	'SELECT value, timeout, timestamp '
			.	'FROM '.$this->table_name.' '
			.	'WHERE key=:key'
			);

			if($result === false)
				throw new Exception('PDO prepare error');

			if($result->execute([':key'=>$key]) === false)
				throw new Exception('PDO execute error');

			$result=$result->fetch(PDO::FETCH_ASSOC);

			if($result === false)
				return [];

			$result['value']=json_decode($result['value'], true);
			if($result['value'] === false)
			{
				$this->unset($key);
				return [];
			}

			$result['timeout']=(int)$result['timeout'];
			$result['timestamp']=(int)$result['timestamp'];

			return $result;
		}
		public function unset($key)
		{
			$query=$this->pdo_handler->prepare(''
			.	'DELETE FROM '.$this->table_name.' '
			.	'WHERE key=:key'
			);

			if($query === false)
				throw new Exception('PDO prepare error');

			if($query->execute([':key'=>$key]) === false)
				throw new Exception('PDO execute error');
		}
		public function flush()
		{
			if($this->pdo_handler->exec('DELETE FROM '.$this->table_name) === false)
				throw new Exception('PDO exec error');
		}
	}
	class cache_driver_redis implements cache_driver
	{
		protected $redis_handler;
		protected $prefix='cache_container__';

		public function __construct(array $params)
		{
			if(!isset($params['redis_handler']))
				throw new Exception('No redis address given');

			$this->redis_handler=$params['redis_handler'];

			if(isset($params['prefix']))
				$this->prefix=$params['prefix'];
		}

		public function put($key, $value, $timeout)
		{
			$value=json_encode([
				'value'=>$value,
				'timeout'=>$timeout,
				'timestamp'=>time()
			],
			JSON_UNESCAPED_UNICODE);

			if($timeout > 0)
				$this->redis_handler->setex($this->prefix.$key, $timeout, $value);
			else
				$this->redis_handler->set($this->prefix.$key, $value);
		}
		public function get($key): array
		{
			$value=$this->redis_handler->get($this->prefix.$key);

			if($value === false)
				return [];

			$value=json_decode($value, true);
			if($value === false)
			{
				$this->unset($key);
				return [];
			}

			return $value;
		}
		public function unset($key)
		{
			$this->redis_handler->del($this->prefix.$key);
		}
		public function flush()
		{
			$this->redis_handler->flushAll();
		}
	}
?>