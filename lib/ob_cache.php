<?php
	/*
	 * Output buffer cache library
	 *
	 * Warning:
	 *  buffer control must not be started
	 *  this library may conflict with other libraries that uses output buffer control
	 *  $GLOBALS['ob_cache'] is reserved
	 *  ob_redis_cache requires the phpredis extension
	 *
	 * Note:
	 *  if int_expire_seconds is 0 cache won't be refreshed
	 *  in ob_phpredis_cache timeout is handled by Redis
	 *
	 * Functions:
	 *  ob_file_cache -> save cache to file
	 *  ob_phpredis_cache -> save cache in Redis
	 *  ob_url2file -> convert $_SERVER['REQUEST_URI'] to filename
	 *  ob_url2sha1 -> return the sha1 hash of the url
	 *
	 * Return codes:
	 *  0 -> cache is valid and has just been sent to the client
	 *  1 -> cache will be created on exit
	 *  2 -> cache will be refreshed on exit
	 *
	 * Usage:
	 *  ob_file_cache(string_path_to_file, int_expire_seconds=3600, bool_gzip=true)
	 *  ob_phpredis_cache(phpredis_handler, string_url, int_expire_seconds=3600, bool_gzip=true, string_record_prefix='ob_phpredis_cache')
	 *  ob_url2file(bool_ignore_get_params=true)
	 *  ob_url2sha1(bool_ignore_get_params=true)
	 *
	 * Examples:
	 *  ob_file_cache with compression enabled and no timeout:
			if(ob_file_cache('./tmp/cache_'.ob_url2file(), 0, true) === 0)
				exit();
	 *  ob_phpredis_cache with compression enabled and no timeout:
			$ob_phpredis_cache=new Redis();
			$ob_phpredis_cache->connect('127.0.0.1', 6379);
			if(ob_phpredis_cache($ob_phpredis_cache, 'cache_'.ob_url2file(), 0, true, 'app_cache') === 0)
				exit();
	 */

	function ob_file_cache(string $output_file, int $expire=3600, bool $gzip=false)
	{
		global $ob_cache;
		$ob_cache['output_file']=$output_file;
		$ob_cache['gzip']=$gzip;
		$ob_cache['cwd']=getcwd();
		$ob_cache['browser_gzip_support']=strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');

		$generate=function($ob_cache)
		{
			if(($ob_cache['gzip']) && ($ob_cache['browser_gzip_support'] !== false))
				header('Content-Encoding: gzip');

			if(file_put_contents($ob_cache['output_file'], '') === false)
				throw new Exception('cannot write to the '.$ob_cache['output_file']);

			ob_start(function($buffer){
				$cwd=getcwd();

				global $ob_cache;
				chdir($ob_cache['cwd']);					
				if($ob_cache['gzip'])
				{
					if($ob_cache['browser_gzip_support'] === false)
						$raw_buffer=$buffer;
					$buffer=gzencode($buffer);
				}

				if(file_put_contents($ob_cache['output_file'], $buffer, FILE_APPEND) === false)
					throw new Exception('cannot write to the '.$ob_cache['output_file']);

				chdir($cwd);

				if(($ob_cache['gzip']) && ($ob_cache['browser_gzip_support'] === false))
					return $raw_buffer;
				else
					return $buffer;
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
			){
				$generate($ob_cache);
				return 2;
			}

			if($gzip)
			{
				if($ob_cache['browser_gzip_support'] === false)
					readgzfile($output_file);
				else
				{
					header('Content-Encoding: gzip');
					readfile($output_file);
				}
			}
			else
				readfile($output_file);
			return 0;
		}

		$generate($ob_cache);
		return 1;
	}
	function ob_phpredis_cache(
		$redis_handler,
		string $url,
		int $expire=3600,
		bool $gzip=false,
		string $prefix='ob_phpredis_cache'
	){
		global $ob_cache;
		$ob_cache['redis_handler']=$redis_handler;
		$ob_cache['url']=$prefix.$url;
		$ob_cache['expire']=$expire;
		$ob_cache['gzip']=$gzip;
		$ob_cache['browser_gzip_support']=strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');

		$generate=function($ob_cache)
		{
			if(($ob_cache['gzip']) && ($ob_cache['browser_gzip_support'] !== false))
				header('Content-Encoding: gzip');

			if($ob_cache['expire'] === 0)
				$ob_cache['redis_handler']->set($ob_cache['url'], '');
			else
				$ob_cache['redis_handler']->setex($ob_cache['url'], $ob_cache['expire'], '');

			ob_start(function($buffer){
				global $ob_cache;
				if($ob_cache['gzip'])
				{
					if($ob_cache['browser_gzip_support'] === false)
						$raw_buffer=$buffer;
					$buffer=gzencode($buffer);
				}

				$cache_content=$ob_cache['redis_handler']->get($ob_cache['url']);

				if($ob_cache['expire'] === 0)
					$ob_cache['redis_handler']->set($ob_cache['url'], $cache_content.$buffer);
				else
					$ob_cache['redis_handler']->setex($ob_cache['url'], $ob_cache['expire'], $cache_content.$buffer);

				if(($ob_cache['gzip']) && ($ob_cache['browser_gzip_support'] === false))
					return $raw_buffer;
				else
					return $buffer;
			});
		};

		$cache_content=$redis_handler->get($prefix.$url);
		if($cache_content === false)
		{
			$generate($ob_cache);
			return 1;
		}

		if($gzip)
		{
			if($ob_cache['browser_gzip_support'] === false)
				echo gzdecode($cache_content);
			else
			{
				header('Content-Encoding: gzip');
				echo $cache_content;
			}
		}
		else
			echo $cache_content;
		return 0;
	}
	function ob_url2file(bool $ignore_get=true)
	{
		$result=$_SERVER['REQUEST_URI'];
		if($ignore_get)
			$result=strtok($result, '?');
		return str_replace(['/', '\\'], '___', $result);
	}
	function ob_url2sha1(bool $ignore_get=true)
	{
		$result=$_SERVER['REQUEST_URI'];
		if($ignore_get)
			$result=strtok($result, '?');
		return sha1($result);
	}
?>