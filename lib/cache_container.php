<?php
	/*
	 * Cache manager/abstraction layer
	 *
	 * Note:
	 *  timeout == 0 means infinity
	 *  the timestamp of the variable will be refreshed with each modification
	 *  for cache_driver_pdo and cache_driver_redis:
	 *   if the key value is not json, the key will be deleted automatically
	 *  throws an cache_container_exception on error (also see cache_driver_pdo doc)
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
	 *   warning: only available in the cache_container class
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
	 *   warning: this driver is rejected by cache_container_lite
	 *  cache_driver_file -> store json-encoded data in a file
	 *   constructor array parameters:
	 *    file => string_file_path
	 *    lock_file => string_lock_file_path
	 *   note: the database is loaded by the constructor and written by the destructor
	 *   warning:
	 *    if an uncaught exception occurs, the lockfile will not be removed,
	 *    a "lockfile still exists" cache_container_exception will be thrown
	 *    and the cache will not work until you manually remove the lockfile
	 *  cache_driver_file_realtime
	 *   cache_driver_file wrapper that reads the database and writes changes to the database after each use
	 *   usage is the same as for cache_driver_file
	 *  cache_driver_pdo -> use a relational database as a cache
	 *   constructor array parameters:
	 *    pdo_handle (object)
	 *    [table_name] (string, default: cache_container)
	 *    [create_table] (bool, default: true)
	 *   note:
	 *    throws an cache_container_exception if query execution fails
	 *    may throw PDOException depending on PDO::ATTR_ERRMODE
	 *   supported databases: PostgreSQL, MySQL, SQLite3
	 *   table layout:
	 *    MySQL:
	 *     `cachekey` VARCHAR(255) [PRIMARY KEY]
	 *     `cachevalue` TEXT
	 *     `timeout` INTEGER
	 *     `timestamp` INTEGER
	 *    PostgreSQL and SQLite3:
	 *     `cachekey` TEXT PRIMARY KEY
	 *     `cachevalue` TEXT
	 *     `timeout` INTEGER
	 *     `timestamp` INTEGER
	 *  cache_driver_redis -> use Redis
	 *   constructor array parameters:
	 *    redis_handle (object)
	 *    [prefix] => string, adds to the name of each key (default: cache_container__)
	 *  cache_driver_memcached -> use Memcached
	 *   constructor array parameters:
	 *    memcached_handle (object)
	 *    [prefix] => string, adds to the name of each key (default: cache_container__)
	 *   warning: Memcached does not support the flush method
	 *  cache_driver_apcu -> use APCu
	 *   constructor array parameters:
	 *    [prefix] => string, adds to the name of each key (default: cache_container__)
	 *   warning: apcu extension is required
	 *  cache_driver_shm -> use Unix shared memory
	 *   constructor array parameters:
	 *    [key] => int, system's id for the shared memory block
	 *    [size] => int, the size of the shared memory block you wish to create in bytes (default: 320000)
	 *   warning:
	 *    shmop extension is required
	 *    if the process that opened the memory block dies, the data will be lost
	 *    use only for long-lived processes
	 *
	 * Example initialization:
		$cache=new cache_container(new cache_driver_pdo([
			'pdo_handle'=>new PDO('sqlite:./var/cache/cache.sqlite3')
		]));
	 */

	class cache_container_exception extends Exception {}

	interface cache_driver
	{
		public function put($key, $value, $timeout): void;
		public function get($key): array;
			// returns array('value'=>string_value, 'timeout'=>int_timeout, 'timestamp'=>int_timestamp)|array()
		public function unset($key): void;
		public function flush(): void;
	}

	class cache_container
	{
		protected $cache_driver;
		protected $local_cache=[];

		public function __construct(cache_driver $cache_driver)
		{
			$this->cache_driver=$cache_driver;
		}

		protected function validate_cache(
			$key,
			&$value,
			&$timeout
		){
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

			if(
				($timeout !== 0) &&
				((time()-$timestamp) > $timeout)
			){
				$this->unset($key);

				$value=null;
				$timeout=0;
			}
		}

		public function put_temp(
			string $key,
			$value,
			int $timeout=0
		){
			$this->local_cache[$key]['value']=$value;
			$this->local_cache[$key]['timeout']=$timeout;
			$this->local_cache[$key]['timestamp']=time();
		}

		public function get(string $key, $default_value=null)
		{
			$value=null;
			$timeout=0;

			$this->validate_cache(
				$key,
				$value,
				$timeout
			);

			if($value === null)
				return $default_value;

			return $value;
		}
		public function put(
			string $key,
			$value,
			int $timeout=0
		){
			$this->put_temp(
				$key,
				$value,
				$timeout
			);

			$this->cache_driver->put(
				$key,
				$value,
				$timeout
			);
		}

		public function get_put(
			string $key,
			$default_value,
			int $timeout=0
		){
			$value=$this->get($key, null);

			if($value === null)
			{
				$this->put(
					$key,
					$default_value,
					$timeout
				);

				return $default_value;
			}

			return $value;
		}
		public function increment(string $key, float $amount=1)
		{
			$value=null;
			$timeout=0;

			$this->validate_cache(
				$key,
				$value,
				$timeout
			);

			if($value === null)
				throw new cache_container_exception(
					$key.' is not set'
				);

			$value=$value+$amount;

			$this->put(
				$key,
				$value,
				$timeout
			);

			return $value;
		}
		public function decrement(string $key, float $amount=1)
		{
			$value=null;
			$timeout=0;

			$this->validate_cache(
				$key,
				$value,
				$timeout
			);

			if($value === null)
				throw new cache_container_exception(
					$key.' is not set'
				);

			$value=$value-$amount;

			$this->put(
				$key,
				$value,
				$timeout
			);

			return $value;
		}
		public function pull(string $key)
		{
			$value=null;
			$timeout=0;

			$this->validate_cache(
				$key,
				$value,
				$timeout
			);

			if($value === null)
				throw new cache_container_exception(
					$key.' is not set'
				);

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
				throw new cache_container_exception(
					'Dummy driver cannot be used with '.__CLASS__
				);

			$this->cache_driver=$cache_driver;
		}

		protected function validate_cache(string $key)
		{
			$value=$this->cache_driver->get($key);

			if(empty($value))
				return null;

			if(
				($value['timeout'] !== 0) &&
				((time()-$value['timestamp']) > $value['timeout'])
			){
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
		public function put(
			string $key,
			$value,
			int $timeout=0
		){
			$this->cache_driver->put(
				$key,
				$value,
				$timeout
			);
		}

		public function put_temp()
		{
			throw new cache_container_exception(
				'put_temp() is not available in the '.__CLASS__
			);
		}
		public function get_put(
			string $key,
			$default_value,
			int $timeout=0
		){
			$value=$this->get($key, null);

			if($value === null)
			{
				$this->put(
					$key,
					$default_value,
					$timeout
				);

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
				throw new cache_container_exception(
					$key.' is not set'
				);

			$value['value']=$value['value']+$amount;

			$this->put(
				$key,
				$value['value'],
				$value['timeout']
			);

			return $value['value'];
		}
		public function decrement(string $key, float $amount=1)
		{
			$value=$this->validate_cache($key);

			if($value === null)
				throw new cache_container_exception(
					$key.' is not set'
				);

			$value['value']=$value['value']-$amount;

			$this->put(
				$key,
				$value['value'],
				$value['timeout']
			);

			return $value['value'];
		}
		public function pull(string $key)
		{
			$value=$this->get($key, null);

			$this->unset($key);

			if($value === null)
				throw new cache_container_exception(
					$key.' is not set'
				);

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

	class cache_driver_none implements cache_driver
	{
		public function put($a, $b, $c): void {}
		public function get($a): array
		{
			return [];
		}
		public function unset($a): void {}
		public function flush(): void {}
	}
	class cache_driver_file implements cache_driver
	{
		protected $file;
		protected $lock_file;
		protected $container=[];

		public function __construct(array $params)
		{
			foreach([
				'file',
				'lock_file'
			] as $param){
				if(isset($params[$param]))
				{
					if(
						(!isset($params['_no_type_hint'])) &&
						(!is_string($params[$param]))
					)
						throw new cache_container_exception(
							'The input array parameter '.$param.' is not a string'
						);

					$this->$param=$params[$param];

					continue;
				}

				throw new cache_container_exception(
					'The '.$param.' parameter was not specified for the constructor'
				);
			}

			$this->lock_unlock_file(true);

			if(file_exists($this->file))
				$this->container=json_decode(
					file_get_contents($this->file),
					true
				);
		}
		public function __destruct()
		{
			$this->lock_unlock_file(false, function(){
				if(file_put_contents(
					$this->file,
					json_encode(
						$this->container,
						JSON_UNESCAPED_UNICODE
					)
				) === false){
					unlink($this->lock_file);

					throw new cache_container_exception(
						'Unable to save the cache file'
					);
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

					if($max_wait-- === 0)
						throw new cache_container_exception(
							'Lock file still exists'
						);
				}

				if(file_put_contents(
					$this->lock_file,
					''
				) === false)
					throw new cache_container_exception(
						'Unable to create the lock file'
					);

				return;
			}

			if(!file_exists($this->lock_file))
				throw new cache_container_exception(
					'Lock file not exists - cache not saved'
				);

			$save_callback();
			unlink($this->lock_file);
		}

		public function put($key, $value, $timeout): void
		{
			$this->container[$key]['value']=$value;
			$this->container[$key]['timeout']=$timeout;
			$this->container[$key]['timestamp']=time();
		}
		public function get($key): array
		{
			if(isset($this->container[$key]))
			{
				if(
					($this->container[$key]['timeout'] !== 0) &&
					((time()-$this->container[$key]['timestamp']) > $this->container[$key]['timeout'])
				){
					unset($this->container[$key]);
					return [];
				}

				return $this->container[$key];
			}

			return [];
		}
		public function unset($key): void
		{
			if(isset($this->container[$key]))
				unset($this->container[$key]);
		}
		public function flush(): void
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
			foreach([
				'file',
				'lock_file'
			] as $param){
				if(isset($params[$param]))
				{
					if((!is_string($params[$param])))
						throw new cache_container_exception(
							'The input array parameter '.$param.' is not a string'
						);

					$this->$param=$params[$param];

					continue;
				}

				throw new cache_container_exception(
					'The '.$param.' parameter was not specified for the constructor'
				);
			}
		}

		protected function open_database()
		{
			return new cache_driver_file([
				'file'=>$this->file,
				'lock_file'=>$this->lock_file,
				'_no_type_hint'=>true
			]);
		}

		public function put($key, $value, $timeout): void
		{
			$this->open_database()->put(
				$key,
				$value,
				$timeout
			);
		}
		public function get($key): array
		{
			return $this->open_database()->get($key);
		}
		public function unset($key): void
		{
			$this->open_database()->unset($key);
		}
		public function flush(): void
		{
			$this->open_database()->flush();
		}
	}
	class cache_driver_pdo implements cache_driver
	{
		protected $pdo_handle;
		protected $table_name='cache_container';
		protected $create_table=true;

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handle']))
				throw new cache_container_exception(
					'No pdo_handle given'
				);

			foreach([
				'pdo_handle'=>'object',
				'table_name'=>'string',
				'create_table'=>'boolean'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new cache_container_exception(
							'The input array parameter '.$param.' is not a '.$param_type
						);

					$this->$param=$params[$param];
				}

			if(!in_array(
				$this->pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new cache_container_exception(
					$this->pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported'
				);

			if($this->create_table)
				switch($this->pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'mysql':
						if($this->pdo_handle->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'cachekey VARCHAR(255), PRIMARY KEY(cachekey),'
						.		'cachevalue TEXT,'
						.		'timeout INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new cache_container_exception(
								'Cannot create '.$this->table_name.' table'
							);
					break;
					case 'pgsql':
					case 'sqlite':
						if($this->pdo_handle->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'cachekey TEXT PRIMARY KEY,'
						.		'cachevalue TEXT,'
						.		'timeout INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new cache_container_exception(
								'Cannot create '.$this->table_name.' table'
							);
				}
		}

		public function put($key, $value, $timeout): void
		{
			switch($this->pdo_handle->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$query=$this->pdo_handle->prepare(''
					.	'INSERT INTO '.$this->table_name
					.	'('
					.		'cachekey,'
					.		'cachevalue,'
					.		'timeout,'
					.		'timestamp'
					.	') VALUES ('
					.		':key,'
					.		':value,'
					.		':timeout,'
					.		':timestamp'
					.	')'
					.	'ON CONFLICT(cachekey) DO UPDATE SET '
					.		'cachevalue=:value,'
					.		'timeout=:timeout,'
					.		'timestamp=:timestamp'
					);
				break;
				case 'mysql':
				case 'sqlite':
					$query=$this->pdo_handle->prepare(''
					.	'REPLACE INTO '.$this->table_name
					.	'('
					.		'cachekey,'
					.		'cachevalue,'
					.		'timeout,'
					.		'timestamp'
					.	') VALUES ('
					.		':key,'
					.		':value,'
					.		':timeout,'
					.		':timestamp'
					.	')'
					);
			}

			if($query === false)
				throw new cache_container_exception(
					'PDO prepare error'
				);

			if(!$query->execute([
				':key'=>$key,
				':value'=>json_encode($value, JSON_UNESCAPED_UNICODE),
				':timeout'=>$timeout,
				':timestamp'=>time()
			]))
				throw new cache_container_exception(
					'PDO execute error'
				);
		}
		public function get($key): array
		{
			$result=$this->pdo_handle->prepare(''
			.	'SELECT cachevalue, timeout, timestamp '
			.	'FROM '.$this->table_name.' '
			.	'WHERE cachekey=:key'
			);

			if($result === false)
				throw new cache_container_exception(
					'PDO prepare error'
				);

			if(!$result->execute([
				':key'=>$key
			]))
				throw new cache_container_exception(
					'PDO execute error'
				);

			$result=$result->fetch(PDO::FETCH_ASSOC);

			if($result === false)
				return [];

			$result['cachevalue']=json_decode(
				$result['cachevalue'],
				true
			);

			if($result['cachevalue'] === false)
			{
				$this->unset($key);
				return [];
			}

			$result['timeout']=(int)$result['timeout'];
			$result['timestamp']=(int)$result['timestamp'];
			$result['value']=&$result['cachevalue'];

			return $result;
		}
		public function unset($key): void
		{
			$query=$this->pdo_handle->prepare(''
			.	'DELETE FROM '.$this->table_name.' '
			.	'WHERE cachekey=:key'
			);

			if($query === false)
				throw new cache_container_exception(
					'PDO prepare error'
				);

			if(!$query->execute([
				':key'=>$key
			]))
				throw new cache_container_exception(
					'PDO execute error'
				);
		}
		public function flush(): void
		{
			if($this->pdo_handle->exec(''
			.	'DELETE FROM '.$this->table_name
			) === false)
				throw new cache_container_exception(
					'PDO exec error'
				);
		}
	}
	class cache_driver_redis implements cache_driver
	{
		protected $redis_handle;
		protected $prefix='cache_container__';

		public function __construct(array $params)
		{
			if(!isset($params['redis_handle']))
				throw new cache_container_exception(
					'No redis_handle given'
				);

			foreach([
				'redis_handle'=>'object',
				'prefix'=>'string'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new cache_container_exception(
							'The input array parameter '.$param.' is not a '.$param_type
						);

					$this->$param=$params[$param];
				}
		}

		public function put($key, $value, $timeout): void
		{
			$value=json_encode(
				[
					'value'=>$value,
					'timeout'=>$timeout,
					'timestamp'=>time()
				],
				JSON_UNESCAPED_UNICODE
			);

			if($timeout > 0)
			{
				$this->redis_handle->set(
					$this->prefix.$key,
					$value,
					['ex'=>$timeout]
				);

				return;
			}

			$this->redis_handle->set(
				$this->prefix.$key,
				$value
			);
		}
		public function get($key): array
		{
			$value=$this->redis_handle->get(''
			.	$this->prefix
			.	$key
			);

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
		public function unset($key): void
		{
			$this->redis_handle->del(''
			.	$this->prefix
			.	$key
			);
		}
		public function flush(): void
		{
			$iterator=null;

			do
			{
				$keys=$this->redis_handle->scan(
					$iterator,
					$this->prefix.'*'
				);

				if($keys === false)
					break;

				foreach($keys as $key)
					$this->redis_handle->del($key);
			}
			while($iterator > 0);
		}
	}
	class cache_driver_memcached implements cache_driver
	{
		protected $memcached_handle;
		protected $prefix='cache_container__';

		public function __construct(array $params)
		{
			if(!isset($params['memcached_handle']))
				throw new cache_container_exception(
					'No memcached_handle given'
				);

			foreach([
				'memcached_handle'=>'object',
				'prefix'=>'string'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new cache_container_exception(
							'The input array parameter '.$param.' is not a '.$param_type
						);

					$this->$param=$params[$param];
				}
		}

		public function put($key, $value, $timeout): void
		{
			$this->memcached_handle->set(
				$this->prefix.$key,
				json_encode(
					[
						'value'=>$value,
						'timeout'=>$timeout,
						'timestamp'=>time()
					],
					JSON_UNESCAPED_UNICODE
				),
				$timeout
			);
		}
		public function get($key): array
		{
			// trigger expiration
			$this->memcached_handle->get(''
			.	$this->prefix
			.	$key
			);

			$value=$this->memcached_handle->get(''
			.	$this->prefix
			.	$key
			);

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
		public function unset($key): void
		{
			$this->memcached_handle->delete(''
			.	$this->prefix
			.	$key
			);
		}
		public function flush(): void
		{
			throw new cache_container_exception(
				'Memcached does not support the flush method'
			);
		}
	}
	class cache_driver_apcu implements cache_driver
	{
		protected $prefix='cache_container__';

		public function __construct(array $params)
		{
			if(!function_exists('apcu_enabled'))
				throw new cache_container_exception(
					'apcu extension is not loaded'
				);

			if(!apcu_enabled())
				throw new cache_container_exception(
					'APCu is disabled'
				);

			if(isset($params['prefix']))
			{
				if(!is_string($params['prefix']))
					throw new cache_container_exception(
						'The input array parameter prefix is not a string'
					);

				$this->prefix=$params['prefix'];
			}
		}

		public function put($key, $value, $timeout): void
		{
			apcu_store(
				$this->prefix.$key,
				json_encode(
					[
						'value'=>$value,
						'timeout'=>$timeout,
						'timestamp'=>time()
					],
					JSON_UNESCAPED_UNICODE
				),
				$timeout
			);
		}
		public function get($key): array
		{
			$value=apcu_fetch(''
			.	$this->prefix
			.	$key
			);

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
		public function unset($key): void
		{
			apcu_delete(''
			.	$this->prefix
			.	$key
			);
		}
		public function flush(): void
		{
			foreach(new APCUIterator(''
			.	'/^'
			.		$this->prefix
			.	'.*/'
			)as $item)
				apcu_delete($item['key']);
		}
	}
	class cache_driver_shm implements cache_driver
	{
		protected $key;
		protected $size=320000;
		protected $id;
		protected $mode='c';

		public function __construct(array $params=[])
		{
			if(!function_exists('shmop_open'))
				throw new cache_container_exception(
					'shmop extension is not loaded'
				);

			foreach(['key', 'size'] as $param)
				if(isset($params[$param]))
				{
					if(!is_int($params[$param]))
						throw new cache_container_exception(
							'The input array parameter '.$param.' is not an integer'
						);

					$this->$param=$params[$param];
				}

			if($this->key === null)
				$this->key=ftok(__FILE__, 'b');

			if(@shmop_open(
				$this->key,
				'a',
				0, 0
			))
				$this->mode='w';

			$this->id=shmop_open(
				$this->key,
				$this->mode,
				0644,
				$this->size
			);

			if($this->id === false)
				throw new cache_container_exception(
					'shmop_open failed'
				);
		}
		public function __destruct()
		{
			if($this->mode === 'c')
				shmop_delete(
					$this->id
				);
		}

		protected function get_shm_data()
		{
			$data=shmop_read(
				$this->id,
				0,
				shmop_size(
					$this->id
				)
			);

			if($data === false)
				return '';

			$data=rtrim($data);

			if($data === '')
				return '';

			$data=json_decode($data, true);

			if($data === null)
				return '';

			return $data;
		}
		protected function put_shm_data($data)
		{
			shmop_write(
				$this->id,
				str_repeat("\0", shmop_size(
					$this->id
				)),
				0
			);

			if($data === null)
				return;

			shmop_write(
				$this->id,
				json_encode(
					$data,
					JSON_UNESCAPED_UNICODE
				),
				0
			);
		}

		public function put($key, $value, $timeout): void
		{
			$data=$this->get_shm_data();

			if($data === '')
				$data=[];

			$data[$key]=[
				$value,
				$timeout,
				time()
			];

			$this->put_shm_data($data);
		}
		public function get($key): array
		{
			$data=$this->get_shm_data();

			if($data === '')
				return [];

			if(!isset($data[$key]))
				return [];

			return [
				'value'=>$data[$key][0],
				'timeout'=>$data[$key][1],
				'timestamp'=>$data[$key][2]
			];
		}
		public function unset($key): void
		{
			$data=$this->get_shm_data();

			if($data === '')
				return;

			unset($data[$key]);

			$this->put_shm_data($data);
		}
		public function flush(): void
		{
			$this->put_shm_data(null);
		}
	}
?>