<?php
	/*
	 * stream_isatty() polyfill
	 *
	 * Sources:
	 *  https://github.com/symfony/polyfill-php72/blob/1.x/Php72.php
	 * License: MIT
	 */

	if(!function_exists('stream_isatty'))
	{
		function stream_isatty($stream)
		{
			if(!is_resource($stream))
			{
				trigger_error('stream_isatty() expects parameter 1 to be resource, '.gettype($stream).' given', E_USER_WARNING);
				return false;
			}

			if(DIRECTORY_SEPARATOR === '\\')
			{
				$stat=fstat($stream);

				if($stat === false)
					return false;

				return (($stat['mode'] & 0170000) === 0020000);
			}

			if(!function_exists('posix_isatty'))
				return false;

			return posix_isatty($stream);
		}
	}
?>