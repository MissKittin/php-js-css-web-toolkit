<?php
	/*
	 * Output buffer cache library
	 *
	 * Warning:
	 *  buffer control must not be started
	 *  this library may conflict with other libraries that uses output buffer control
	 *
	 * Note:
	 *  if int_expire_seconds is 0 cache won't be refreshed
	 *  in ob_redis_cache timeout is handled by Redis
	 *  in ob_memcached_cache timeout is handled by Memcached
	 *  throws an ob_cache_exception on error
	 *
	 * Functions:
	 *  ob_file_cache -> save cache to file
	 *  ob_redis_cache -> save cache in Redis
	 *  ob_memcached_cache -> save cache in Memcached
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
	 *  ob_memcached_cache(memcached_handler, string_url, int_expire_seconds=3600, bool_gzip=true, string_record_prefix='ob_memcached_cache')
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
	 *  ob_memcached_cache with compression enabled and no timeout:
			$ob_memcached_cache=new Memcached();
			$ob_memcached_cache->addServer('127.0.0.1', 11211);
			if(ob_memcached_cache($ob_memcached_cache, 'cache_'.ob_url2file(), 0, true, 'app_cache') === 0)
				exit();
	 */

	class ob_cache_exception extends Exception {}
	function ob_file_cache(string $output_file, int $expire=3600, bool $gzip=false)
	{
		$_ob_cache['gzip']=$gzip;
		$_ob_cache['browser_gzip_support']=false;

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$_ob_cache['browser_gzip_support']=(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);

		$generate=function($_ob_cache)
		{
			if(
				$_ob_cache['gzip'] &&
				$_ob_cache['browser_gzip_support']
			)
				header('Content-Encoding: gzip');

			if(file_put_contents($_ob_cache['output_file'], '') === false)
				throw new ob_cache_exception('Cannot write to the '.$_ob_cache['output_file']);

			ob_start(function($buffer) use($_ob_cache){
				if($_ob_cache['gzip'])
				{
					if(!$_ob_cache['browser_gzip_support'])
						$raw_buffer=$buffer;

					$buffer=gzencode($buffer);
				}

				if(file_put_contents($_ob_cache['output_file'], $buffer, FILE_APPEND) === false)
					throw new ob_cache_exception('Cannot write to the '.$_ob_cache['output_file']);

				if(
					$_ob_cache['gzip'] &&
					(!$_ob_cache['browser_gzip_support'])
				)
					return $raw_buffer;
				else
					return $buffer;
			});
		};

		$output_dir=dirname($output_file);

		if(!file_exists($output_dir))
			mkdir($output_dir, 0777, true);

		if(!is_dir($output_dir))
			throw new ob_cache_exception($output_dir.' is not a directory');

		if(file_exists($output_file))
		{
			$_ob_cache['output_file']=realpath($output_file);

			if
			(
				($expire !== 0) &&
				((time()-filemtime($output_file)) > $expire)
			){
				$generate($_ob_cache);
				return 2;
			}

			if($gzip)
			{
				if($_ob_cache['browser_gzip_support'])
				{
					header('Content-Encoding: gzip');
					readfile($output_file);
				}
				else
					readgzfile($output_file);
			}
			else
				readfile($output_file);

			return 0;
		}

		if(file_put_contents($output_file, '') === false)
			throw new ob_cache_exception('Cannot create '.$output_file);

		$_ob_cache['output_file']=realpath($output_file);
		$generate($_ob_cache);

		return 1;
	}
	function ob_redis_cache(
		$redis_handler,
		string $url,
		int $expire=3600,
		bool $gzip=false,
		string $prefix='ob_redis_cache'
	){
		$_ob_cache['redis_handler']=$redis_handler;
		$_ob_cache['url']=$prefix.$url;
		$_ob_cache['expire']=$expire;
		$_ob_cache['gzip']=$gzip;
		$_ob_cache['browser_gzip_support']=false;

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$_ob_cache['browser_gzip_support']=(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);

		$cache_content=$redis_handler->get($prefix.$url);

		if($cache_content === false)
		{
			// $generate()

			if(
				$_ob_cache['gzip'] &&
				$_ob_cache['browser_gzip_support']
			)
				header('Content-Encoding: gzip');

			if($_ob_cache['expire'] === 0)
				$_ob_cache['redis_handler']->set(
					$_ob_cache['url'],
					''
				);
			else
				$_ob_cache['redis_handler']->set(
					$_ob_cache['url'],
					'',
					['ex'=>$_ob_cache['expire']]
				);

			ob_start(function($buffer) use($_ob_cache){
				if($_ob_cache['gzip'])
				{
					if(!$_ob_cache['browser_gzip_support'])
						$raw_buffer=$buffer;

					$buffer=gzencode($buffer);
				}

				$cache_content=$_ob_cache['redis_handler']->get($_ob_cache['url']);

				if($_ob_cache['expire'] === 0)
					$_ob_cache['redis_handler']->set(
						$_ob_cache['url'],
						$cache_content.$buffer
					);
				else
					$_ob_cache['redis_handler']->set(
						$_ob_cache['url'],
						$cache_content.$buffer,
						['ex'=>$_ob_cache['expire']]
					);

				if(
					$_ob_cache['gzip'] &&
					(!$_ob_cache['browser_gzip_support'])
				)
					return $raw_buffer;
				else
					return $buffer;
			});

			return 1;
		}

		if($gzip)
		{
			if($_ob_cache['browser_gzip_support'])
			{
				header('Content-Encoding: gzip');
				echo $cache_content;
			}
			else
				echo gzdecode($cache_content);
		}
		else
			echo $cache_content;

		return 0;
	}
	function ob_memcached_cache(
		$memcached_handler,
		string $url,
		int $expire=3600,
		bool $gzip=false,
		string $prefix='ob_memcached_cache'
	){
		$_ob_cache['memcached_handler']=$memcached_handler;
		$_ob_cache['url']=$prefix.$url;
		$_ob_cache['expire']=$expire;
		$_ob_cache['gzip']=$gzip;
		$_ob_cache['browser_gzip_support']=false;

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$_ob_cache['browser_gzip_support']=(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);

		$memcached_handler->get($prefix.$url); // trigger expiration
		$cache_content=$memcached_handler->get($prefix.$url);

		if($cache_content === false)
		{
			// $generate()

			if(
				$_ob_cache['gzip'] &&
				$_ob_cache['browser_gzip_support']
			)
				header('Content-Encoding: gzip');

			if($_ob_cache['expire'] === 0)
				$_ob_cache['memcached_handler']->set(
					$_ob_cache['url'],
					''
				);
			else
				$_ob_cache['memcached_handler']->set(
					$_ob_cache['url'],
					'',
					$_ob_cache['expire']
				);

			ob_start(function($buffer) use($_ob_cache){
				if($_ob_cache['gzip'])
				{
					if(!$_ob_cache['browser_gzip_support'])
						$raw_buffer=$buffer;

					$buffer=gzencode($buffer);
				}

				$cache_content=$_ob_cache['memcached_handler']->get($_ob_cache['url']);

				if($_ob_cache['expire'] === 0)
					$_ob_cache['memcached_handler']->set(
						$_ob_cache['url'],
						$cache_content.$buffer
					);
				else
					$_ob_cache['memcached_handler']->set(
						$_ob_cache['url'],
						$cache_content.$buffer,
						$_ob_cache['expire']
					);

				if(
					$_ob_cache['gzip'] &&
					(!$_ob_cache['browser_gzip_support'])
				)
					return $raw_buffer;
				else
					return $buffer;
			});

			return 1;
		}

		if($gzip)
		{
			if($_ob_cache['browser_gzip_support'])
			{
				header('Content-Encoding: gzip');
				echo $cache_content;
			}
			else
				echo gzdecode($cache_content);
		}
		else
			echo $cache_content;

		return 0;
	}
	function ob_url2file(bool $ignore_get=true)
	{
		if(!isset($_SERVER['REQUEST_URI']))
			throw new ob_cache_exception('$_SERVER["REQUEST_URI"] is not set');

		$result=$_SERVER['REQUEST_URI'];

		if($ignore_get)
			$result=strtok($result, '?');

		return str_replace(['/', '\\'], '___', $result);
	}
	function ob_url2sha1(bool $ignore_get=true)
	{
		if(!isset($_SERVER['REQUEST_URI']))
			throw new ob_cache_exception('$_SERVER["REQUEST_URI"] is not set');

		$result=$_SERVER['REQUEST_URI'];

		if($ignore_get)
			$result=strtok($result, '?');

		return sha1($result);
	}
?>