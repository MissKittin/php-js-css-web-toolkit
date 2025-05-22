<?php
	/*
	 * fastcgi_finish_request emulation for PHP built-in server
	 *
	 * Note:
	 *  this code uses some strange PHP behavior
	 *  in some cases it may not fulfill its function
	 *
	 * Warning:
	 *  output buffers started before the library was included will be removed
	 *
	 * Source: https://stackoverflow.com/a/141026
	 */

	if(!function_exists('fastcgi_finish_request'))
	{
		function fastcgi_finish_request()
		{
			static $calls=1;

			if($calls > 1)
			{
				trigger_error(
					__FUNCTION__.' called '.$calls.' times - remove unnecessary calls',
					E_USER_NOTICE
				);

				++$calls;

				return;
			}

			if(headers_sent())
			{
				trigger_error(
					__FUNCTION__.': headers already sent - check output buffer',
					E_USER_WARNING
				);

				return;
			}

			header('Connection: close');

			ignore_user_abort(true);

			if(session_status() === PHP_SESSION_ACTIVE)
				session_write_close();

			while(ob_get_level() > 1)
				ob_end_flush();

			if(ob_get_level() === 1)
			{
				header('Content-Length: '.ob_get_length());
				ob_end_flush();
			}

			flush();

			++$calls;
		}

		while(ob_get_level() > 0)
			ob_end_clean();

		ob_start();
	}
?>