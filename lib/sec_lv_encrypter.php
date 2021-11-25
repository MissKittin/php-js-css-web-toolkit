<?php
	/*
	 * Lv encrypter
	 * Laravel Encrypter class
	 *  with adapters and session handlers
	 *
	 * Warning:
	 *  the key may leak through stack trace!!! - please display_errors=off
	 *   or your app will be compromised!!!
	 *  this library requires the OpenSSL (>=1.1.0g) and mbstring extensions
	 *
	 * Classes:
	 *  lv_encrypter
	 *   main class - content encryptor/decryptor and key generator
	 *   from laravel framework
	 *   distributed under the MIT license
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
	 */

	class lv_encrypter
	{
		/*
		 * Lv encrypter
		 * main class
		 *
		 * Warning:
		 *  OpenSSL (>=1.1.0g) and mbstring extensions are required
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
		 *
		 * Source: https://github.com/illuminate/encryption/blob/master/Encrypter.php
		 * License: MIT
		 */

		private static $supported_ciphers=array(
			'aes-128-cbc'=>array(
				'size'=>16,
				'aead'=>false
			),
			'aes-256-cbc'=>array(
				'size'=>32,
				'aead'=>false
			),
			'aes-128-gcm'=>array(
				'size'=>16,
				'aead'=>true
			),
			'aes-256-gcm'=>array(
				'size'=>32,
				'aead'=>true
			)
		);

		private $key;
		private $cipher;

		public function __construct($key, $cipher='aes-128-cbc')
		{
			$key=base64_decode($key);
			$cipher=strtolower($cipher);

			if(!isset(self::$supported_ciphers[$cipher]))
				throw new Exception($cipher.' cipher is not supported');
			if(mb_strlen($key, '8bit') !== self::$supported_ciphers[$cipher]['size'])
				throw new Exception('key length is invalid');

			$this->key=$key;
			$this->cipher=$cipher;
		}

		public static function generate_key($cipher='aes-128-cbc')
		{
			$cipher=strtolower($cipher);

			if(isset(self::$supported_ciphers[$cipher]))
				return base64_encode(random_bytes(self::$supported_ciphers[$cipher]['size']));
			throw new Exception($cipher.' cipher is not supported');
		}

		public function encrypt($content, $serialize=true)
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
				throw new Exception('could not encrypt the data');

			$iv=base64_encode($iv);
			$tag=base64_encode($tag);
			if(self::$supported_ciphers[$this->cipher]['aead'])
				$mac='';
			else
				$mac=hash_hmac('sha256', $iv.$content, $this->key);

			$json=json_encode(compact('iv', 'content', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);
			if(json_last_error() !== JSON_ERROR_NONE)
				throw new Exception('could not encrypt the data');

			return base64_encode($json);
		}
		public function decrypt($payload, $unserialize=true)
		{
			$payload=json_decode(base64_decode($payload), true);
			if
			(!(
				is_array($payload) &&
				isset($payload['iv'], $payload['content'], $payload['mac']) &&
				(strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher))
			))
				throw new Exception('the payload is invalid');
			if
			(
				(!self::$supported_ciphers[$this->cipher]['aead']) &&
				(!hash_equals(hash_hmac('sha256', $payload['iv'].$payload['content'], $this->key), $payload['mac']))
			)
				throw new Exception('the MAC is invalid');

			$iv=base64_decode($payload['iv']);
			if(empty($payload['tag']))
				$tag='';
			else
				$tag=base64_decode($payload['tag']);
			if((self::$supported_ciphers[$this->cipher]['aead']) && (strlen($tag) !== 16))
				throw new Exception('could not decrypt the data');

			$decrypted=openssl_decrypt($payload['content'], $this->cipher, $this->key, 0, $iv, $tag);
			if($decrypted === false)
				throw new Exception('could not decrypt the data');

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

		private static $do_serialization=true; // constant

		private $lv_encrypter;

		public function __construct($key, $cipher='aes-128-cbc')
		{
			$this->lv_encrypter=new lv_encrypter($key, $cipher);
		}

		public function setcookie(string $name, $value='', int $expires=0, string $path='', string $domain='', bool $secure=false, bool $httponly=false)
		{
			return setcookie($name, $this->lv_encrypter->encrypt($value, self::$do_serialization), $expires, $path, $domain, $secure, $httponly);
		}
		public function getcookie($cookie_name)
		{
			if(isset($_COOKIE[$cookie_name]))
				return $this->decrypt($_COOKIE[$cookie_name]);
			return null;
		}

		public function decrypt($content)
		{
			if($content !== null)
				return $this->lv_encrypter->decrypt($content, self::$do_serialization);
			return null;
		}
	}
	class lv_session_encrypter extends SessionHandler
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
		 * Usage: add before session_start()
		 *  session_set_save_handler(new lv_session_encrypter($key), true)
		 */

		private static $initialized=false;
		private $lv_encrypter;

		public function __construct($key, $cipher='aes-128-cbc')
		{
			if(self::$initialized)
				throw new Exception(__CLASS__.' is a singleton');
			self::$initialized=true;

			$this->lv_encrypter=new lv_encrypter($key, $cipher);
		}
		public function __destruct()
		{
			self::$initialized=false;
		}
		public function __clone()
		{
			throw new Exception(__CLASS__.' is a singleton');
		}
		public function __wakeup()
		{
			throw new Exception(__CLASS__.' is a singleton');
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
	class lv_cookie_session_handler implements SessionHandlerInterface
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
		 * Usage: just start a session by calling the static method
		 *  lv_cookie_session_handler::session_start(array_setup_params)
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
		private $cookie_expire;

		public function __construct(array $params)
		{
			if(self::$initialized)
				throw new Exception(__CLASS__.' is a singleton');
			self::$initialized=true;

			if(!isset($params['key']))
				throw new Exception('no key specified');
			$cipher='aes-128-cbc';
			if(isset($params['cipher']))
				$cipher=$params['cipher'];
			$this->lv_encrypter=new lv_encrypter($params['key'], $cipher);

			$this->cookie_expire=session_get_cookie_params()['lifetime'];
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

		public static function session_start($params)
		{
			$class=__CLASS__;
			session_set_save_handler(new $class($params), true);
			session_id('0');
			return session_start(['use_cookies'=>0, 'cache_limiter'=>'']);
		}

		public function read($a)
		{
			$session_data='';

			if(isset($_COOKIE[$this->cookie_id]))
				try {
					$session_data=$this->lv_encrypter->decrypt($_COOKIE[$this->cookie_id], false);
				} catch(Exception $error) {
					$this->on_error['callback'](__CLASS__.' error: '.$error->getMessage().', new session created');
					$session_data='';
				}

			return $session_data;
		}
		public function write($a, $session_data)
		{
			if($session_data !== '')
				$session_data=$this->lv_encrypter->encrypt($session_data, false);

			setcookie($this->cookie_id, $session_data, time()+$this->cookie_expire, '', '', false, true);
			return true;
		}
		public function destroy($a)
		{
			$this->write(null, '');
			return true;
		}

		public function open($a, $b) { return true; }
		public function close() { return true; }
		public function gc($a) {}
	}
	class lv_pdo_session_handler implements SessionHandlerInterface
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
		 *
		 * Hint:
		 *  the gc() calls on_error for both the error log and notifications
		 *  the on_error() can write logs to the same database, eg:
				'on_error'=>function($message, $pdo_handler)
				{
					$log_table_name='lv_handler_logs';
					$pdo_handler->exec('
						CREATE TABLE IF NOT EXISTS '.$log_table_name.'
						(
							id INTEGER PRIMARY KEY AUTOINCREMENT,
							date VARCHAR(25),
							message VARCHAR(100)
						);
						INSERT INTO '.$log_table_name.'(date, message)
						VALUES
						(
							"'.gmdate('Y-m-d H:i:s').'",
							"'.$message.'"
						)
					');
				}
		 *
		 * Note:
		 *  is_sid_available and create_sid methods were created
		 *  to make sure that the generated id does not exists in the table.
		 *  If you do not see the need for such a solution,
		 *  you can remove it from the class.
		 *
		 * Usage:
			session_set_save_handler(new lv_pdo_session_handler(array(
				'key'=>'randomstringforlvencrypter', // required
				'pdo_handler'=>new PDO('sqlite:./lv_pdo_session.sqlite3'), // required
				'table_name'=>'lv_handler_sessions', // optional, default: lv_pdo_session_handler
				'on_error'=>function($message) // optional
				{
					error_log($message);
				}
			)), true);
		 */

		private static $initialized=false;
		private $lv_encrypter;
		private $on_error;
		private $pdo_handler;
		private $table_name='lv_pdo_session_handler';

		public function __construct(array $params)
		{
			if(self::$initialized)
				throw new Exception(__CLASS__.' is a singleton');
			self::$initialized=true;

			foreach(['pdo_handler', 'key'] as $param)
				if(!isset($params[$param]))
					throw new Exception('the '.$param.' parameter was not specified for the constructor');

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
		}
		public function __destruct()
		{
			$this->close();
			self::$initialized=false;
		}

		private function is_sid_available($session_id) // just for my peace of mind
		{
			$data=$this->pdo_handler->prepare('SELECT id FROM '.$this->table_name.' WHERE id=:id');
			$data->execute(array(':id'=>$session_id));

			if(empty($data->fetchAll(PDO::FETCH_ASSOC)))
				return true;

			$this->on_error['callback'](__CLASS__.' error: session id collision with '.$session_id, $this->pdo_handler);
			return false;
		}

		public function open($save_path, $session_name)
		{ 
			if($this->pdo_handler->exec('
				CREATE TABLE IF NOT EXISTS '.$this->table_name.'
				(
					id VARCHAR(30) PRIMARY KEY,
					payload VARCHAR(255),
					last_activity INTEGER
				)
			') === false)
				return false;
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

			$data=$this->pdo_handler->prepare('SELECT payload FROM '.$this->table_name.' WHERE id=:id');
			$data->execute(array(':id'=>$session_id));
			$data=$data->fetch(PDO::FETCH_ASSOC);

			if($data !== false)
				try {
					$session_data=$this->lv_encrypter->decrypt($data['payload'], false);
				} catch(Exception $error) {
					$this->on_error['callback'](__CLASS__.' error: '.$error->getMessage().', new session created', $this->pdo_handler);
					$session_data='';
				}

			return $session_data;
		}
		public function write($session_id, $session_data)
		{
			$data=$this->pdo_handler->prepare('
				REPLACE INTO '.$this->table_name.'(id, payload, last_activity)
				VALUES(:id, :payload, '.time().')
			');
			return $data->execute(array(
				':id'=>$session_id,
				':payload'=>$this->lv_encrypter->encrypt($session_data, false)
			));
		}
		public function close()
		{
			$this->pdo_handler=null;
			return true;
		}
		public function destroy($session_id)
		{
			$data=$this->pdo_handler->prepare('DELETE FROM '.$this->table_name.' WHERE id=:id');
			return $data->execute(array(':id'=>$session_id));
		}
		public function gc($max_lifetime)
		{
			$max_lifetime=time()-intval($max_lifetime);
			$result=$this->pdo_handler->exec('DELETE FROM '.$this->table_name.' WHERE last_activity<'.$max_lifetime);

			if($result === false)
			{
				$this->on_error['callback'](__CLASS__.' error: gc query failed', $this->pdo_handler);
				return false;
			}

			$this->on_error['callback'](__CLASS__.' gc: '.$result.' sessions removed', $this->pdo_handler);
			return true;
		}
	}
?>