<?php
	/*
	 * CSRF prevention library
	 * from simpleblog and server-admin-page/webadmin projects
	 * token is generated at include and stored in the $_SESSION
	 *
	 * Warning:
	 *  you must start session before include
	 *  $_SESSION['csrf_token'] is reseved
	 *
	 * HTML form:
	 *  <input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	 * GET token checking:
	 *  csrf_check_token('get')
	 *  note: forget about it for HTML forms
	 * POST token checking:
	 *  csrf_check_token('post')
	*/

	function csrf_checkToken($method)
	{
		if(isset($_SESSION['csrf_token']))
			switch($method)
			{
				case 'get':
					if(isset($_GET['csrf_token']))
						if($_SESSION['csrf_token'] === $_GET['csrf_token'])
							return true;
				break;
				case 'post':
					if(isset($_POST['csrf_token']))
						if($_SESSION['csrf_token'] === $_POST['csrf_token'])
							return true;
				break;
			}

		return false;
	}
	function csrf_printToken($parameter)
	{
		switch($parameter)
		{
			case 'parameter': return 'csrf_token'; break;
			case 'value': return $_SESSION['csrf_token']; break;
		}

		return false;
	}

	if(session_status() !== PHP_SESSION_ACTIVE)
		throw new Exception('session not started');

	if((!csrf_checkToken('get')) && (!csrf_checkToken('post')))
		$_SESSION['csrf_token']=substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 32);

	// snake_case wrappers (camelCase for backward compatibility with my projects)
	function csrf_check_token($method) { return csrf_checkToken($method); }
	function csrf_print_token($parameter) { return csrf_printToken($parameter); }
?>