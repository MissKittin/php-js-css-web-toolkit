<?php
	function relative_path(string $from, string $to)
	{
		/*
		 * Get relative path between two files/directories
		 *
		 * Example usage:
		 *  relative_path('./apache/a/a.php', './root/b/b.php')
		 *   returns '../../root/b/b.php'
		 *  relative_path('/home/apache/a/a.php', '/home/root/b/b.php')
		 *   returns '../../root/b/b.php'
		 *  relative_path('C:\\home\\apache\\a\\a.php', 'C:\\home\\root\\b\\b.php')
		 *   returns '../../root/b/b.php'
		 *
		 * Note:
		 *  returns false if $from or $to not exists
		 *  if $from or $to is a directory, a slash will be added to the end
		 *
		 * Source:
		 *  https://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php#
		 */

		$from=realpath($from);
		$to=realpath($to);

		if(($from === false) || ($to === false))
			return false;

		if(is_dir($from))
			$from=rtrim($from, '\/').'/';
		if(is_dir($to))
			$to=rtrim($to, '\/').'/';

		$from=explode('/', str_replace('\\', '/', $from));
		$to=explode('/', str_replace('\\', '/', $to));
		$relative_path=$to;

		foreach($from as $depth=>$dir)
			if($dir === $to[$depth])
				array_shift($relative_path);
			else
			{
				$remaining=count($from)-$depth;

				if($remaining > 1)
				{
					$relative_path=array_pad(
						$relative_path,
						(count($relative_path)+$remaining-1)*-1,
						'..'
					);

					break;
				}
				else
					$relative_path[0]='./'.$relative_path[0];
			}

		return implode('/', $relative_path);
	}
?>