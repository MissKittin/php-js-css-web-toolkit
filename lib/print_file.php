<?php
	function print_file($path, $cache=false)
	{
		/*
		 * Send file with optional HTTP cache headers
		 *
		 * Usage:
		 *  print_file('./image.png', '3600');
		 */

		if(file_exists($path))
		{
			switch(pathinfo($path, PATHINFO_EXTENSION))
			{
				case 'css': $file_type='text/css'; break;
				default: $file_type=mime_content_type($path); break;
			}
			header('Content-type: ' . $file_type);
			header('Content-length: ' . filesize($path));

			if($cache !== false)
			{
				header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache) . ' GMT');
				header('Pragma: cache');
				header('Cache-Control: max-age='.$cache);
			}

			while(ob_get_level()) ob_end_clean();
			readfile($path);

			return true;
		}

		return false;
	}
?>