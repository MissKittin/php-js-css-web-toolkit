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
		}

		while(ob_get_level() > 0)
			ob_end_clean();

		ob_start();
	}
?>