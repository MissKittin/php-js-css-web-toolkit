<?php
	/*
	 * Recursive copy and link2file
	 *
	 * Functions:
		copy_recursive('path/to/src-dir', 'path/to/desc-dir'); // returns bool
		link2file('path/to/file-or-dir'); // returns bool
	 */

	class link2file_exception extends Exception {}

	function copy_recursive(string $source, string $destination)
	{
		/*
		 * An overlay on the copy function
		 * that allows you to copy entire directories
		 *
		 * Usage:
			copy_recursive('path/to/src-dir', 'path/to/desc-dir'); // returns bool
		 */

		if(!is_dir($source))
			return copy($source, $destination);

		if(!mkdir($destination))
			return false;

		$iterator=new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$source,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach($iterator as $item)
		{
			if($item->isDir())
			{
				if(!mkdir(
					$destination.'/'.$iterator->getSubPathname())
				)
					return false;

				continue;
			}

			if(!copy(
				$item,
				$destination.'/'.$iterator->getSubPathname()
			))
				return false;
		}

		return true;
	}
	function link2file(string $file)
	{
		/*
		 * Replace symbolic link with file or directory
		 *
		 * Note:
		 *  throws an link2file_exception on error
		 *
		 * Warning:
		 *  only for *nix systems
		 *  copy_recursive function is required
		 *
		 * Usage:
			link2file('path/to/file-or-dir'); // returns bool
		 */

		if(!is_link($file))
			return false;

		$link_destination=readlink($file);

		if($link_destination === false)
			return false;

		$link_destination=dirname($file).'/'.$link_destination;

		if(unlink($file) === false)
			throw new link2file_exception(
				$file.' cannot be removed'
			);

		if(copy_recursive($link_destination, $file) === false)
			throw new link2file_exception(
				$file.' cannot be copied'
			);

		return true;
	}
?>