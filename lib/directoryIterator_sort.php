<?php
	function directoryIterator_sort(string $directory, array $output_array_items, string $key_method='getPathname')
	{
		/*
		 * Sort directoryIterator output by name
		 *
		 * Warning:
		 *  this function saves selected data into single array
		 *  this may be problematic
		 *
		 * Usage:
		 *  directoryIterator_sort('path/to/directory', ['getSomethingA', 'getSomethingB'])
		 *   where [] is output array field names - output of these iterator's methods will be saved
		 *  directoryIterator_sort('path/to/directory', ['getSomethingA', 'getSomethingB'], 'getFilename')
		 *   you can also change the keys name in the output array - default method for this is getPathname()
		 *   warning: key must be an unique value!
		 */

		$return_array=array();

		foreach(new directoryIterator($directory) as $file)
			if(!$file->isDot())
				foreach($output_array_items as $output_array_item)
					$return_array[$file->$key_method()][$output_array_item]=$file->$output_array_item();

		uksort($return_array, function($a, $b){
			return strcmp($a, $b);
		});

		return $return_array;
	}
?>