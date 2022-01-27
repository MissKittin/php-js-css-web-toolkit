<?php
	/*
	 * Trivial bruteforce prevention library - IP ban
	 * designed so that the attacker does not see that he is banned
	 *
	 * Classes with timeout has autoclean function
	 * 	removes ip from database if is not banned anymore.
	 *  See clas's readme.
	 * All classes depends on bruteforce_generic.
	 *
	 * Warning: if you create database for one class, then cannot be used in another
	 *  (you can read table from bruteforce_timeout_pdo in bruteforce_pdo)
	 *
	 * Functions:
	 *  bruteforce_mixed - mix timeout ban with permban
	 *
	 * Classes:
	 *  bruteforce_pdo - store data in database via PDO (permban)
	 *  bruteforce_timeout_pdo - store data in database via PDO (timeout ban)
	 *  bruteforce_json - store data in flat file (for debugging purposes) (permban)
	 *  bruteforce_timeout_json - store data in flat file (for debugging purposes) (timeout ban)
	 */

	function bruteforce_mixed($timeout_hook, $permban_hook, $iterate_permban_counter=true, $max_attempts=3)
	{
		/*
		 * Mix timeout ban with permban
		 *
		 * Parameters:
		 *  $timeout_hook - bruteforce_timeout type object
		 *  $permban_hook - standard bruteforce type object
		 *  $iterate_permban_counter - if banned permanently, iterates attemps counter on every request
		 *  $max_attempts - ban permanently after n timeout bans (default 3)
		 *
		 * Returns true if ip is banned, false if not
		 *
		 * Checking ip status:
			$bruteforce_tempban=new bruteforce_timeout_pdo(array(
				'pdo_handler'=>new PDO('sqlite:' . './tmp/sec_bruteforce.sqlite3'),
				'table_name'=>'temp_ban',
				'auto_clean'=>false
			));
			if(bruteforce_mixed(
				$bruteforce_tempban,
				new bruteforce_pdo(array(
					'pdo_handler'=>new PDO('sqlite:' . './tmp/sec_bruteforce.sqlite3'),
					'table_name'=>'perm_ban'
				))
			))
			{
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
		protected $ip;
		protected $max_attempts=3;
		protected $current_attempts=0;

		public function get_attempts()
		{
			return (int)$this->current_attempts;
		}
	}

	class bruteforce_pdo extends bruteforce_generic
	{
		/*
		 * Trivial permbanning method by IP on n unsuccessful attempts
		 * from simpleblog project
		 * rewritten to PDO OOP
		 *
		 * Constructor parameters:
		 *  pdo_handler [object] (required)
		 *  table_name [string] selected table for data (default sec_bruteforce)
		 *  max_attempts [int] n attempts and ban (default 3)
		 *  ip [string] default $_SERVER['REMOTE_ADDR']
		 *
		 * Opening database:
			$bruteforce=new bruteforce_pdo(array(
				'pdo_handler'=>new PDO('sqlite:./tmp/sec_bruteforce.sqlite3')
			));
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Closing connection: unset($bruteforce)
		 *
		 * Table layout:
		 *  id[primary key] ip[varchar(39)] attempts[int]
		 */

		protected $pdo_handler;
		protected $table_name='sec_bruteforce';

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new Exception('no PDO handler given');

			$this->ip=$_SERVER['REMOTE_ADDR'];
			foreach(['pdo_handler', 'table_name', 'max_attempts', 'ip'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->pdo_handler->exec('
				CREATE TABLE IF NOT EXISTS '.$this->table_name.'
				(id INTEGER PRIMARY KEY AUTOINCREMENT, ip VARCHAR(39), attempts INT)
			');

			$ip_query=$this->pdo_handler->query('
				SELECT *
				FROM '.$this->table_name.'
				WHERE ip="'.$this->ip.'"
			')->fetch(PDO::FETCH_NAMED);
			if($ip_query !== false)
				$this->current_attempts=$ip_query['attempts'];
		}

		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;
			return true;
		}
		public function add()
		{
			if($this->current_attempts === 0)
				$this->pdo_handler->exec('
					INSERT INTO '.$this->table_name.'(ip, attempts)
					VALUES("'.$this->ip.'", 1)
				');
			else
			{
				$this->current_attempts=++$this->current_attempts;
				$this->pdo_handler->exec('
					UPDATE '.$this->table_name.'
					SET attempts='.$this->current_attempts.'
					WHERE ip="'.$this->ip.'"
				');
			}
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				$this->pdo_handler->exec('
					DELETE FROM '.$this->table_name.'
					WHERE ip="'.$this->ip.'"
				');
				$this->current_attempts=0;
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
		 * Constructor parameters:
		 *  pdo_handler [object] (required)
		 *  table_name [string] selected table for data (default sec_bruteforce)
		 *  max_attempts [int] n attempts and ban (default 3)
		 *  ban_time [int] unban after n seconds (default 600 [10min])
		 *   if is set to 0, ip is permanently banned after max_attempts
		 *  ip [string] default $_SERVER['REMOTE_ADDR']
		 *  auto_clean [bool] if exists in database, remove in check() if not banned anymore (defualt true)
		 *   if is enabled and ban timeout > 0, ip has max_attempts after ban, if disabled - one attempt after every ban
		 *   if ban timeout === 0, auto_clean functionality will not work
		 *
		 * Opening database:
			$bruteforce=new bruteforce_pdo(array(
				'pdo_handler'=>new PDO('sqlite:./tmp/sec_bruteforce.sqlite3')
			));
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0
		 * Last add() timestamp: $bruteforce->get_timestamp()
		 *  returns int unix timestamp of last add() or 0
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Closing connection: unset($bruteforce)
		 *
		 * Table layout:
		 *  id[primary key] ip[varchar(39)] attempts[int] timestamp[int]
		 *
		 * Changes with respect to bruteforce_pdo: $ban_time, $current_timestamp, $auto_clean, __construct(), get_timestamp(), check(), add()
		 */

		protected $pdo_handler;
		protected $table_name='sec_bruteforce';
		protected $ban_time=600;
		protected $current_timestamp=null;
		protected $auto_clean=true;

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new Exception('no PDO handler given');

			$this->ip=$_SERVER['REMOTE_ADDR'];
			foreach(['pdo_handler', 'table_name', 'max_attempts', 'ban_time', 'ip', 'auto_clean'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->pdo_handler->exec('
				CREATE TABLE IF NOT EXISTS '.$this->table_name.'
				(id INTEGER PRIMARY KEY AUTOINCREMENT, ip VARCHAR(39), attempts INT, timestamp INT)
			');

			$ip_query=$this->pdo_handler->query('
				SELECT *
				FROM '.$this->table_name.'
				WHERE ip="'.$this->ip.'"
			')->fetch(PDO::FETCH_NAMED);
			if($ip_query !== false)
			{
				$this->current_attempts=$ip_query['attempts'];
				$this->current_timestamp=$ip_query['timestamp'];
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
			if($this->current_attempts === 0)
				$this->pdo_handler->exec('
					INSERT INTO '.$this->table_name.'(ip, attempts, timestamp)
					VALUES("'.$this->ip.'", 1, '.time().')
				');
			else
			{
				$this->current_attempts=++$this->current_attempts;
				$this->pdo_handler->exec('
					UPDATE '.$this->table_name.'
					SET attempts='.$this->current_attempts.', timestamp='.time().'
					WHERE ip="'.$this->ip.'"
				');
			}
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				$this->pdo_handler->exec('
					DELETE FROM '.$this->table_name.'
					WHERE ip="'.$this->ip.'"
				');
				$this->current_attempts=0;
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
		 *  file [string] database file (required)
		 *  lock_file [string] database lock file (suggested)
		 *  max_attempts [int] n attempts and permban (default 3)
		 *  ip [string] default $_SERVER['REMOTE_ADDR']
		 *
		 * Opening database:
			$bruteforce=new bruteforce_json(array(
				'file'=>'./tmp/sec_bruteforce.json',
				'lock_file'=>'./tmp/sec_bruteforce.json.lock'
			));
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Saving database: unset($bruteforce)
		 */

		protected $file;
		protected $lock_file=null;
		protected $database=array();

		public function __construct(array $params)
		{
			if(!isset($params['file']))
				throw new Exception('no file path given');

			$this->ip=$_SERVER['REMOTE_ADDR'];
			foreach(['file', 'lock_file', 'max_attempts', 'ip'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->lock_unlock_database(true);

			if(file_exists($this->file))
				$this->database=json_decode(file_get_contents($this->file), true);

			if(isset($this->database[$this->ip]))
				$this->current_attempts=$this->database[$this->ip];
		}
		public function __destruct()
		{
			if($this->lock_unlock_database(false, true))
			{
				file_put_contents($this->file, json_encode($this->database));
				$this->lock_unlock_database(false);
			}
		}
		protected function lock_unlock_database($action, $check=false)
		{
			/*
			 * for constructor and destructor only
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

		public function check()
		{
			if($this->current_attempts < $this->max_attempts)
				return false;
			return true;
		}
		public function add()
		{
			$this->current_attempts=++$this->current_attempts;
			$this->database[$this->ip]=$this->current_attempts;
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				unset($this->database[$this->ip]);
				$this->current_attempts=0;
			}
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
		 *  file [string] database file (required)
		 *  lock_file [string] database lock file (suggested)
		 *  max_attempts [int] n attempts and ban (default 3)
		 *  ban_time [int] unban after n seconds (default 600 [10min])
		 *   if is set to 0, ip is permanently banned after max_attempts
		 *  ip [string] default $_SERVER['REMOTE_ADDR']
		 *  auto_clean [bool] if exists in database, remove in check() if not banned anymore (defualt true)
		 *   if is enabled and ban timeout > 0, ip has max_attempts after ban, if disabled - one attempt after every ban
		 *   if ban timeout === 0, auto_clean functionality will not work
		 *
		 * Opening database:
			$bruteforce=new bruteforce_json(array(
				'file'=>'./tmp/sec_bruteforce.json',
				'lock_file'=>'./tmp/sec_bruteforce.json.lock'
			));
		 * Checking: $bruteforce->check()
		 *  returns bool
		 * Current attempts number: $bruteforce->get_attempts()
		 *  returns int from 0
		 * Last add() timestamp: $bruteforce->get_timestamp()
		 *  returns int unix timestamp of last add() or 0
		 * Banning: $bruteforce->add()
		 *  adding to the table or iterates attempts counter and refreshes timestamp
		 * Unbanning: $bruteforce->del()
		 *  removes from the table
		 * Saving database: unset($bruteforce)
		 *
		 * Changes with respect to bruteforce_json: $ban_time, $current_timestamp, $auto_clean, __construct(), get_timestamp(), check(), add()
		 */

		protected $file;
		protected $lock_file=null;
		protected $database=array();
		protected $ban_time=600;
		protected $current_timestamp=null;
		protected $auto_clean=true;

		public function __construct(array $params)
		{
			if(!isset($params['file']))
				throw new Exception('no file path given');

			$this->ip=$_SERVER['REMOTE_ADDR'];
			foreach(['file', 'lock_file', 'max_attempts', 'ban_time', 'ip', 'auto_clean'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->lock_unlock_database(true);

			if(file_exists($this->file))
				$this->database=json_decode(file_get_contents($this->file), true);

			if(isset($this->database[$this->ip]))
			{
				$this->current_attempts=$this->database[$this->ip]['attempts'];
				$this->current_timestamp=$this->database[$this->ip]['timestamp'];
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
		protected function lock_unlock_database($action, $check=false)
		{
			/*
			 * for constructor and destructor only
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
			$this->current_attempts=++$this->current_attempts;
			$this->database[$this->ip]['attempts']=$this->current_attempts;
			$this->database[$this->ip]['timestamp']=time();
		}
		public function del()
		{
			if($this->current_attempts !== 0)
			{
				unset($this->database[$this->ip]);
				$this->current_attempts=0;
			}
		}
	}
?>