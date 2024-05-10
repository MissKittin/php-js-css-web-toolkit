<?php
	class mock_session implements SessionHandlerInterface
	{
		public function open($save_path, $session_name)
		{
			return true;
		}
		public function create_sid()
		{
			return '0';
		}
		public function read($session_id)
		{
			return '';
		}
		public function write($session_id, $session_data)
		{
			return true;
		}
		public function close()
		{
			return true;
		}
		public function destroy($session_id)
		{
			return true;
		}
		public function gc($max_lifetime)
		{
			return true;
		}
	}
	session_set_save_handler(new mock_session(), true);
	session_start();
	echo ' -> Session mocked'.PHP_EOL;

	echo ' -> Mocking functions';
		function is_logged()
		{
			return true;
		}
		function csrf_check_token()
		{
			return true;
		}
		function logout()
		{
			return true;
		}
		function check_post()
		{
			return true;
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including main.php';
		try {
			if(@(include __DIR__.'/../main.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'
				.PHP_EOL.PHP_EOL
				.'Caught: '.$error->getMessage()
				.PHP_EOL;

			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Setting up component';
		login_com_reg_config::_()['on_logout']=function()
		{
			echo ' -> Logged out [ OK ]'.PHP_EOL;
			exit();
		};
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Executing login_com()'.PHP_EOL;
		ob_start();
		try {
			login_com();
		} catch(Throwable $error) {
			echo ' [FAIL]'
				.PHP_EOL.PHP_EOL
				.'Caught: '.$error->getMessage()
				.PHP_EOL;

			exit(1);
		}
		ob_end_clean();

	echo ' -> Not logged out [FAIL]'.PHP_EOL;
		exit(1);
?>