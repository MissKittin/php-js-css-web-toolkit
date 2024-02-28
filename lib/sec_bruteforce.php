<?php
	/*
	 * Trivial bruteforce prevention library - IP ban
	 * designed so that the attacker does not see that he is banned
	 *
	 * Warning:
	 *  if you create database for one class, then cannot be used in another
	 *  (you can read table from bruteforce_timeout_pdo in bruteforce_pdo)
	 *
	 * Note:
	 *  classes with timeout has autoclean function
	 *   removes ip from database if is not banned anymore
	 *   See clas's readme
	 *  sll classes depends on bruteforce_generic
	 *  the json_ondemand classes require their counterparts for composition
	 *  throws an bruteforce_exception on error
	 *
	 * Functions:
	 *  bruteforce_mixed - mix timeout ban with permban
	 *
	 * Classes:
	 *  bruteforce_redis
	 *   store data in Redis (permban)
	 *  bruteforce_timeout_redis
	 *   store data in Redis (timeout ban)
	 *  bruteforce_memcached
	 *   store data in Memcached (permban)
	 *  bruteforce_timeout_memcached
	 *   store data in Memcached (timeout ban)
	 *  bruteforce_pdo
	 *   store data in database via PDO (permban)
	 *   supported databases: PostgreSQL, MySQL, SQLite3
	 *   table layout: see class header
	 *  bruteforce_timeout_pdo
	 *   store data in database via PDO (timeout ban)
	 *   supported databases: PostgreSQL, MySQL, SQLite3
	 *   table layout: see class header
	 *  bruteforce_json
	 *   store data in flat file (for debugging purposes) (permban)
	 *  bruteforce_timeout_json
	 *   store data in flat file (for debugging purposes) (timeout ban)
	 *  bruteforce_json_ondemand
	 *   store data in flat file, open on access (for debugging purposes) (permban)
	 *  bruteforce_timeout_json_ondemand
	 *   store data in flat file, open on access (for debugging purposes) (timeout ban)
	 */

	class bruteforce_exception extends Exception {}

	function bruteforce_mixed(
		bruteforce_generic $timeout_hook,
		bruteforce_generic $permban_hook,
		bool $iterate_permban_counter=true,
		int $max_attempts=3
	){
		/*
		 * Mix timeout ban with permban
		 *
		 * Parameters:
		 *  $timeout_hook
		 *   bruteforce_timeout type object
		 *  $permban_hook
		 *   standard bruteforce type object
		 *  $iterate_permban_counter
		 *   if banned permanently, iterates attemps counter on every request
		 *  $max_attempts
		 *   ban permanently after n timeout bans (default 3)
		 *
		 * Returns true if ip is banned, false if not
		 *
		 * Checking ip status:
			$bruteforce_tempban=new bruteforce_timeout_pdo([
				'pdo_handler'=>new PDO('sqlite:'.'./tmp/sec_bruteforce.sqlite3'),
				'table_name'=>'temp_ban',
				'auto_clean'=>false
			]);
			if(bruteforce_mixed(
				$bruteforce_tempban,
				new bruteforce_pdo([
					'pdo_handler'=>new PDO('sqlite:'.'./tmp/sec_bruteforce.sqlite3'),
					'table_name'=>'perm_ban'
				])
			)){
				echo 'Banned';
				exit();
			}
		 * you must use $bruteforce_tempban->del() after successful operation (eg. login)
		 * if operation failed, use $bruteforce_tempban->add() as in standard operation
		 */

		if($permban_hook->check())
		{
			if($iterate_permban_counter)
				$permban_hook->add();

			return true;
		}
		else
		{
			if($timeout_hook->check())
			{
				$timeout_hook->add();

				if($timeout_hook->get_attempts()%$max_attempts === 0)
				{
					$permban_hook->add();

					if($permban_hook->check())
						$timeout_hook->del();
				}

				return true;
			}
		}

		return false;
	}

	abstract class bruteforce_generic
	{
		/*
		 * This class only contains common code
		 * Keep going
		 */

		protected $constructor_params=[];
		protected $required_constructor_params=[];

		protected $ip=null;
		protected $max_attempts=3;
		protected $current_attempts=0;
		protected $on_ban;

		// bruteforce_*_timeout
		protected $ban_time=600;
		protected $current_timestamp=null;
		protected $auto_clean=true;

		public function __construct(array $params)
		{
			$this->on_ban['callback']=function(){};

			foreach($this->required_constructor_params as $param)
				if(!isset($params[$param]))
					throw new bruteforce_exception('The '.$param.' parameter was not specified for the constructor');

			if(isset($_SERVER['REMOTE_ADDR']))
				$this->ip=$_SERVER['REMOTE_ADDR'];

			foreach($this->constructor_params as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new bruteforce_exception('The input array parameter '.$param.' is not a '.$param_type);

					$this->$param=$params[$param];
				}

			if(isset($params['on_ban']))
			{
				if(!is_callable($params['on_ban']))
					throw new bruteforce_exception('The input array parameter on_ban is not callable');

				$this->on_ban['callback']=$params['on_ban'];
			}

			if($this->ip === null)
				throw new bruteforce_exception('$_SERVER["REMOTE_ADDR"] is not set and no ip was given');
		}

		protected function lock_unlock_database($action, $check=false)
		{
			/*
			 * for constructor and destructor only
			 * used in bruteforce_json and bruteforce_timeout_json
			 *
			 * $action [bool] true: wait and create lock file, false: check if lock file exists/remove lock file (see $check)
			 * $check [bool] (req $action=false) true: check if lock file exists, false: remove lock file
			 *  $check=true returns: true: lock file exists, false: lock file not exists
			 */

			if($this->lock_file !== null)
			{
				if($action)
				{
					while(file_exists($this->lock_file))
						sleep(0.01);

					file_put_contents($this->lock_file, '');
				}
				else
				{
					if($check)
					{
						if(file_exists($this->lock_file))
							return true;

						return false;
					}

					unlink($this->lock_file);
				}
			}

			return true;
		}

		public function get_attempts()
		{
			return (int)$this->current_attempts;
		}
	}

	class bruteforce_redis extends bruteforce_generic
	{
		/*
		 * Trivial permbanning method by IP on n unsuccessful attempts
		 * from simpleblog project
		 * rewritten to Redis OOP
		 *
		 * Constructor parameters:
		 *  redis_handler [object]
		 *   required
		 *  prefix [string]
		 *   adds to the name of each key (default: bruteforce_redis__)
		 *  max_attempts [int]
		 *   n attempts and permban (default 3)
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  expire [int]
		 *   delete record after n seconds (default: 2592000 [30 days], 0 - disable)
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$bruteforce=new bruteforce_redis([
				'redis_handler'=>new Redis([
					'host'=>'127.0.0.1',
					'port'=>6379
				])
			])
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Closing connection: unset($bruteforce)
		 */

		protected $constructor_params=[
			'redis_handler'=>'object',
			'prefix'=>'string',
			'max_attempts'=>'integer',
			'ip'=>'string',
			'expire'=>'integer'
		];
		protected $required_constructor_params=['redis_handler'];

		protected $redis_handler;
		protected $prefix='bruteforce_redis__';
		protected $expire=2592000;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$current_attempts=$this->redis_handler->get($this->prefix.$this->ip);

			if($current_attempts !== false)
				$this->current_attempts=(int)$current_attempts;
		}

		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;

			return true;
		}
		public function add()
		{
			++$this->current_attempts;

			if($this->expire > 0)
			{
				$this->redis_handler->set(
					$this->prefix.$this->ip,
					$this->current_attempts,
					['ex'=>$this->expire]
				);
			}
			else
			{
				if($this->current_attempts === 1)
					$this->redis_handler->set(
						$this->prefix.$this->ip,
						$this->current_attempts
					);
				else
					$this->redis_handler->incr($this->prefix.$this->ip);
			}

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				$this->redis_handler->del($this->prefix.$this->ip);
				$this->current_attempts=0;
			}
		}
		public function clean_database() {}
	}
	class bruteforce_timeout_redis extends bruteforce_generic
	{
		/*
		 * Trivial banning method by IP on x unsuccessful attempts for n seconds
		 * from simpleblog project
		 * rewritten to Redis OOP
		 *
		 * Warning:
		 *  get_timestamp() always returns 0
		 *
		 * Note:
		 *  the auto_clean functionality is performed by Redis (set-with-expire method)
		 *  you can disable this functionality by setting the ban_time value to 0
		 *
		 * Constructor parameters:
		 *  redis_handler [object]
		 *   required
		 *  prefix [string]
		 *   adds to the name of each key (default: bruteforce_redis__)
		 *  max_attempts [int]
		 *   n attempts and ban (default 3)
		 *  ban_time [int]
		 *   unban after n seconds (default 600 [10min])
		 *   if is lower than 1, ip is permanently banned after max_attempts (see "expire" below)
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  expire [int]
		 *   delete record after n seconds (default: 2592000 [30 days], 0 - disable)
		 *   ignored when ban_time is higher than 1
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$bruteforce=new bruteforce_redis([
				'redis_handler'=>new Redis([
					'host'=>'127.0.0.1',
					'port'=>6379
				])
			])
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Banning: $bruteforce->add()
		 *  adding to the database or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from database
		 * Closing connection: unset($bruteforce)
		 *
		 * Changes with respect to bruteforce_redis: get_timestamp(), add()
		 */

		protected $constructor_params=[
			'redis_handler'=>'object',
			'prefix'=>'string',
			'max_attempts'=>'integer',
			'ban_time'=>'integer',
			'ip'=>'string',
			'expire'=>'integer'
		];
		protected $required_constructor_params=['redis_handler'];

		protected $redis_handler;
		protected $prefix='bruteforce_redis__';
		protected $expire=2592000;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$current_attempts=$this->redis_handler->get($this->prefix.$this->ip);

			if($current_attempts !== false)
				$this->current_attempts=(int)$current_attempts;
		}

		public function get_timestamp()
		{
			return 0;
		}
		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;

			if($this->current_timestamp !== null)
				if($this->current_timestamp+$this->ban_time < time())
				{
					$this->del();
					return false;
				}

			return true;
		}
		public function add()
		{
			++$this->current_attempts;
			$this->current_timestamp=time();

			if($this->ban_time < 1)
			{
				if($this->expire > 0)
					$this->redis_handler->set(
						$this->prefix.$this->ip,
						$this->current_attempts,
						['ex'=>$this->expire]
					);
				else
					$this->redis_handler->set(
						$this->prefix.$this->ip,
						$this->current_attempts
					);
			}
			else
				$this->redis_handler->set(
					$this->prefix.$this->ip,
					$this->current_attempts,
					['ex'=>$this->ban_time]
				);

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				$this->redis_handler->del($this->prefix.$this->ip);

				$this->current_attempts=0;
				$this->current_timestamp=null;
			}
		}
		public function clean_database() {}
	}
	class bruteforce_memcached extends bruteforce_generic
	{
		/*
		 * Trivial permbanning method by IP on n unsuccessful attempts
		 * from simpleblog project
		 * rewritten to Memcached OOP
		 *
		 * Constructor parameters:
		 *  memcached_handler [object]
		 *   required
		 *  prefix [string]
		 *   adds to the name of each key (default: bruteforce_memcached__)
		 *  max_attempts [int]
		 *   n attempts and permban (default 3)
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  expire [int]
		 *   delete record after n seconds (default: 2592000 [30 days], 0 - disable)
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$memcached_handler=new Memcached();
			$memcached_handler->addServer('127.0.0.1', 11211);
			$bruteforce=new bruteforce_memcached([
				'memcached_handler'=>$memcached_handler
			]);
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Closing connection: unset($bruteforce)
		 */

		protected $constructor_params=[
			'memcached_handler'=>'object',
			'prefix'=>'string',
			'max_attempts'=>'integer',
			'ip'=>'string',
			'expire'=>'integer'
		];
		protected $required_constructor_params=['memcached_handler'];

		protected $memcached_handler;
		protected $prefix='bruteforce_memcached__';
		protected $expire=2592000;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$this->memcached_handler->get($this->prefix.$this->ip); // trigger expiration
			$current_attempts=$this->memcached_handler->get($this->prefix.$this->ip);

			if($current_attempts !== false)
				$this->current_attempts=(int)$current_attempts;
		}

		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;

			return true;
		}
		public function add()
		{
			++$this->current_attempts;

			if($this->expire > 0)
			{
				$this->memcached_handler->set(
					$this->prefix.$this->ip,
					$this->current_attempts,
					$this->expire
				);
			}
			else
			{
				if($this->current_attempts === 1)
					$this->memcached_handler->set(
						$this->prefix.$this->ip,
						$this->current_attempts
					);
				else
					$this->memcached_handler->increment($this->prefix.$this->ip);
			}

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				$this->memcached_handler->delete($this->prefix.$this->ip);
				$this->current_attempts=0;
			}
		}
		public function clean_database() {}
	}
	class bruteforce_timeout_memcached extends bruteforce_generic
	{
		/*
		 * Trivial banning method by IP on x unsuccessful attempts for n seconds
		 * from simpleblog project
		 * rewritten to Memcached OOP
		 *
		 * Warning:
		 *  get_timestamp() always returns 0
		 *
		 * Note:
		 *  the auto_clean functionality is performed by Memcached (set-with-expire method)
		 *  you can disable this functionality by setting the ban_time value to 0
		 *
		 * Constructor parameters:
		 *  memcached_handler [object]
		 *   required
		 *  prefix [string]
		 *   adds to the name of each key (default: bruteforce_memcached__)
		 *  max_attempts [int]
		 *   n attempts and ban (default 3)
		 *  ban_time [int]
		 *   unban after n seconds (default 600 [10min])
		 *   if is lower than 1, ip is permanently banned after max_attempts (see "expire" below)
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  expire [int]
		 *   delete record after n seconds (default: 2592000 [30 days], 0 - disable)
		 *   ignored when ban_time is higher than 1
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$memcached_handler=new Memcached();
			$memcached_handler->addServer('127.0.0.1', 11211);
			$bruteforce=new bruteforce_memcached([
				'memcached_handler'=>$memcached_handler
			]);
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Banning: $bruteforce->add()
		 *  adding to the database or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from database
		 * Closing connection: unset($bruteforce)
		 *
		 * Changes with respect to bruteforce_memcached: get_timestamp(), add()
		 */

		protected $constructor_params=[
			'memcached_handler'=>'object',
			'prefix'=>'string',
			'max_attempts'=>'integer',
			'ban_time'=>'integer',
			'ip'=>'string',
			'expire'=>'integer'
		];
		protected $required_constructor_params=['memcached_handler'];

		protected $memcached_handler;
		protected $prefix='bruteforce_memcached__';
		protected $expire=2592000;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$this->memcached_handler->get($this->prefix.$this->ip); // trigger expiration
			$current_attempts=$this->memcached_handler->get($this->prefix.$this->ip);

			if($current_attempts !== false)
				$this->current_attempts=(int)$current_attempts;
		}

		public function get_timestamp()
		{
			return 0;
		}
		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;

			if($this->current_timestamp !== null)
				if($this->current_timestamp+$this->ban_time < time())
				{
					$this->del();
					return false;
				}

			return true;
		}
		public function add()
		{
			++$this->current_attempts;
			$this->current_timestamp=time();

			if($this->ban_time < 1)
			{
				if($this->expire > 0)
					$this->memcached_handler->set(
						$this->prefix.$this->ip,
						$this->current_attempts,
						$this->expire
					);
				else
					$this->memcached_handler->set(
						$this->prefix.$this->ip,
						$this->current_attempts
					);
			}
			else
				$this->memcached_handler->set(
					$this->prefix.$this->ip,
					$this->current_attempts,
					$this->ban_time
				);

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				$this->memcached_handler->delete($this->prefix.$this->ip);

				$this->current_attempts=0;
				$this->current_timestamp=null;
			}
		}
		public function clean_database() {}
	}
	class bruteforce_pdo extends bruteforce_generic
	{
		/*
		 * Trivial permbanning method by IP on n unsuccessful attempts
		 * from simpleblog project
		 * rewritten to PDO OOP
		 *
		 * Note:
		 *  throws an bruteforce_exception on error
		 *
		 * Supported databases:
		 *  PostgreSQL
		 *  MySQL
		 *  SQLite3
	 	 *
		 * Table layout:
		 *  PostgreSQL:
		 *   `id` SERIAL PRIMARY KEY
		 *   `ip` VARCHAR(39)
		 *   `attempts` INTEGER
		 *   `timestamp` INTEGER
		 *  MySQL:
		 *   `id` INTEGER NOT NULL AUTO_INCREMENT [PRIMARY KEY]
		 *   `ip` VARCHAR(39)
		 *   `attempts` INTEGER
		 *   `timestamp` INTEGER
		 *  SQLite3:
		 *   `id` INTEGER PRIMARY KEY AUTOINCREMENT
		 *   `ip` VARCHAR(39)
		 *   `attempts` INTEGER
		 *   `timestamp` INTEGER
		 *
		 * Constructor parameters:
		 *  pdo_handler [object]
		 *   required
		 *  table_name [string]
		 *   selected table for data (default sec_bruteforce)
		 *  create_table [bool]
		 *   send table creation query (default (safe) true)
		 *  max_attempts [int]
		 *   n attempts and ban (default 3)
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$bruteforce=new bruteforce_pdo([
				'pdo_handler'=>new PDO('sqlite:./tmp/sec_bruteforce.sqlite3')
			])
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Database cleaning: $bruteforce->clean_database(int_seconds)
		 *  removes stale records (older than 2592000 seconds [30 days] by default)
		 * Closing connection: unset($bruteforce)
		 */

		protected $constructor_params=[
			'pdo_handler'=>'object',
			'table_name'=>'string',
			'create_table'=>'boolean',
			'max_attempts'=>'integer',
			'ip'=>'string'
		];
		protected $required_constructor_params=['pdo_handler'];

		protected $pdo_handler;
		protected $table_name='sec_bruteforce';
		protected $create_table=true;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new bruteforce_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if($this->create_table)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id SERIAL PRIMARY KEY,'
						.		'ip VARCHAR(39),'
						.		'attempts INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
						.		'ip VARCHAR(39),'
						.		'attempts INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
						.		'ip VARCHAR(39),'
						.		'attempts INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}

			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$ip_query=$this->pdo_handler->query(''
					.	'SELECT * '
					.	'FROM '.$this->table_name.' '
					.	"WHERE ip='".$this->ip."'"
					);
				break;
				case 'mysql':
				case 'sqlite':
					$ip_query=$this->pdo_handler->query(''
					.	'SELECT * '
					.	'FROM '.$this->table_name.' '
					.	'WHERE ip="'.$this->ip.'"'
					);
			}

			if($ip_query !== false)
			{
				$ip_query=$ip_query->fetch(PDO::FETCH_NAMED);

				if($ip_query !== false)
					$this->current_attempts=$ip_query['attempts'];
			}
		}

		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;

			return true;
		}
		public function add()
		{
			++$this->current_attempts;

			if($this->current_attempts === 1)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'INSERT INTO '.$this->table_name
						.	'('
						.		'ip,'
						.		'attempts,'
						.		'timestamp'
						.	') VALUES ('
						.		"'".$this->ip."',"
						.		'1,'
						.		time()
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'INSERT INTO '.$this->table_name
						.	'('
						.		'ip,'
						.		'attempts,'
						.		'timestamp'
						.	') VALUES ('
						.		'"'.$this->ip.'",'
						.		'1,'
						.		time()
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}
			else
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'UPDATE '.$this->table_name.' '
						.	'SET '
						.		'attempts='.$this->current_attempts.', '
						.		'timestamp='.time().' '
						.	"WHERE ip='".$this->ip."'"
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'UPDATE '.$this->table_name.' '
						.	'SET '
						.		'attempts='.$this->current_attempts.', '
						.		'timestamp='.time().' '
						.	'WHERE ip="'.$this->ip.'"'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'DELETE FROM '.$this->table_name.' '
						.	"WHERE ip='".$this->ip."'"
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'DELETE FROM '.$this->table_name.' '
						.	'WHERE ip="'.$this->ip.'"'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}

				$this->current_attempts=0;
			}
		}
		public function clean_database(int $seconds=2592000)
		{
			$timestamp=time()-$seconds;

			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($this->pdo_handler->exec(''
					.	'DELETE FROM '.$this->table_name.' '
					.	'WHERE timestamp<'.$timestamp
					) === false)
						throw new bruteforce_exception('PDO exec error');
				break;
				case 'mysql':
				case 'sqlite':
					if($this->pdo_handler->exec(''
					.	'DELETE FROM '.$this->table_name.' '
					.	'WHERE timestamp<"'.$timestamp.'"'
					) === false)
						throw new bruteforce_exception('PDO exec error');
			}
		}
	}
	class bruteforce_timeout_pdo extends bruteforce_generic
	{
		/*
		 * Trivial banning method by IP on x unsuccessful attempts for n seconds
		 * from simpleblog project
		 * rewritten to PDO OOP
		 *
		 * Note:
		 *  throws an bruteforce_exception on error
		 *
		 * Supported databases:
		 *  PostgreSQL
		 *  MySQL
		 *  SQLite3
	 	 *
		 * Table layout:
		 *  PostgreSQL:
		 *   `id` SERIAL PRIMARY KEY
		 *   `ip` VARCHAR(39)
		 *   `attempts` INTEGER
		 *   `timestamp` INTEGER
		 *  MySQL:
		 *   `id` INTEGER NOT NULL AUTO_INCREMENT [PRIMARY KEY]
		 *   `ip` VARCHAR(39)
		 *   `attempts` INTEGER
		 *   `timestamp` INTEGER
		 *  SQLite3:
		 *   `id` INTEGER PRIMARY KEY AUTOINCREMENT
		 *   `ip` VARCHAR(39)
		 *   `attempts` INTEGER
		 *   `timestamp` INTEGER
		 *
		 * Constructor parameters:
		 *  pdo_handler [object]
		 *   required
		 *  table_name [string]
		 *   selected table for data (default sec_bruteforce)
		 *  create_table [bool]
		 *   send table creation query (default (safe) true)
		 *  max_attempts [int]
		 *   n attempts and ban (default 3)
		 *  ban_time [int]
		 *   unban after n seconds (default 600 [10min])
		 *   if is set to 0, ip is permanently banned after max_attempts
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  auto_clean [bool]
		 *   if exists in database, remove in check() if not banned anymore (defualt true)
		 *   if is enabled and ban timeout > 0, ip has max_attempts after ban, if disabled - one attempt after every ban
		 *   if ban timeout === 0, auto_clean functionality will not work
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$bruteforce=new bruteforce_pdo([
				'pdo_handler'=>new PDO('sqlite:./tmp/sec_bruteforce.sqlite3')
			])
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Last add() timestamp: $bruteforce->get_timestamp()
		 *  returns int unix timestamp of last add() or 0
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Database cleaning: $bruteforce->clean_database(int_seconds)
		 *  removes stale records (older than 2592000 seconds [30 days] by default)
		 * Closing connection: unset($bruteforce)
		 *
		 * Changes with respect to bruteforce_pdo: __construct(), get_timestamp(), check(), add()
		 */

		protected $constructor_params=[
			'pdo_handler'=>'object',
			'table_name'=>'string',
			'create_table'=>'boolean',
			'max_attempts'=>'integer',
			'ban_time'=>'integer',
			'ip'=>'string',
			'auto_clean'=>'boolean'
		];
		protected $required_constructor_params=['pdo_handler'];

		protected $pdo_handler;
		protected $table_name='sec_bruteforce';
		protected $create_table=true;

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new bruteforce_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if($this->create_table)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id SERIAL PRIMARY KEY,'
						.		'ip VARCHAR(39),'
						.		'attempts INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
						.		'ip VARCHAR(39),'
						.		'attempts INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
						.	'('
						.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
						.		'ip VARCHAR(39),'
						.		'attempts INTEGER,'
						.		'timestamp INTEGER'
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}

			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$ip_query=$this->pdo_handler->query(''
					.	'SELECT * '
					.	'FROM '.$this->table_name.' '
					.	"WHERE ip='".$this->ip."'"
					);
				break;
				case 'mysql':
				case 'sqlite':
					$ip_query=$this->pdo_handler->query(''
					.	'SELECT * '
					.	'FROM '.$this->table_name.' '
					.	'WHERE ip="'.$this->ip.'"'
					);
			}

			if($ip_query !== false)
			{
				$ip_query=$ip_query->fetch(PDO::FETCH_NAMED);

				if($ip_query !== false)
				{
					$this->current_attempts=$ip_query['attempts'];
					$this->current_timestamp=$ip_query['timestamp'];
				}
			}
		}

		public function get_timestamp()
		{
			return (int)$this->current_timestamp;
		}
		public function check()
		{
			if($this->current_timestamp === null)
				return false;

			if($this->current_attempts < $this->max_attempts)
				return false;

			if($this->ban_time === 0)
				return true;

			if($this->current_timestamp+$this->ban_time < time())
			{
				if($this->auto_clean)
					$this->del();

				return false;
			}

			return true;
		}
		public function add()
		{
			$timestamp=time();

			++$this->current_attempts;
			$this->current_timestamp=$timestamp;

			if($this->current_attempts === 1)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'INSERT INTO '.$this->table_name
						.	'('
						.		'ip,'
						.		'attempts,'
						.		'timestamp'
						.	') VALUES ('
						.		"'".$this->ip."',"
						.		'1,'
						.		$timestamp
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'INSERT INTO '.$this->table_name
						.	'('
						.		'ip,'
						.		'attempts,'
						.		'timestamp'
						.	') VALUES ('
						.		'"'.$this->ip.'",'
						.		'1,'
						.		$timestamp
						.	')'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}
			else
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'UPDATE '.$this->table_name.' '
						.	'SET '
						.		'attempts='.$this->current_attempts.','
						.		'timestamp='.$timestamp.' '
						.	"WHERE ip='".$this->ip."'"
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'UPDATE '.$this->table_name.' '
						.	'SET '
						.		'attempts='.$this->current_attempts.','
						.		'timestamp='.$timestamp.' '
						.	'WHERE ip="'.$this->ip.'"'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'DELETE FROM '.$this->table_name.' '
						.	"WHERE ip='".$this->ip."'"
						) === false)
							throw new bruteforce_exception('PDO exec error');
					break;
					case 'mysql':
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'DELETE FROM '.$this->table_name.' '
						.	'WHERE ip="'.$this->ip.'"'
						) === false)
							throw new bruteforce_exception('PDO exec error');
				}

				$this->current_attempts=0;
				$this->current_timestamp=null;
			}
		}
		public function clean_database(int $seconds=2592000)
		{
			$timestamp=time()-$seconds;

			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($this->pdo_handler->exec(''
					.	'DELETE FROM '.$this->table_name.' '
					.	'WHERE timestamp<'.$timestamp
					) === false)
						throw new bruteforce_exception('PDO exec error');
				break;
				case 'mysql':
				case 'sqlite':
					if($this->pdo_handler->exec(''
					.	'DELETE FROM '.$this->table_name.' '
					.	'WHERE timestamp<"'.$timestamp.'"'
					) === false)
						throw new bruteforce_exception('PDO exec error');
			}
		}
	}
	class bruteforce_json extends bruteforce_generic
	{
		/*
		 * Trivial permbanning method by IP on n unsuccessful attempts
		 * from simpleblog project
		 * rewritten to JSON OOP
		 * created for debugging purposes
		 *
		 * Constructor parameters:
		 *  file [string]
		 *   database file (required)
		 *  lock_file [string]
		 *   database lock file (suggested)
		 *  max_attempts [int]
		 *   n attempts and permban (default 3)
		 *  ip [string]
		 *   default $_SERVER['REMOTE_ADDR']
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$bruteforce=new bruteforce_json([
				'file'=>'./tmp/sec_bruteforce.json',
				'lock_file'=>'./tmp/sec_bruteforce.json.lock'
			])
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Database cleaning: $bruteforce->clean_database(int_seconds)
		 *  removes stale records (older than 2592000 seconds [30 days] by default)
		 * Saving database: unset($bruteforce)
		 *
		 * JSON layout:
		 *  {"string_ip":[int_attempts, int_timestamp]}
		 */

		protected $constructor_params=[
			'file'=>'string',
			'lock_file'=>'string',
			'max_attempts'=>'integer',
			'ip'=>'string'
		];
		protected $required_constructor_params=['file'];

		protected $file;
		protected $lock_file=null;
		protected $database=[];

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$this->lock_unlock_database(true);

			if(file_exists($this->file))
				$this->database=json_decode(file_get_contents($this->file), true);

			if(isset($this->database[$this->ip]))
				$this->current_attempts=$this->database[$this->ip][0];
		}
		public function __destruct()
		{
			if($this->lock_unlock_database(false, true))
			{
				file_put_contents($this->file, json_encode($this->database));
				$this->lock_unlock_database(false);
			}
		}

		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;

			return true;
		}
		public function add()
		{
			++$this->current_attempts;
			$this->database[$this->ip][0]=$this->current_attempts;
			$this->database[$this->ip][1]=time();

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				unset($this->database[$this->ip]);
				$this->current_attempts=0;
			}
		}
		public function clean_database(int $seconds=2592000)
		{
			foreach($this->database as $ip=>$data)
				if($data[1] < time()-$seconds)
					unset($this->database[$ip]);
		}
	}
	class bruteforce_timeout_json extends bruteforce_generic
	{
		/*
		 * Trivial banning method by IP on x unsuccessful attempts for n seconds
		 * from simpleblog project
		 * rewritten to JSON OOP
		 * created for debugging purposes
		 *
		 * Constructor parameters:
		 *  file [string]
		 *   database file (required)
		 *  lock_file [string]
		 *   database lock file (suggested)
		 *  max_attempts [int]
		 *   n attempts and ban (default 3)
		 *  ban_time [int]
		 *   unban after n seconds (default 600 [10min])
		 *   if is set to 0, ip is permanently banned after max_attempts
		 *  ip [string] default $_SERVER['REMOTE_ADDR']
		 *  auto_clean [bool]
		 *   if exists in database, remove in check() if not banned anymore (defualt true)
		 *   if is enabled and ban timeout > 0, ip has max_attempts after ban, if disabled - one attempt after every ban
		 *   if ban timeout === 0, auto_clean functionality will not work
		 *  on_ban [callback]
		 *   the add method runs the specified callback if a ban occurs
		 *
		 * Opening database:
			$bruteforce=new bruteforce_json([
				'file'=>'./tmp/sec_bruteforce.json',
				'lock_file'=>'./tmp/sec_bruteforce.json.lock'
			])
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0 (0 means not added)
		 * Last add() timestamp: $bruteforce->get_timestamp()
		 *  returns int unix timestamp of last add() or 0
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter and refreshes timestamp
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Database cleaning: $bruteforce->clean_database(int_seconds)
		 *  removes stale records (older than 2592000 seconds [30 days] by default)
		 * Saving database: unset($bruteforce)
		 *
		 * Changes with respect to bruteforce_json: __construct(), get_timestamp(), check(), add()
		 *
		 * JSON layout:
		 *  {"string_ip":[int_attempts, int_timestamp]}
		 */

		protected $constructor_params=[
			'file'=>'string',
			'lock_file'=>'string',
			'max_attempts'=>'integer',
			'ban_time'=>'integer',
			'ip'=>'string',
			'auto_clean'=>'boolean'
		];
		protected $required_constructor_params=['file'];

		protected $file;
		protected $lock_file=null;
		protected $database=[];

		public function __construct(array $params)
		{
			parent::{__FUNCTION__}($params);

			$this->lock_unlock_database(true);

			if(file_exists($this->file))
				$this->database=json_decode(file_get_contents($this->file), true);

			if(isset($this->database[$this->ip]))
			{
				$this->current_attempts=$this->database[$this->ip][0];
				$this->current_timestamp=$this->database[$this->ip][1];
			}
		}
		public function __destruct()
		{
			if($this->lock_unlock_database(false, true))
			{
				file_put_contents($this->file, json_encode($this->database));
				$this->lock_unlock_database(false);
			}
		}

		public function get_timestamp()
		{
			return (int)$this->current_timestamp;
		}
		public function check()
		{
			if($this->current_timestamp === null)
				return false;

			if($this->current_attempts < $this->max_attempts)
				return false;

			if($this->ban_time === 0)
				return true;

			if($this->current_timestamp+$this->ban_time < time())
			{
				if($this->auto_clean)
					$this->del();

				return false;
			}

			return true;
		}
		public function add()
		{
			++$this->current_attempts;
			$this->current_timestamp=time();

			$this->database[$this->ip][0]=$this->current_attempts;
			$this->database[$this->ip][1]=$this->current_timestamp;

			if($this->current_attempts === $this->max_attempts)
				$this->on_ban['callback']();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				unset($this->database[$this->ip]);
				$this->current_attempts=0;
				$this->current_timestamp=null;
			}
		}
		public function clean_database(int $seconds=2592000)
		{
			foreach($this->database as $ip=>$data)
				if($data[1] < time()-$seconds)
					unset($this->database[$ip]);
		}
	}
	class bruteforce_json_ondemand
	{
		/*
		 * Trivial banning method by IP on x unsuccessful attempts for n seconds
		 * from simpleblog project
		 * rewritten to PDO OOP
		 *
		 * This is a simple wrapper that opens the database only on access
		 * Usage: see bruteforce_json
		 */

		protected $class_name='bruteforce_json';
		protected $params;

		public function __construct(array $params)
		{
			$this->params=$params;
		}

		public function __call($method, $args)
		{
			$bruteforce=new $this->class_name($this->params);

			if(isset($args[0]))
				$output=$bruteforce->$method($args[0]);
			else
				$output=$bruteforce->$method();

			unset($bruteforce);

			return $output;
		}
	}
	class bruteforce_timeout_json_ondemand extends bruteforce_json_ondemand
	{
		/*
		 * Trivial banning method by IP on x unsuccessful attempts for n seconds
		 * from simpleblog project
		 * rewritten to PDO OOP
		 *
		 * This is a simple wrapper that opens the database only on access
		 * Usage: see bruteforce_timeout_json
		 */

		protected $class_name='bruteforce_timeout_json';
	}
?>