<?php
	class print_file_exception extends Exception {}
	function print_file(string $path, array $options=[])
	{
		/*
		 * Send file with optional HTTP cache headers
		 *
		 * Warning:
		 *  cleans the output buffer and turns off output buffering
		 *
		 * Note:
		 *  returns false if $path is not a file
		 *  throws an print_file_exception on error
		 *
		 * Usage (simple):
			print_file('./image.png');
			// the file has already been sent - quitting time
			exit();
		 *
		 * Usage (with options):
			print_file('./image.png', [
				'cache'=>3600, // expiration time, 0 means do not cache, optional default: 0
				'mimes'=>[ // mime_content_type() may be wrong, you can correct it here, optional
					'js'=>'text/javascript',
					'mp3'=>'audio/mpeg'
				]
			]);
			exit();
		 */

		if(!is_file($path))
			return false;

		if(!isset($options['cache']))
			$options['cache']=0;

		if(!is_int($options['cache']))
			throw new print_file_exception(
				'cache parameter is not an integer'
			);

		if(!isset($options['mimes']))
			$options['mimes']=[
				'css'=>'text/css',
				'html'=>'text/html'
			];

		$file_extension=pathinfo($path, PATHINFO_EXTENSION);

		if($file_extension === '')
			$file_extension='__NOEXTENSION__';

		if(!isset(
			$options['mimes'][$file_extension]
		))
			$options['mimes'][$file_extension]=mime_content_type($path);

		header(''
		.	'Content-Type: '
		.	$options['mimes'][$file_extension]
		);
		header('Content-Length: '.filesize($path));

		if($options['cache'] === 0)
		{
			header('Expires: 0');
			header('Cache-Control: no-store, no-cache, must-revalidate');
		}
		else
		{
			header('Expires: '.gmdate(
				'D, d M Y H:i:s',
				time()+$options['cache']
			).' GMT');
			header(''
			.	'Cache-Control: max-age='
			.	$options['cache']
			);
		}

		while(ob_get_level())
			ob_end_clean();

		readfile($path);

		return true;
	}
?>