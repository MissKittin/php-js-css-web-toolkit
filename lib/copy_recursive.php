<?php
	function copy_recursive(string $source, string $destination)
	{
		/*
		 * An overlay on the copy function
		 * that allows you to copy entire directories
		 */

		if(is_dir($source))
		{
			if(!mkdir($destination))
				return false;

			foreach(new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::SELF_FIRST
			) as $item)
				if($item->isDir())
				{
					if(!mkdir($destination.'/'.$iterator->getSubPathname()))
						return false;
				}
				else
					if(!copy($item, $destination.'/'.$iterator->getSubPathname()))
						return false;

			return true;
		}

		return copy($source, $destination);
	}
?>