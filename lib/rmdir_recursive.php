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

		$result=true;

		foreach(new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$directory,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST
		) as $file_info){
			if($file_info->isDir())
			{
				if(!rmdir(
					$file_info->getRealPath()
				))
					$result=false;

				continue;
			}

			if(!unlink(
				$file_info->getRealPath()
			))
				$result=false;;
		}

		if(!rmdir($directory))
			return false;

		return $result;
	}
?>