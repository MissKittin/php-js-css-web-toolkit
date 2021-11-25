<?php
	/*
	 * Blog article slicing solutions from simpleblog project
	 *
	 * Functions:
	 *  blog_page_slicer - a simplified version that uses built-in functions
	 *  blog_page_slicer_old - old optimized version, for historical purposes
	 */

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

		if($current_page < 1)
			$current_page=1;

		return array_slice($input_array, ($current_page-1)*$entries_per_page, $entries_per_page, $preserve_keys);
	}
	function blog_page_slicer_old($input_array, $current_page, $entries_per_page)
	{
		/*
		 * Select n elements from array at start point
		 * from simpleblog project (optimized)
		 * old version - for historical purposes only
		 *
		 * Usage:
		 *  blog_page_slicer_old(array_slice(scandir('/path'), 2), 1, 5)
			blog_page_slicer_old(array_filter(scandir('/path'), function($input){
				if(substr($input, 0, 7) === 'public_') return $input;
			}), 1, 5)
		 */

		$return_array=array();
		if($current_page < 1)
			$current_page=1;

		$foreach_cache_a=($current_page*$entries_per_page)-($entries_per_page-1);
		$foreach_cache_b=$current_page*$entries_per_page;
		$foreach_loop_ind=1;

		foreach($input_array as $input_array_element)
		{
			if($foreach_loop_ind >= $foreach_cache_a)
				$return_array[]=$input_array_element;

			if($foreach_loop_ind === $foreach_cache_b)
				break;

			++$foreach_loop_ind;
		}

		return $return_array;
	}
?>