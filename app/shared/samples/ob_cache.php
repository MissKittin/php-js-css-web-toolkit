<?php
	/*
	 * Checks if it is possible to connect to Redis
	 * if so, use Redis as cache, if not - dump the cache to a file
	 *
	 * See:
	 *  controllers/samples/about.php
	 *  controllers/samples/check-date.php
	 *  controllers/samples/preprocessing-test.php
	 */

	if(!function_exists('ob_file_cache'))
		include './lib/ob_cache.php';

	function ob_cache($url, $expire=3600, $redis_address='127.0.0.1', $redis_port=6379)
	{
		if(extension_loaded('redis'))
		{
			$redis=new Redis();

			try {
				if($redis_port === null)
					$connected=$redis->connect($redis_address);
				else
					$connected=$redis->connect($redis_address, $redis_port);
			} catch(RedisException $e) {
				$connected=false;
			}

			if($connected)
			{
				if(ob_redis_cache($redis, $url, $expire, true) === 0)
					exit();

				return true;
			}
		}

		if(ob_file_cache('./var/cache/cache_'.$url, $expire, true) === 0)
			exit();

		return false;
	}
?>