<?php
	function file_cache($input=null)
	{
		/*
		 * Output cache library - cache to file
		 *
		 * Usage methods:
		 * 1) as function - pass options as function argument
		 * 2) as block of code - pass options via global variable (remove function tag and "global" tags)
		 *
		 * Usage:
		 * (new way) add after header() function in controller (as function):
			include './lib/file_cache.php';
			if(file_cache(array(
				'cache_file_url'=>$APP_ROUTER[1]
			))['status'] === 0)
				exit();
		 * (old way) add after header() function in controller (as block of code):
		 *  include './lib/file_cache.php'; if($_lib_file_cache['status'] === 0) exit();
		 *
		 * Warning:
		 * this library may conflict with other libraries that uses output buffer control
		 * $_lib_file_cache must be global variable (or passed as function argument)
		 *
		 * Settings:
		 *  if $_lib_file_cache global array is not defined, default values will be used
		 *  $_lib_file_cache['cache_patch']
		 *   directory for cache files
		 *   will be created if not exists
		 *   default value: ./tmp
		 *  $_lib_file_cache['cache_file_extension']
		 *   will be append to the cache file name
		 *  $_lib_file_cache['cache_file_url']
		 *   cache file name
		 *   default value: $APP_ROUTER[1] . '.html' (see line 61)
		 *  $_lib_file_cache['cache_index_file']
		 *   cache file name if $_lib_file_cache['cache_file_url'] is empty
		 *  $_lib_file_cache['expire']
		 *   cache expiration in seconds
		 *   default value: 3600
		 *   0 is infinity
		 *  you can pass these values as function parameter, eg:
		 *   file_cache(['setting_a'=>'value_a', 'setting_b'=>'value_b'])
		 *
		 * $_lib_file_cache['status']:
		 *  0 - cache is valid
		 *  1 - cache file created
		 *  2 - cache file refreshed
		 *  3 - cache code not executed - $_lib_file_cache['cache_patch'] is not a directory
		 * this array will be returned by this function
		 *
		 * If page is blank, it can be problem with permissions. Check server logs.
		*/

		global $_lib_file_cache; // remove this if you want block of code
		if($input !== null) // remove this if you want block of code
			$_lib_file_cache=$input; // remove this if you want block of code

		if(!isset($_lib_file_cache['cache_patch'])) $_lib_file_cache['cache_patch']='./tmp';
		if(!isset($_lib_file_cache['cache_file_extension'])) $_lib_file_cache['cache_file_extension']='.html';
		//if(!isset($_lib_file_cache['cache_file_url'])) $_lib_file_cache['cache_file_url']=$APP_ROUTER[1]; // $APP_ROUTER not exists in this function. Uncomment this  if you want block of code
		if(!isset($_lib_file_cache['cache_index_file'])) $_lib_file_cache['cache_index_file']='index';
		if(!isset($_lib_file_cache['expire'])) $_lib_file_cache['expire']=3600;

		$_lib_file_cache['cwd']=getcwd();
		$_lib_file_cache['status']=3;
		$_lib_file_cache['generate']=function()
		{
			if(!ob_get_level()) ob_start();
			register_shutdown_function(function(){
				global $_lib_file_cache;

				chdir($_lib_file_cache['cwd']);
				file_put_contents($_lib_file_cache['cache_file'], ob_get_contents());

				ob_end_flush();
			});
		};

		if(!file_exists($_lib_file_cache['cache_patch']))
			mkdir($_lib_file_cache['cache_patch']);

		if(is_dir($_lib_file_cache['cache_patch']))
		{
			if($_lib_file_cache['cache_file_url'] === '')
				$_lib_file_cache['cache_file']=$_lib_file_cache['cache_patch'] . '/' . $_lib_file_cache['cache_index_file'].$_lib_file_cache['cache_file_extension'];
			else
				$_lib_file_cache['cache_file']=$_lib_file_cache['cache_patch'] . '/' . $_lib_file_cache['cache_file_url'].$_lib_file_cache['cache_file_extension'];

			if(file_exists($_lib_file_cache['cache_file']))
			{
				if($_lib_file_cache['expire'] === 0)
				{
					readfile($_lib_file_cache['cache_file']);
					$_lib_file_cache['status']=0;
				}
				else if(time()-filemtime($_lib_file_cache['cache_file']) > $_lib_file_cache['expire'])
				{
					$_lib_file_cache['generate']();
					$_lib_file_cache['status']=2;
				}
				else
				{
					readfile($_lib_file_cache['cache_file']);
					$_lib_file_cache['status']=0;
				}
			}
			else
			{
				$_lib_file_cache['generate']();
				$_lib_file_cache['status']=1;
			}
		}

		return $_lib_file_cache;
	}
?>