<?php
	function ob_file_cache($output_file, $expire=3600)
	{
		/*
		 * Cache output buffer to the file
		 *
		 * Warning:
		 *  this library may conflict with other libraries that uses output buffer control
		 *  $GLOBALS['file_cache'] is reserved
		 *
		 * Usage:
			if(ob_file_cache('./tmp/cache_'.str_replace('/', '___', strtok($_SERVER['REQUEST_URI'], '?')), 3600) === 0)
				exit();
		 * where 3600 is the validity time of the cache and can be omitted
		 *  if this parameter is 0 cache won't be refreshed
		 * note: it is up to you to decide where in the application this function is called
		 *
		 * Return codes:
		 *  0 -> cache is valid and has just been sent to the client
		 *  1 -> cache file will be created on exit
		 *  2 -> cache file will be refreshed on exit
		 */

		global $file_cache;
		$file_cache['output_file']=$output_file;
		$file_cache['cwd']=getcwd();
		$generate=function()
		{
			if(!ob_get_level())
				ob_start();

			register_shutdown_function(function(){
				global $file_cache;
				chdir($file_cache['cwd']);
				if(file_put_contents($file_cache['output_file'], ob_get_contents()) === false)
					throw new Exception('cannot write to the '.$file_cache['output_file']);
				ob_end_flush();
			});
		};

		$output_dir=dirname($output_file);
		@mkdir($output_dir, 0777, true);
		if(!is_dir($output_dir))
			throw new Exception($output_dir.' is not a directory');

		if(file_exists($output_file))
		{
			if
			(
				($expire !== 0)
				&&
				((time()-filemtime($output_file)) > $expire)
			)
			{
				$generate();
				return 2;
			}

			readfile($output_file);
			return 0;
		}

		$generate();
		return 1;
	}
?>