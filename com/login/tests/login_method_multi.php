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
			return false;
		}
		function login_multi()
		{
			return true;
		}
		function login_refresh()
		{
			return null;
		}
		function logout()
		{
			return false;
		}
		function csrf_check_token()
		{
			return true;
		}
		function csrf_print_token()
		{
			return '';
		}
		function check_post()
		{
			return 'value';
		}
		function check_session()
		{
			return null;
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
		login_com_reg_config::_()['method']='login_multi';
		login_com_reg_config::_()['on_login_success']=function()
		{
			echo ' -> Login success'.PHP_EOL;
		};
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Executing login_com()'.PHP_EOL;
		ob_start();
		try {
			login_com();
		} catch(Throwable $error) {
			echo ' <- Executing login_com() [FAIL]'
				.PHP_EOL.PHP_EOL
				.'Caught: '.$error->getMessage()
				.PHP_EOL;

			exit(1);
		}
		ob_end_clean();

	echo ' -> Login failed'.PHP_EOL;
		exit(1);
?>