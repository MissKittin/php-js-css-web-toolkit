<?php
	/*
	 * Cache manager/abstraction layer
	 *
	 * Note:
	 *  timeout == 0 means infinity
	 *  the timestamp of the variable will be refreshed with each modification
	 *
	 * Main classes:
	 *  cache_container - full version of the container with local cache
	 *  cache_container_lite - simplified version without local cache
	 *
	 * Container methods:
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
	 * Drivers:
	 *  cache_driver_none -> dummy backend - only use local cache
	 *   warning:
	 *    this driver is rejected by cache_container_lite
	 *  cache_driver_file -> store serialized data in a file
	 *   constructor array parameters:
	 *    file => file path
	 *    lock_file => lock file path
	 *   warning:
	 *    if an uncaught exception occurs, the lockfile will not be removed,
	 *    a "lockfile still exists" exception will be thrown
	 *    and the cache will not work until you manually remove the lockfile
	 *  cache_driver_pdo -> use a relational database as a cache
	 *   constructor array parameters:
	 *    pdo_handler
	 *    [table_name] (default: cache_container)
	 *  cache_driver_phpredis -> use Redis as a cache
	 *   constructor array parameters:
	 *    address
	 *    [port] (default: 6379)
	 *    [socket] (default: false)
	 *    [prefix] => adds to the name of each key (default: cache_container__)
	 *   warning:
	 *    phpredis extension is required
	 *
	 * Example initialization:
		$cache=new cache_container(new cache_driver_pdo(
			'pdo_handler'=>new PDO('sqlite:./tmp/cache.sqlite3')
		));
	 */

	class cache_container
	{
		protected $cache_driver;
		protected $local_cache=array();

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
				if($value === null)
					return null;

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

		public function put_temp($key, $value, $timeout=0)
		{
			$this->local_cache[$key]['value']=$value;
			$this->local_cache[$key]['timeout']=$timeout;
			$this->local_cache[$key]['timestamp']=time();
		}

		public function get($key, $default_value=null)
		{
			$value=null;
			$timeout=0;
			$this->validate_cache($key, $value, $timeout);

			if($value === null)
				return $default_value;
			return $value;
		}
		public function put($key, $value, $timeout=0)
		{
			$this->put_temp($key, $value, $timeout);
			$this->cache_driver->put($key, $value, $timeout);
		}

		public function get_put($key, $default_value, $timeout=0)
		{
			$value=$this->get($key, null);
			if($value === null)
			{
				$this->put($key, $default_value, $timeout);
				return $default_value;
			}
			return $value;
		}
		public function increment($key, $amount=1)
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
		public function decrement($key, $amount=1)
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
		public function pull($key)
		{
			$value=null;
			$timeout=0;
			$this->validate_cache($key, $value, $timeout);

			if($value === null)
				throw new Exception($key.' is not set');

			$this->unset($key);

			return $value;
		}
		public function unset($key)
		{
			$this->cache_driver->unset($key);
			if(isset($this->local_cache[$key]))
				unset($this->local_cache[$key]);
		}
		public function isset($key)
		{
			if($this->get($key, null) === null)
				return false;
			return true;
		}
		public function flush()
		{
			$this->cache_driver->flush();
			$this->local_cache=array();
		}
	}
	class cache_container_lite
	{
		protected $cache_driver;

		public function __construct(cache_driver $cache_driver)
		{
			if($cache_driver instanceof cache_driver_none)
				throw new Exception('dummy driver cannot be used with '.__CLASS__);
			$this->cache_driver=$cache_driver;
		}

		protected function validate_cache($key)
		{
			$value=$this->cache_driver->get($key);

			if($value === null)
				return null;

			if(($value['timeout'] !== 0) && ((time()-$value['timestamp']) > $value['timeout']))
			{
				$this->unset($key);
				return null;
			}

			return $value;
		}

		public function get($key, $default_value=null)
		{
			$value=$this->validate_cache($key);
			if($value === null)
				return $default_value;

			return $value['value'];
		}
		public function put($key, $value, $timeout=0)
		{
			$this->cache_driver->put($key, $value, $timeout);
		}

		public function put_temp()
		{
			throw new Exception('put_temp() is not available in the '.__CLASS__);
		}
		public function get_put($key, $default_value, $timeout=0)
		{
			$value=$this->get($key, null);
			if($value === null)
			{
				$this->put($key, $default_value, $timeout);
				return $default_value;
			}
			return $value;
		}
		public function isset($key)
		{
			$value=$this->get($key, null);
			if($value === null)
				return false;
			return true;
		}
		public function increment($key, $amount=1)
		{
			$value=$this->validate_cache($key);
			if($value === null)
				throw new Exception($key.' is not set');
			$value['value']=$value['value']+$amount;
			$this->put($key, $value['value'], $value['timeout']);

			return $value['value'];
		}
		public function decrement($key, $amount=1)
		{
			$value=$this->validate_cache($key);
			if($value === null)
				throw new Exception($key.' is not set');
			$value['value']=$value['value']-$amount;
			$this->put($key, $value['value'], $value['timeout']);

			return $value['value'];
		}
		public function pull($key)
		{
			$value=$this->get($key, null);
			$this->unset($key);

			if($value === null)
				throw new Exception($key.' is not set');

			return $value;
		}
		public function unset($key)
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
		public function put($key, $value, $timeout): array;
			// returns array('value'=>string_value, 'timeout'=>int_timeout, 'timestamp'=>int_timestamp)|null
		public function get($key): array;
			// returns array('value'=>string_value, 'timeout'=>int_timeout, 'timestamp'=>int_timestamp)|null
		public function unset($key);
		public function flush();
	}

	class cache_driver_none implements cache_driver
	{
		public function put($a, $b, $c): array
		{
			return array();
		}
		public function get($a): array
		{
			return array();
		}
		public function unset($a) {}
		public function flush() {}
	}
	class cache_driver_file implements cache_driver
	{
		protected $file;
		protected $lock_file;
		protected $container=array();

		public function __construct(array $params)
		{
			foreach(['file', 'lock_file'] as $param)
				if(!isset($params[$param]))
					throw new Exception('the '.$param.' parameter was not specified for the constructor');

			foreach(['file', 'lock_file'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->lock_unlock_file(true);
			if(file_exists($this->file))
				$this->container=unserialize(file_get_contents($this->file));
		}
		public function __destruct()
		{
			$this->lock_unlock_file(false, function(){
				if(file_put_contents($this->file, serialize($this->container)) === false)
				{
					unlink($this->lock_file);
					throw new Exception('unable to save the cache file');
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
						throw new Exception('lock file still exists');
					--$max_wait;
				}
				if(file_put_contents($this->lock_file, '') === false)
					throw new Exception('unable to create the lock file');
			}
			else
			{
				if(!file_exists($this->lock_file))
					throw new Exception('lock file not exists - cache not saved');
				$save_callback();
				unlink($this->lock_file);
			}
		}

		public function put($key, $value, $timeout): array
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
					return null;
				}
				return $this->container[$key];
			}
			return null;
		}
		public function unset($key)
		{
			if(isset($this->container[$key]))
				unset($this->container[$key]);
		}
		public function flush()
		{
			$this->container=array();
		}
	}
	class cache_driver_pdo implements cache_driver
	{
		protected $pdo_handler;
		protected $table_name='cache_container';

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new Exception('no pdo_handler given');

			foreach(['pdo_handler', 'table_name'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			if($this->pdo_handler->exec('
				CREATE TABLE IF NOT EXISTS '.$this->table_name.'
				(
					key TEXT PRIMARY KEY,
					value TEXT,
					timeout INTEGER,
					timestamp INTEGER
				)
			') === false)
				throw new Exception('cannot create '.$this->table_name.' table');
		}

		public function put($key, $value, $timeout): array
		{
			$query=$this->pdo_handler->prepare('
				REPLACE INTO '.$this->table_name.'(key, value, timeout, timestamp)
				VALUES(:key, :value, :timeout, :timestamp)
			');
			$query->execute(array(
				':key'=>$key,
				':value'=>$value,
				':timeout'=>$timeout,
				':timestamp'=>time()
			));
		}
		public function get($key): array
		{
			$result=$this->pdo_handler->prepare('SELECT value, timeout, timestamp FROM '.$this->table_name.' WHERE key=:key');
			$result->execute(array(':key'=>$key));
			$result=$result->fetch(PDO::FETCH_ASSOC);
			if($result === false)
				return null;

			$result['timeout']=(int)$result['timeout'];
			$result['timestamp']=(int)$result['timestamp'];
			return $result;
		}
		public function unset($key)
		{
			$query=$this->pdo_handler->prepare('DELETE FROM '.$this->table_name.' WHERE key=:key');
			$query->execute(array(':key'=>$key));
		}
		public function flush()
		{
			$this->pdo_handler->exec('DELETE FROM '.$this->table_name);
		}
	}
	class cache_driver_phpredis implements cache_driver
	{
		protected $redis_handler;
		protected $prefix='cache_container__';

		public function __construct(array $params)
		{
			if(!isset($params['address']))
				throw new Exception('no redis address given');

			if(!isset($params['port']))
				$params['port']=6379;

			$this->redis_handler=new Redis();
			if((isset($params['socket'])) && ($params['socket']))
			{
				if(!$this->redis_handler->connect($params['address']))
					throw new Exception('cannot connect to the Redis');
			}
			else
				if(!$this->redis_handler->connect($params['address'], $params['port']))
					throw new Exception('cannot connect to the Redis');

			if(isset($params['prefix']))
				$this->prefix=$params['prefix'];
		}

		public function put($key, $value, $timeout): array
		{
			$value=serialize(array(
				'value'=>$value,
				'timeout'=>$timeout,
				'timestamp'=>time()
			));

			if($timeout > 0)
				$this->redis_handler->setex($this->prefix.$key, $timeout, $value);
			else
				$this->redis_handler->set($this->prefix.$key, $value);
		}
		public function get($key): array
		{
			$value=$this->redis_handler->get($this->prefix.$key);

			if($value === false)
				return null;

			$value=unserialize($value);
			if($value === false)
				return null;

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