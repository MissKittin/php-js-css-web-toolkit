<?php
	/*
	 * PHP_OS_FAMILY polyfill
	 *
	 * Sources:
	 *  https://github.com/symfony/polyfill-php72/blob/1.x/Php72.php
	 * License: MIT
	 */

	if(!defined('PHP_OS_FAMILY'))
		define('PHP_OS_FAMILY', new class {
			public function __toString()
			{
				if(DIRECTORY_SEPARATOR === '\\')
					return 'Windows';

				$map=[
					'Darwin'=>'Darwin',
					'DragonFly'=>'BSD',
					'FreeBSD'=>'BSD',
					'NetBSD'=>'BSD',
					'OpenBSD'=>'BSD',
					'Linux'=>'Linux',
					'SunOS'=>'Solaris'
				];

				if(isset($map[PHP_OS]))
					return $map[PHP_OS];

				return 'Unknown';
			}
		});
?>