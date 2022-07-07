<?php
	/*
	 * CSRF prevention library
	 * from simpleblog and server-admin-page/webadmin projects
	 *
	 * Note:
	 * 	token is generated at include and stored in the $_SESSION
	 *
	 * Warning:
	 *  you must start session before include
	 *  $_GET['_csrf_token'] and $_POST['_csrf_token'] are reserved
	 *  $_SESSION['_csrf_token'] is reseved
	 *
	 * HTML form:
	 *  <input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	 * GET token checking:
	 *  csrf_check_token('get')
	 *  note: forget about it for HTML forms
	 * POST token checking:
	 *  csrf_check_token('post')
	*/

	function csrf_checkToken(string $method)
	{
		switch($method)
		{
			case 'get':
				if(
					isset($_GET['_csrf_token']) &&
					($_SESSION['_csrf_token'] === $_GET['_csrf_token'])
				)
					return true;
			break;
			case 'post':
				if(
					isset($_POST['_csrf_token']) &&
					($_SESSION['_csrf_token'] === $_POST['_csrf_token'])
				)
					return true;
		}

		return false;
	}
	function csrf_printToken(string $parameter)
	{
		switch($parameter)
		{
			case 'parameter':
				return '_csrf_token';
			break;
			case 'value':
				return $_SESSION['_csrf_token'];
		}

		return false;
	}

	function csrf_check_token(string $method)
	{
		return csrf_checkToken($method);
	}
	function csrf_print_token(string $parameter)
	{
		return csrf_printToken($parameter);
	}

	if(session_status() !== PHP_SESSION_ACTIVE)
		throw new Exception('Session not started');

	if((!csrf_checkToken('get')) && (!csrf_checkToken('post')))
		$_SESSION['_csrf_token']=substr(
			base_convert(
				sha1(
					uniqid(mt_rand())
				),
				16, 36
			),
			0, 32
		);
?>