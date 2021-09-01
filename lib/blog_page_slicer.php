<?php
	function blog_page_slicer($input_array, $current_page, $entries_per_page, $preserve_keys=false)
	{
		/*
		 * Select n elements from array at start point
		 * from simpleblog project
		 *
		 * Usage:
		 *  blog_page_slicer(array_slice(scandir('/path'), 2), 1, 5)
			blog_page_slicer(array_filter(scandir('/path'), function($input){
				if(substr($input, 0, 7) === 'public_') return $input;
			}), 1, 5)
		 */

		if($current_page < 1) $current_page=1;
		return array_slice($input_array, ($current_page-1)*$entries_per_page, $entries_per_page, $preserve_keys);
	}
?>