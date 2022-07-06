<?php
	function rmdir_recursive(string $directory)
	{
		/*
		 * An overlay on the rmdir function
		 * that allows you to remove non-empty directories
		 *
		 * Source:
		 *  https://stackoverflow.com/a/3352564
		 */

		if(!is_dir($directory))
			return false;

		foreach(new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		) as $file_info){
			$action=($file_info->isDir() ? 'rmdir' : 'unlink');
			$action($file_info->getRealPath());
		}

		rmdir($directory);

		return true;
	}
?>