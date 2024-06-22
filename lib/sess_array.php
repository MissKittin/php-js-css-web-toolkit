<?php
	class sess_array_exception extends Exception {}
	class sess_array implements SessionHandlerInterface
	{
		/*
		 * Store the session in an array
		 * Mainly for testing purposes
		 *
		 * Usage:
			sess_array::register_handler();

			// slot #0
			sess_array::session_start(array_optional_session_start_params);
			$_SESSION['variable']='slot0';
			session_write_close();

			// slot #1
			session_id('1');
			sess_array::session_start(array_optional_session_start_params);
			$_SESSION['variable']='slot1';
			session_write_close();

			// back to the slot #0
			session_id('0');
			sess_array::session_start(array_optional_session_start_params);
			echo $_SESSION['variable']; // 'slot0'
			session_destroy();

			// back to the slot #1
			session_id('1');
			sess_array::session_start(array_optional_session_start_params);
			echo $_SESSION['variable']; // 'slot1'
			session_destroy();
		 */

		protected static $initialized=false;

		protected $session_data=[];

		public static function register_handler()
		{
			if(!static::$initialized)
				return session_set_save_handler(new static(), true);

			return false;
		}
		public static function session_start(array $params=[])
		{
			if(!static::$initialized)
				throw new sess_array_exception(static::class.' is not registered - use the '.static::class.'::register_handler method');

			$params['use_cookies']=0;
			$params['cache_limiter']='';

			return session_start($params);
		}

		public function __construct()
		{
			if(static::$initialized)
				throw new sess_array_exception(static::class.' is a singleton');

			static::$initialized=true;
		}

		public function open($save_path, $session_name)
		{
			return true;
		}
		public function create_sid()
		{
			return (string)count($this->session_data);
		}
		public function read($session_id)
		{
			if(isset($this->session_data[$session_id]))
				return $this->session_data[$session_id];

			return '';
		}
		public function write($session_id, $session_data)
		{
			$this->session_data[$session_id]=$session_data;
			return true;
		}
		public function close()
		{
			return true;
		}
		public function destroy($session_id)
		{
			$this->session_data[$session_id]='';
			return true;
		}
		public function gc($max_lifetime)
		{
			return true;
		}
	}
?>