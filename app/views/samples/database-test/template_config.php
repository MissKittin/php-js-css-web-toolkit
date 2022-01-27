<?php
	$view['lang']='en';
	$view['title']='Database';
	$view['meta_description']='App test - relational database part';
	$view['meta_robots']='index,follow';

	$view['render_table']=function($result)
	{
		if(empty($result))
			return false;

		echo '<table><tr><th>ID</th><th>Name</th><th>Price</th></tr>';
			foreach($result as $row)
				echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td><td>' . $row[2] . '$</td></tr>';
		echo '</table>';

		return true;
	};
	$view['print_found_records']=function($view)
	{
		echo '<h3>Search results:</h3>';
		if(!$view['render_table'](
			$view['db_cars']->read('name', $_POST['car_name'])
		))
			echo 'No records found';
	};
	$view['print_all_records']=function($view)
	{
		if(!$view['render_table'](
			$view['db_cars']->read()
		))
			echo 'No records found';
	};
?>