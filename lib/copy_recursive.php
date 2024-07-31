<?php
	function copy_recursive(string $source, string $destination)
	{
		/*
		 * An overlay on the copy function
		 * that allows you to copy entire directories
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
				if(!mkdir($destination.'/'.$iterator->getSubPathname()))
					return false;

				continue;
			}

			if(!copy($item, $destination.'/'.$iterator->getSubPathname()))
				return false;
		}

		return true;
	}
?>