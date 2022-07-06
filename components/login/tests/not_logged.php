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

	echo ' -> Setting up component';
		$GLOBALS['not_logged']=false;
		$GLOBALS['login']['config']['on_login_prompt']=function()
		{
			$GLOBALS['not_logged']=true;
		};
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including login.php';
		ob_start();
		try {
			if(@(include __DIR__.'/../login.php') === false)
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
		ob_end_clean();
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Checking on_login_prompt callback';
		if($GLOBALS['not_logged'])
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Checking if views/form.php was included';
		$found=false;
		$strlen=strlen(realpath(__DIR__.'/..'))+1;
		foreach(get_included_files() as $file)
			if(strtr(substr($file, $strlen), '\\', '/') === 'views/form.php')
			{
				$found=true;
				break;
			}
		if($found)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>