<?php
	/*
	 * spl_object_id() polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php72/blob/1.x/Php72.php
	 * License: MIT https://github.com/symfony/polyfill-php72/blob/1.x/LICENSE
	 */

	if(!function_exists('spl_object_id'))
	{
		function spl_object_id($object)
		{
			static $hash_mask=-1;
			static $int_size=PHP_INT_SIZE*2-1;

			if($hash_mask === -1)
			{
				$init_hash_object=new stdClass();

				$init_hash_ob_funcs=['ob_clean', 'ob_end_clean', 'ob_flush', 'ob_end_flush', 'ob_get_contents', 'ob_get_flush'];
				$init_hash_backtrace_ignore_args=false;

				if(PHP_VERSION_ID >= 50400)
					$init_hash_backtrace_ignore_args=DEBUG_BACKTRACE_IGNORE_ARGS;

				foreach(debug_backtrace($init_hash_backtrace_ignore_args) as $init_hash_frame)
					if(
						isset($init_hash_frame['function'][0]) &&
						(!isset($init_hash_frame['class'])) &&
						($init_hash_frame['function'][0] === 'o') &&
						in_array($init_hash_frame['function'], $init_hash_ob_funcs)
					){
						$init_hash_frame['line']=0;
						break;
					}

				if(!empty($init_hash_frame['line']))
				{
					ob_start();
					debug_zval_dump($init_hash_object);
					$hash_mask=(int)substr(ob_get_clean(), 17);
				}

				$hash_mask^=hexdec(substr(spl_object_hash($init_hash_object), 16-$int_size, $int_size));
			}

			$hash=spl_object_hash($object);

			if($hash === null)
				return null;

			return $hash_mask^hexdec(substr($hash, 16-$int_size, $int_size));
		}
	}
?>