<?php
	/*
	 * Output buffer cache library
	 *
	 * Warning:
	 *  buffer control must not be started
	 *  this library may conflict with other libraries that uses output buffer control
	 *  $GLOBALS['_ob_cache'] is reserved
	 *  ob_redis_cache requires the redis extension
	 *
	 * Note:
	 *  if int_expire_seconds is 0 cache won't be refreshed
	 *  in ob_redis_cache timeout is handled by Redis
	 *
	 * Functions:
	 *  ob_file_cache -> save cache to file
	 *  ob_redis_cache -> save cache in Redis
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
	 *  ob_redis_cache(redis_handler, string_url, int_expire_seconds=3600, bool_gzip=true, string_record_prefix='ob_redis_cache')
	 *  ob_url2file(bool_ignore_get_params=true)
	 *  ob_url2sha1(bool_ignore_get_params=true)
	 *
	 * Examples:
	 *  ob_file_cache with compression enabled and no timeout:
			if(ob_file_cache('./tmp/cache_'.ob_url2file(), 0, true) === 0)
				exit();
	 *  ob_redis_cache with compression enabled and no timeout:
			$ob_redis_cache=new Redis();
			$ob_redis_cache->connect('127.0.0.1', 6379);
			if(ob_redis_cache($ob_redis_cache, 'cache_'.ob_url2file(), 0, true, 'app_cache') === 0)
				exit();
	 */

	function ob_file_cache(string $output_file, int $expire=3600, bool $gzip=false)
	{
		$GLOBALS['_ob_cache']['output_file']=$output_file;
		$GLOBALS['_ob_cache']['gzip']=$gzip;
		$GLOBALS['_ob_cache']['cwd']=getcwd();
		$GLOBALS['_ob_cache']['browser_gzip_support']=false;

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$GLOBALS['_ob_cache']['browser_gzip_support']=strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');

		$generate=function()
		{
			if(
				$GLOBALS['_ob_cache']['gzip'] &&
				($GLOBALS['_ob_cache']['browser_gzip_support'] !== false)
			)
				header('Content-Encoding: gzip');

			if(file_put_contents($GLOBALS['_ob_cache']['output_file'], '') === false)
				throw new Exception('Cannot write to the '.$GLOBALS['_ob_cache']['output_file']);

			ob_start(function($buffer){
				$cwd=getcwd();

				chdir($GLOBALS['_ob_cache']['cwd']);

				if($GLOBALS['_ob_cache']['gzip'])
				{
					if($GLOBALS['_ob_cache']['browser_gzip_support'] === false)
						$raw_buffer=$buffer;

					$buffer=gzencode($buffer);
				}

				if(file_put_contents($GLOBALS['_ob_cache']['output_file'], $buffer, FILE_APPEND) === false)
					throw new Exception('Cannot write to the '.$GLOBALS['_ob_cache']['output_file']);

				chdir($cwd);

				if(
					$GLOBALS['_ob_cache']['gzip'] &&
					($GLOBALS['_ob_cache']['browser_gzip_support'] === false)
				)
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
				($expire !== 0) &&
				((time()-filemtime($output_file)) > $expire)
			){
				$generate();
				return 2;
			}

			if($gzip)
			{
				if($GLOBALS['_ob_cache']['browser_gzip_support'] === false)
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

		$generate();

		return 1;
	}
	function ob_redis_cache(
		$redis_handler,
		string $url,
		int $expire=3600,
		bool $gzip=false,
		string $prefix='ob_redis_cache'
	){
		$GLOBALS['_ob_cache']['redis_handler']=$redis_handler;
		$GLOBALS['_ob_cache']['url']=$prefix.$url;
		$GLOBALS['_ob_cache']['expire']=$expire;
		$GLOBALS['_ob_cache']['gzip']=$gzip;
		$GLOBALS['_ob_cache']['browser_gzip_support']=false;

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$GLOBALS['_ob_cache']['browser_gzip_support']=strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');

		$generate=function()
		{
			if(
				$GLOBALS['_ob_cache']['gzip'] &&
				($GLOBALS['_ob_cache']['browser_gzip_support'] !== false)
			)
				header('Content-Encoding: gzip');

			if($GLOBALS['_ob_cache']['expire'] === 0)
				$GLOBALS['_ob_cache']['redis_handler']->set(
					$GLOBALS['_ob_cache']['url'],
					''
				);
			else
				$GLOBALS['_ob_cache']['redis_handler']->setex(
					$GLOBALS['_ob_cache']['url'],
					$GLOBALS['_ob_cache']['expire'],
					''
				);

			ob_start(function($buffer){
				if($GLOBALS['_ob_cache']['gzip'])
				{
					if($GLOBALS['_ob_cache']['browser_gzip_support'] === false)
						$raw_buffer=$buffer;

					$buffer=gzencode($buffer);
				}

				$cache_content=$GLOBALS['_ob_cache']['redis_handler']->get($GLOBALS['_ob_cache']['url']);

				if($GLOBALS['_ob_cache']['expire'] === 0)
					$GLOBALS['_ob_cache']['redis_handler']->set(
						$GLOBALS['_ob_cache']['url'],
						$cache_content.$buffer
					);
				else
					$GLOBALS['_ob_cache']['redis_handler']->setex(
						$GLOBALS['_ob_cache']['url'],
						$GLOBALS['_ob_cache']['expire'],
						$cache_content.$buffer
					);

				if(
					$GLOBALS['_ob_cache']['gzip'] &&
					($GLOBALS['_ob_cache']['browser_gzip_support'] === false)
				)
					return $raw_buffer;
				else
					return $buffer;
			});
		};

		$cache_content=$redis_handler->get($prefix.$url);

		if($cache_content === false)
		{
			$generate();
			return 1;
		}

		if($gzip)
		{
			if($GLOBALS['_ob_cache']['browser_gzip_support'] === false)
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
		if(!isset($_SERVER['REQUEST_URI']))
			throw new Exception('$_SERVER["REQUEST_URI"] is not set');

		$result=$_SERVER['REQUEST_URI'];

		if($ignore_get)
			$result=strtok($result, '?');

		return str_replace(['/', '\\'], '___', $result);
	}
	function ob_url2sha1(bool $ignore_get=true)
	{
		if(!isset($_SERVER['REQUEST_URI']))
			throw new Exception('$_SERVER["REQUEST_URI"] is not set');

		$result=$_SERVER['REQUEST_URI'];

		if($ignore_get)
			$result=strtok($result, '?');

		return sha1($result);
	}
?>