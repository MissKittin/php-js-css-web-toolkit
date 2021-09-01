<?php
	/*
	 * Convert flat array to tree, tree to list
	 * See list2tree.js for frontend function
	 */

	function array_flat2tree($input_array, $indexes, $parent_id)
	{
		/*
		 * Convert flat array to nested array
		 *
		 * Usage:
			$nested_array=array_flat2tree
			(
				$input_array,
				$input_array_indexes,
				null // parent_element_id for root
			);
		 *
		 * Example input arrays:
			$input_array=array
			(
				[0, null, 'content0|description0'],
				[1, null, 'content1|description1'],
				[2, 3, 'content2|description2'],
				[3, null, 'content3|description3'],
				[4, 2, 'content4|description4'],
				[5, null, 'content5|description5'],
				[6, 4, 'content6|description6'],
				[7, null, 'content7|description7'],
				[8, 2, 'content8|description8'],
				[9, 5, 'content9|description9']
			);
			$input_array_indexes=array
			(
				'element_id'=>0,
				'parent_element_id'=>1,
				'content'=>2
			);
		 * $input_array can be from PDO's fetchAll()
		 */

		$output_array=array();
		foreach($input_array as $row)
			if($row[$indexes['parent_element_id']] === $parent_id)
				$output_array[$row[$indexes['content']]]=array_flat2tree($input_array, $indexes, $row[$indexes['element_id']]);
		return $output_array;
	}
	function print_array_recursive($input_array, $open_tree, $open_node, $print_node_element, $close_node, $close_tree, $current_depth=0)
	{
		/*
		 * Convert tree array to list
		 *
		 * Usage:
			print_array_recursive(
				$nested_array,
				function($array, $depth)
				{
					// open tree
					// $array is node content [array]
					// $depth is level in tree [int]

					if($depth === 0)
						echo '<ul id="tree_list" class="tree">';
					else
						echo '<ul>';
				},
				function($key, $value, $depth)
				{
					// open node
					// $key is node name [string]
					// $value is node's child [array]
					// $depth is level in tree [int]

					echo '<li>';
				},
				function($key, $value, $depth)
				{
					// print node
					// $key is node name [string]
					// $value is node's child [array]
					// $depth is level in tree [int]

					// CSS classes prepared for list2tree.js
					$content=explode('|', $key);
					if(empty($value))
						echo '<span>' . $content[0] . ' <span style="color: #ff0000;">' . $content[1] . '</span> ($depth === ' . $depth . ')</span>';
					else
						echo '<span class="tree_open_tick">' . $content[0] . ' <span style="color: #ff0000;">' . $content[1] . '</span> ($depth === ' . $depth . ')</span>';
				},
				function($key, $value, $depth)
				{
					// close node
					// $key is node name [string]
					// $value is node's child [array]
					// $depth is level in tree [int]

					echo '</li>';
				},
				function($array, $depth)
				{
					// close tree
					// $array is node content [array]
					// $depth is level in tree [int]

					echo '</ul>';
				}
			);
		 * you can use return instead of echo
		 *
		 * Example input array:
		 *	array(5)
		 *	{
		 *		["content0|description0"]=>array(0){}
		 *		["content1|description1"]=>array(0){}
		 *		["content3|description3"]=>array(1)
		 *		{
		 *			["content2|description2"]=>array(2)
		 *			{
		 *				["content4|description4"]=>array(1)
		 *				{
		 *					["content6|description6"]=>array(0){}
		 *				}
		 *				["content8|description8"]=>array(0){}
		 *			}
		 *		}
		 *		["content5|description5"]=>array(1)
		 *		{
		 *			["content9|description9"]=>array(0){}
		 *		}
		 *		["content7|description7"]=>array(0){}
		 *	}
		 * can be from array_flat2tree()
		 */

		$return='';

		$return.=$open_tree($input_array, $current_depth);
		foreach($input_array as $key=>$value)
		{
			$return.=$open_node($key, $value, $current_depth);
			$return.=$print_node_element($key, $value, $current_depth);

			$depth=$current_depth;
			if(!empty($value))
				$return.=print_array_recursive($value, $open_tree, $open_node, $print_node_element, $close_node, $close_tree, ++$depth);

			$return.=$close_node($key, $value, $current_depth);
		}
		$return.=$close_tree($input_array, $current_depth);

		return $return;
	}
?>