<?php
	/*
	 * Lv encrypter
	 * Laravel Encrypter class
	 *  with adapters and session handlers
	 *
	 * Warning:
	 *  the key may leak through stack trace!!! - please display_errors=off
	 *   or your app will be compromised!!!
	 *  openssl (>=1.1.0g) extension is required
	 *  mbstring extensions is required
	 *
	 * Note:
	 *  throws an lv_encrypter_exception on error
	 *
	 * Classes:
	 *  lv_encrypter
	 *   main class - content encryptor/decryptor and key generator
	 *   from laravel framework
	 *   distributed under the MIT license
	 *   note: you cannot inherit from this class
	 *  lv_cookie_encrypter
	 *   adapter between setcookie and lv_encrypter
	 *  lv_session_encrypter
	 *   adapter (middleware) between SessionHandler and lv_encrypter
	 *   transparently encrypts session content
	 *  lv_cookie_session_handler
	 *   session handler that uses an encrypted cookie to store the session
	 *   note: use lv_cookie_session_handler::session_start() instead of PHP session_start()
	 *  lv_pdo_session_handler
	 *   session handler that uses a relational database to store an encrypted session
	 *   supported databases: PostgreSQL, MySQL, SQLite3
	 *   read warning in class block
	 *  lv_redis_session_handler
	 *   session handler that uses Redis to store an encrypted session
	 */

	class lv_encrypter_exception extends Exception {}
	final class lv_encrypter
	{
		/*
		 * Lv encrypter
		 * main class
		 *
		 * Warning:
		 *  OpenSSL (>=1.1.0g) and mbstring extensions are required
		 *
		 * Note:
		 *  throws an lv_encrypter_exception on error
		 *
		 * Usage:
		 *  Key generation:
		 *   lv_encrypter::generate_key([string_cipher='aes-128-cbc']) [returns base64-encoded random string]
		 *  Initialization:
		 *   $encrypter=new lv_encrypter(string_key, [string_cipher='aes-128-cbc'])
		 *  Encryption:
		 *   $encrypter->encrypt(string_content, [bool_if_do_serialization=true])
		 *    serializes input content before encryption by default
		 *  Decryption:
		 *   $encrypter->decrypt(string_payload, [bool_if_do_unserialization=true])
		 *  Print supported ciphers (returns array):
		 *   lv_encrypter::supported_ciphers()
		 *
		 * Source: https://github.com/illuminate/encryption/blob/master/Encrypter.php
		 * License: MIT
		 */

		private static $supported_ciphers=[
			'aes-128-cbc'=>[
				'size'=>16,
				'aead'=>false
			],
			'aes-256-cbc'=>[
				'size'=>32,
				'aead'=>false
			],
			'aes-128-gcm'=>[
				'size'=>16,
				'aead'=>true
			],
			'aes-256-gcm'=>[
				'size'=>32,
				'aead'=>true
			]
		];

		private $key;
		private $cipher;

		public static function supported_ciphers()
		{
			return static::$supported_ciphers;
		}
		public static function generate_key(string $cipher='aes-128-cbc')
		{
			$cipher=strtolower($cipher);

			if(isset(self::$supported_ciphers[$cipher]))
				return base64_encode(random_bytes(self::$supported_ciphers[$cipher]['size']));

			throw new lv_encrypter_exception($cipher.' cipher is not supported');
		}

		public function __construct(string $key, string $cipher='aes-128-cbc')
		{
			if(!extension_loaded('openssl'))
				throw new lv_encrypter_exception('openssl extension is not loaded');

			if(!extension_loaded('mbstring'))
				throw new lv_encrypter_exception('mbstring extension is not loaded');

			$key=base64_decode($key);
			$cipher=strtolower($cipher);

			if(!isset(self::$supported_ciphers[$cipher]))
				throw new lv_encrypter_exception($cipher.' cipher is not supported');

			if(mb_strlen($key, '8bit') !== self::$supported_ciphers[$cipher]['size'])
				throw new lv_encrypter_exception('Key length is invalid');

			$this->key=$key;
			$this->cipher=$cipher;
		}

		public function encrypt($content, bool $serialize=true)
		{
			if($serialize)
				$content=serialize($content);

			$iv=random_bytes(openssl_cipher_iv_length($this->cipher));
			$tag='';
			if(self::$supported_ciphers[$this->cipher]['aead'])
				$content=openssl_encrypt($content, $this->cipher, $this->key, 0, $iv, $tag);
			else
				$content=openssl_encrypt($content, $this->cipher, $this->key, 0, $iv);

			if($content === false)
				throw new lv_encrypter_exception('Could not encrypt the data');

			$iv=base64_encode($iv);
			$tag=base64_encode($tag);
			if(self::$supported_ciphers[$this->cipher]['aead'])
				$mac='';
			else
				$mac=hash_hmac('sha256', $iv.$content, $this->key);

			$json=json_encode(compact('iv', 'content', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);

			if(json_last_error() !== JSON_ERROR_NONE)
				throw new lv_encrypter_exception('Could not encrypt the data');

			return base64_encode($json);
		}
		public function decrypt(string $payload, bool $unserialize=true)
		{
			$payload=json_decode(base64_decode($payload), true);

			if(!(
				is_array($payload) &&
				isset($payload['iv'], $payload['content'], $payload['mac']) &&
				(strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher))
			))
				throw new lv_encrypter_exception('The payload is invalid');

			if(
				(!self::$supported_ciphers[$this->cipher]['aead']) &&
				(!hash_equals(hash_hmac('sha256', $payload['iv'].$payload['content'], $this->key), $payload['mac']))
			)
				throw new lv_encrypter_exception('The MAC is invalid');

			$iv=base64_decode($payload['iv']);

			if(empty($payload['tag']))
				$tag='';
			else
				$tag=base64_decode($payload['tag']);

			if((self::$supported_ciphers[$this->cipher]['aead']) && (strlen($tag) !== 16))
				throw new lv_encrypter_exception('Could not decrypt the data');

			$decrypted=openssl_decrypt($payload['content'], $this->cipher, $this->key, 0, $iv, $tag);

			if($decrypted === false)
				throw new lv_encrypter_exception('Could not decrypt the data');

			if($unserialize)
				$decrypted=unserialize($decrypted);

			return $decrypted;
		}
	}
	class lv_cookie_encrypter
	{
		/*
		 * Lv encrypter
		 * cookie adapter
		 *
		 * Warning:
		 *  lv_encrypter class is required
		 *
		 * Hint:
		 *  serialization allows the array to be packed into a cookie
		 *
		 * Usage:
		 *  Initialization:
		 *   $cookies=new lv_cookie_encrypter(string_key, [string_cipher='aes-128-cbc'])
		 *  Encrypting cookie:
		 *   $cookies->setcookie()
		 *    where setcookie method takes the same parameters as built-in PHP function
		 *    except for $value - it can be anything if $do_serialization===true
		 *  Decrypting cookie:
		 *   $cookies->getcookie(string_cookie_name)
		 *    returns string or null if cookie not exist
		 *  Optional check_var.php integration:
		 *   $cookies->decrypt(check_cookie('cookie_name'));
		 */

		protected static $do_serialization=true; // constant

		protected $lv_encrypter;

		public function __construct(string $key, string $cipher='aes-128-cbc')
		{
			$this->lv_encrypter=new lv_encrypter($key, $cipher);
		}

		public function setcookie(
			string $name,
			$value='',
			int $expires=0,
			string $path='',
			string $domain='',
			bool $secure=false,
			bool $httponly=false
		){
			return setcookie(
				$name,
				$this->lv_encrypter->encrypt($value, self::$do_serialization),
				$expires,
				$path,
				$domain,
				$secure,
				$httponly
			);
		}
		public function getcookie(string $cookie_name)
		{
			if(isset($_COOKIE[$cookie_name]))
				return $this->decrypt($_COOKIE[$cookie_name]);

			return null;
		}

		public function decrypt(string $content)
		{
			if($content !== null)
				return $this->lv_encrypter->decrypt($content, self::$do_serialization);

			return null;
		}
	}
	final class lv_session_encrypter extends SessionHandler
	{
		/*
		 * Lv encrypter
		 * session adapter
		 *
		 * Works on top of original session handler as middleware
		 *
		 * Warning:
		 *  lv_encrypter class is required
		 *  lv_session_encrypter is a singleton
		 *
		 * Note:
		 *  throws an lv_encrypter_exception on error
		 *
		 * Usage: add before session_start()
		 *  session_set_save_handler(new lv_session_encrypter($key), true)
		 */

		private static $initialized=false;
		private $lv_encrypter;

		public function __construct(string $key, string $cipher='aes-128-cbc')
		{
			if(self::$initialized)
				throw new lv_encrypter_exception(__CLASS__.' is a singleton');
			self::$initialized=true;

			$this->lv_encrypter=new lv_encrypter($key, $cipher);
		}
		public function __destruct()
		{
			self::$initialized=false;
		}
		public function __clone()
		{
			throw new lv_encrypter_exception(__CLASS__.' is a singleton');
		}
		public function __wakeup()
		{
			throw new lv_encrypter_exception(__CLASS__.' is a singleton');
		}

		public function read($id)
		{
			$content=parent::read($id);

			if($content !== '')
				return $this->lv_encrypter->decrypt($content, false);

			return '';
		}
		public function write($id, $content)
		{
			return parent::write($id, $this->lv_encrypter->encrypt($content, false));
		}
	}
	final class lv_cookie_session_handler implements SessionHandlerInterface
	{
		/*
		 * Lv encrypter
		 * session handler
		 *
		 * Uses an encrypted cookie to store the session
		 *
		 * Warning:
		 *  if the cookie cannot be decrypted, on_error will be called
		 *   and a new session will be created automatically
		 *  the cookie expiration date is refreshed with each request
		 *  lv_encrypter class is required
		 *  lv_cookie_session_handler is a singleton
		 *
		 * Note:
		 *  throws an lv_encrypter_exception on error
		 *
		 * Usage:
		 *  lv_cookie_session_handler::register_handler(array_setup_params)
		 *  lv_cookie_session_handler::session_start(array_optional_session_start_params)
		 * where array_setup_params are
		 *  'key'=>'randomstringforlvencrypter' // required
		 *  'cipher'=>'aes-256-gcm' // optional, default: aes-128-cbc, for lv_encrypter, see lv_encrypter::$supported_ciphers
		 *  'on_error'=>function($message){} // optional error logger
		 *  'cookie_id'=>'settings' // optional, cookie name, default: id
		 *  'cookie_expire'=>10 // seconds, optional, default: session.cookie_lifetime
		 */

		private static $initialized=false;
		private $lv_encrypter;
		private $on_error;
		private $cookie_id='id';
		private $cookie_expire=null;

		public function __construct(array $params)
		{
			if(self::$initialized)
				throw new lv_encrypter_exception(__CLASS__.' is a singleton');

			self::$initialized=true;

			if(!isset($params['key']))
				throw new lv_encrypter_exception('No key specified');

			$cipher='aes-128-cbc';
			if(isset($params['cipher']))
				$cipher=$params['cipher'];

			$this->lv_encrypter=new lv_encrypter($params['key'], $cipher);

			foreach(['cookie_id', 'cookie_expire'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->on_error['callback']=function(){};
			if(isset($params['on_error']))
				$this->on_error['callback']=$params['on_error'];
		}
		public function __destruct()
		{
			self::$initialized=false;
		}

		public static function register_handler(array $params)
		{
			$class=__CLASS__;

			if(!$class::$initialized)
				return session_set_save_handler(new $class($params), true);

			return false;
		}
		public static function session_start(array $params=[])
		{
			$class=__CLASS__;

			if(!$class::$initialized)
				throw new lv_encrypter_exception($class.' is not registered - use the '.$class.'::register_handler method');

			$params['use_cookies']=0;
			$params['cache_limiter']='';

			session_id('0');
			return session_start($params);
		}

		public function read($a)
		{
			$session_data='';

			if(isset($_COOKIE[$this->cookie_id]))
				try {
					$session_data=$this->lv_encrypter->decrypt($_COOKIE[$this->cookie_id], false);
				} catch(lv_encrypter_exception $error) {
					$this->on_error['callback'](__CLASS__.' error: '.$error->getMessage().', new session created');
					$session_data='';
				}

			return $session_data;
		}
		public function write($a, $session_data)
		{
			if($session_data !== '')
				$session_data=$this->lv_encrypter->encrypt($session_data, false);

			$cookie_expire=$this->cookie_expire;
			if($cookie_expire === null)
				$cookie_expire=session_get_cookie_params()['lifetime'];

			if($cookie_expire === 0)
				setcookie(
					$this->cookie_id,
					$session_data,
					0,
					'',
					'',
					false,
					true
				);
			else
				setcookie(
					$this->cookie_id,
					$session_data,
					time()+$cookie_expire,
					'',
					'',
					false,
					true
				);

			return true;
		}
		public function destroy($a)
		{
			$this->write(null, '');
			return true;
		}

		public function open($a, $b)
		{
			return true;
		}
		public function close()
		{
			return true;
		}
		public function gc($a) {}
	}
	final class lv_pdo_session_handler implements SessionHandlerInterface
	{
		/*
		 * Lv encrypter
		 * session handler
		 *
		 * Uses a relational database to store an encrypted session
		 *
		 * Warning:
		 *  lv_encrypter class is required
		 *  lv_pdo_session_handler is a singleton
		 *  I don't know why, but this class won't work on linuxes with sqlite (PHP 7.4 and 7.3)
		 *   sqlite throws "disk i/o error", but it works on windows (PHP 7.2)
		 *
		 * Note:
		 *  throws an lv_encrypter_exception on error
		 *
		 * Supported databases:
		 *  PostgreSQL
		 *  MySQL
		 *  SQLite3
		 *
		 * Hint:
		 *  the gc() calls on_error for both the error log and notifications
		 *  the on_error() can write logs to the same database, eg:
				'on_error'=>function($message, $pdo_handler)
				{
					$log_table_name='lv_handler_logs';

					$pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$log_table_name
					.	'('
					.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
					.		'date VARCHAR(25),'
					.		'message VARCHAR(100)'
					.	');'
					.	'INSERT INTO '.$log_table_name
					.	'('
					.		'date,'
					.		'message'
					.	') VALUES ('
					.		'"'.gmdate('Y-m-d H:i:s').'",'
					.		'"'.$message.'"'
					.	')'
					);
				}
		 *
		 * Note:
		 *  is_sid_available and create_sid methods were created
		 *  to make sure that the generated id does not exists in the table.
		 *  If you do not see the need for such a solution,
		 *  you can remove it from the class.
		 *
		 * Usage:
			session_set_save_handler(new lv_pdo_session_handler([
				'key'=>'randomstringforlvencrypter', // required
				'pdo_handler'=>new PDO('sqlite:./lv_pdo_session.sqlite3'), // required
				'table_name'=>'lv_handler_sessions', // optional, default: lv_pdo_session_handler
				'on_error'=>function($message) // optional
				{
					error_log($message);
				}
			]), true);
		 */

		private static $initialized=false;
		private $lv_encrypter;
		private $on_error;
		private $pdo_handler;
		private $table_name='lv_pdo_session_handler';

		public function __construct(array $params)
		{
			if(self::$initialized)
				throw new lv_encrypter_exception(__CLASS__.' is a singleton');

			self::$initialized=true;

			foreach(['pdo_handler', 'key'] as $param)
				if(!isset($params[$param]))
					throw new lv_encrypter_exception('The '.$param.' parameter was not specified for the constructor');

			$cipher='aes-128-cbc';
			if(isset($params['cipher']))
				$cipher=$params['cipher'];

			$this->lv_encrypter=new lv_encrypter($params['key'], $cipher);

			foreach(['pdo_handler', 'table_name'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->on_error['callback']=function(){};
			if(isset($params['on_error']))
				$this->on_error['callback']=$params['on_error'];

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new lv_encrypter_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');
		}
		public function __destruct()
		{
			$this->close();
			self::$initialized=false;
		}

		private function is_sid_available($session_id) // just for my peace of mind
		{
			$data=$this->pdo_handler->prepare(''
			.	'SELECT id '
			.	'FROM '.$this->table_name.' '
			.	'WHERE id=:id'
			);

			if($data === false)
				$this->on_error['callback'](__CLASS__.': PDO prepare error', $this->pdo_handler);

			if(!$data->execute([':id'=>$session_id]))
				$this->on_error['callback'](__CLASS__.': PDO execute error', $this->pdo_handler);

			if(empty($data->fetchAll(PDO::FETCH_ASSOC)))
				return true;

			$this->on_error['callback'](__CLASS__.' error: session id collision with '.$session_id, $this->pdo_handler);

			return false;
		}

		public function open($save_path, $session_name)
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'mysql':
					if($this->pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
					.	'('
					.		'id VARCHAR(30), PRIMARY KEY(id),'
					.		'payload TEXT,'
					.		'last_activity INTEGER'
					.	')'
					) === false)
						return false;
				break;
				case 'pgsql':
				case 'sqlite':
					if($this->pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$this->table_name
					.	'('
					.		'id VARCHAR(30) PRIMARY KEY,'
					.		'payload TEXT,'
					.		'last_activity INTEGER'
					.	')'
					) === false)
						return false;
			}

			return true;
		}
		public function create_sid() // just for my peace of mind
		{
			$SessionHandler=new SessionHandler();
			$session_id=$SessionHandler->create_sid();

			while(!$this->is_sid_available($session_id))
			{
				$session_id=$SessionHandler->create_sid();
				$this->on_error['callback'](__CLASS__.' create_sid: new session id generated', $this->pdo_handler);
			}

			return $session_id;
		}
		public function read($session_id)
		{
			$session_data='';

			$data=$this->pdo_handler->prepare(''
			.	'SELECT payload '
			.	'FROM '.$this->table_name.' '
			.	'WHERE id=:id'
			);

			if($data === false)
				$this->on_error['callback'](__CLASS__.': PDO prepare error', $this->pdo_handler);

			if(!$data->execute([':id'=>$session_id]))
				$this->on_error['callback'](__CLASS__.': PDO execute error', $this->pdo_handler);

			$fetch_data=$data->fetch(PDO::FETCH_ASSOC);

			if($fetch_data !== false)
				try {
					$session_data=$this->lv_encrypter->decrypt($fetch_data['payload'], false);
				} catch(lv_encrypter_exception $error) {
					$this->on_error['callback'](__CLASS__.' error: '.$error->getMessage().', new session created', $this->pdo_handler);
					$session_data='';
				}

			return $session_data;
		}
		public function write($session_id, $session_data)
		{
			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					$data=$this->pdo_handler->prepare(''
					.	'INSERT INTO '.$this->table_name
					.	'('
					.		'id,'
					.		'payload,'
					.		'last_activity'
					.	') VALUES ('
					.		':id,'
					.		':payload,'
					.		time()
					.	')'
					.	'ON CONFLICT(id) DO UPDATE SET '
					.		'payload=:payload,'
					.		'last_activity='.time()
					);
				break;
				case 'mysql':
				case 'sqlite':
					$data=$this->pdo_handler->prepare(''
					.	'REPLACE INTO '.$this->table_name
					.	'('
					.		'id,'
					.		'payload,'
					.		'last_activity'
					.	') VALUES ('
					.		':id,'
					.		':payload,'
					.		time()
					.	')'
					);
			}

			if($data === false)
				$this->on_error['callback'](__CLASS__.': PDO prepare error', $this->pdo_handler);

			return $data->execute([
				':id'=>$session_id,
				':payload'=>$this->lv_encrypter->encrypt($session_data, false)
			]);
		}
		public function close()
		{
			$this->pdo_handler=null;
			return true;
		}
		public function destroy($session_id)
		{
			$data=$this->pdo_handler->prepare(''
			.	'DELETE FROM '.$this->table_name.' '
			.	'WHERE id=:id'
			);

			if($data === false)
				$this->on_error['callback'](__CLASS__.': PDO prepare error', $this->pdo_handler);

			return $data->execute([':id'=>$session_id]);
		}
		public function gc($max_lifetime)
		{
			$max_lifetime=time()-intval($max_lifetime);
			$result=$this->pdo_handler->exec(''
			.	'DELETE FROM '.$this->table_name.' '
			.	'WHERE last_activity<'.$max_lifetime
			);

			if($result === false)
			{
				$this->on_error['callback'](__CLASS__.' error: gc query failed', $this->pdo_handler);
				return false;
			}

			$this->on_error['callback'](__CLASS__.' gc: '.$result.' sessions removed', $this->pdo_handler);

			return true;
		}
	}
	final class lv_redis_session_handler implements SessionHandlerInterface
	{
		/*
		 * Lv encrypter
		 * session handler
		 *
		 * Uses Redis to store an encrypted session
		 *
		 * Warning:
		 *  lv_encrypter class is required
		 *  lv_redis_session_handler is a singleton
		 *
		 * Note:
		 *  is_sid_available and create_sid methods were created
		 *   to make sure that the generated id does not exists in the table.
		 *   if you do not see the need for such a solution,
		 *   you can remove it from the class
		 *  throws an lv_encrypter_exception on error
		 *
		 * Usage:
			$redis_handler=new Redis();
			$redis_handler->connect('127.0.0.1', 6379);
			session_set_save_handler(new lv_redis_session_handler([
				'key'=>'randomstringforlvencrypter', // required
				'redis_handler'=>$redis_handler, // required
				'prefix'=>'lv_session__', // optional, default: lv_pdo_session_handler
				'on_error'=>function($message) // optional
				{
					error_log($message);
				}
			]), true);
		 */

		private static $initialized=false;
		private $lv_encrypter;
		private $on_error;
		private $redis_handler;
		private $prefix='lv_redis_session_handler__';

		public function __construct(array $params)
		{
			if(self::$initialized)
				throw new lv_encrypter_exception(__CLASS__.' is a singleton');

			self::$initialized=true;

			foreach(['redis_handler', 'key'] as $param)
				if(!isset($params[$param]))
					throw new lv_encrypter_exception('The '.$param.' parameter was not specified for the constructor');

			$cipher='aes-128-cbc';
			if(isset($params['cipher']))
				$cipher=$params['cipher'];

			$this->lv_encrypter=new lv_encrypter($params['key'], $cipher);

			foreach(['redis_handler', 'prefix'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			$this->on_error['callback']=function(){};
			if(isset($params['on_error']))
				$this->on_error['callback']=$params['on_error'];
		}
		public function __destruct()
		{
			$this->close();
			self::$initialized=false;
		}

		private function is_sid_available($session_id) // just for my peace of mind
		{
			if($this->redis_handler->get($this->prefix.$session_id) === false)
				return true;

			$this->on_error['callback'](__CLASS__.' error: session id collision with '.$session_id, $this->pdo_handler);

			return false;
		}

		public function open($save_path, $session_name)
		{
			return true;
		}
		public function create_sid() // just for my peace of mind
		{
			$SessionHandler=new SessionHandler();
			$session_id=$SessionHandler->create_sid();

			// if method defined
			while(!$this->is_sid_available($session_id))
			{
				$session_id=$SessionHandler->create_sid();
				$this->on_error['callback'](__CLASS__.' create_sid: new session id generated', $this->redis_handler);
			}

			return $session_id;
		}
		public function read($session_id)
		{
			$session_data='';
			$data=$this->redis_handler->get($this->prefix.$session_id);

			if($data === false)
				$this->on_error['callback'](__CLASS__.': key does not exists, new session created', $this->redis_handler);

			try {
				$session_data=$this->lv_encrypter->decrypt($data, false);
			} catch(lv_encrypter_exception $error) {
				$this->on_error['callback'](__CLASS__.' error: '.$error->getMessage().', new session created', $this->redis_handler);
				$session_data='';
			}

			return $session_data;
		}
		public function write($session_id, $session_data)
		{
			return $this->redis_handler->set(
				$this->prefix.$session_id,
				$this->lv_encrypter->encrypt($session_data, false),
				['ex'=>ini_get('session.gc_maxlifetime')]
			);
		}
		public function close()
		{
			$this->redis_handler=null;
			return true;
		}
		public function destroy($session_id)
		{
			return $this->redis_handler->del($this->prefix.$session_id);
		}
		public function gc($max_lifetime)
		{
			return true;
		}
	}
?>