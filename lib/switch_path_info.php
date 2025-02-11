<?php
	function switch_path_info(
		string $request_uri,
		string $script_filename,
		string $document_root
	){
		/*
		 * $_SERVER["PATH_INFO"] replacement
		 * If the application's public directory is in subdirectories
		 * removes them from the path
		 *
		 * Note:
		 *  on cli-server if the file doesn't exist
		 *  the $_SERVER['SCRIPT_FILENAME'] will contain
		 *  the path to the router file
		 *  in this case the function won't work
		 *  but this is not a bug - it's a feature
		 *
		 * Usage:
			switch(switch_path_info(
				$_SERVER['REQUEST_URI'],
				$_SERVER['SCRIPT_FILENAME'],
				$_SERVER['DOCUMENT_ROOT']
			)){
				case 'arg1/arg2/arg3':
					//
				break;
				case '':
					// home page
				break;
				default:
					// 404 Not found
			}
		 */

		$script_relative_path=strtr(substr(
			dirname($script_filename),
			strlen($document_root)
		), '\\', '/');

		if(strcmp($script_relative_path, $request_uri) === 1)
			$i_len=strlen($request_uri);
		else
			$i_len=strlen($script_relative_path);

		for($i=0; $i<$i_len; ++$i)
		{
			if($script_relative_path[$i] === $request_uri[$i])
				continue;

			break;
		}

		if($i !== 0)
			$request_uri=substr($request_uri, $i);

		if(substr($request_uri, 0, 1) === '?')
			return '';

		return trim(
			strtok($request_uri, '?'),
			'/'
		);
	}
?>