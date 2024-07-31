<?php
	/*
	 * PHP_OS_FAMILY polyfill
	 *
	 * Source: https://github.com/symfony/polyfill-php72/blob/1.x/Php72.php
	 * License: MIT https://github.com/symfony/polyfill-php72/blob/1.x/LICENSE
	 */

	if(!defined('PHP_OS_FAMILY'))
		switch(PHP_OS)
		{
			case 'Darwin':
			case 'Linux':
				define('PHP_OS_FAMILY', PHP_OS);
			break;
			case 'DragonFly':
			case 'FreeBSD':
			case 'NetBSD':
			case 'OpenBSD':
				define('PHP_OS_FAMILY', 'BSD');
			break;
			case 'SunOS':
				define('PHP_OS_FAMILY', 'Solaris');
			break;
			default:
				if(
					(DIRECTORY_SEPARATOR === '\\') ||
					(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				)
					define('PHP_OS_FAMILY', 'Windows');
				else
					define('PHP_OS_FAMILY', 'Unknown');
		}
?>