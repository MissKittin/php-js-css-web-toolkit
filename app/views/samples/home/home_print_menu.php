<?php
	function home_print_menu($links, $use_line_break=true)
	{
		$link_class=' class="link_no_arrow"';
		$line_break=' ';

		if($use_line_break)
		{
			$link_class='';
			$line_break='<br>';
		}

		foreach($links as $link_label=>$link_url)
			if(is_array($link_url))
			{
				echo $link_label.' ';
				(__FUNCTION__)($link_url, false);
				echo $line_break;
			}
			else
				echo '<a href="'.$link_url.'"'.$link_class.'>'.$link_label.'</a>'.$line_break;
	}
?>