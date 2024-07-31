<?php
	function print_file(string $path, int $cache=0)
	{
		/*
		 * Send file with optional HTTP cache headers
		 *
		 * Warning:
		 *  cleans the output buffer and turns off output buffering
		 *
		 * Note:
		 *  returns false if file does not exist or is directory
		 *
		 * Usage:
			print_file('./image.png', 3600);
		 * where
		 *  3600 is the cache expiration time (0 means do not cache)
		 */

		if(!file_exists($path))
			return false;

		if(is_dir($path))
			return false;

		switch(pathinfo($path, PATHINFO_EXTENSION))
		{
			case 'css':
				$file_type='text/css';
			break;
			default:
				$file_type=mime_content_type($path);
		}

		header('Content-type: '.$file_type);
		header('Content-length: '.filesize($path));

		if($cache !== 0)
		{
			header('Expires: '.gmdate('D, d M Y H:i:s', time()+$cache).' GMT');
			header('Pragma: cache');
			header('Cache-Control: max-age='.$cache);
		}

		while(ob_get_level())
			ob_end_clean();

		readfile($path);

		return true;
	}
?>