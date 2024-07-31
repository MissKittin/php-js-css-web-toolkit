<?php
	function scandir_recursive(
		string $path,
		bool $skip_dots=false,
		int $sorting_order=SCANDIR_SORT_ASCENDING,
		$prefix=''
	){
		/*
		 * Scan directory recursively
		 *
		 * Note:
		 *  if you need a more robust method
		 *  use RecursiveIteratorIterator with RecursiveDirectoryIterator
		 *
		 * Usage:
			foreach(scandir_recursive('./dirname') as $file)
				//

			// skip dots
			foreach(scandir_recursive('./dirname', true) as $file)
				//

			// sort descending
			foreach(scandir_recursive('./dirname', false, SCANDIR_SORT_DESCENDING) as $file)
				//

			// skip dots, sort descending and dump to the array
			$array=iterator_to_array('./dirname', true, SCANDIR_SORT_DESCENDING);
		 */

		if(!is_dir($path))
			return scandir($path);

		foreach(scandir(
			$path,
			$sorting_order
		) as $file)
			if(($file === '.') || ($file === '..'))
			{
				if(!$skip_dots)
					yield $prefix.$file;
			}
			else if(is_dir($path.'/'.$file))
				foreach((__METHOD__)(
					$path.'/'.$file,
					$skip_dots,
					$sorting_order,
					$prefix.$file.'/'
				) as $file)
					yield $file;
			else
				yield $prefix.$file;
	}
?>